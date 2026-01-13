<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Trip;
use App\Notifications\NewBookingReceived;
use App\Notifications\BookingConfirmed;
use App\Notifications\BookingStatusChanged;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BookingController extends Controller
{
    public function store(Request $request, Trip $trip)
    {
        $validated = $request->validate([
            'booking_type' => 'required|in:passenger,parcel',
            'seats' => 'required_if:booking_type,passenger|integer|min:1|max:' . $trip->available_seats,
            'weight' => 'required_if:booking_type,parcel|numeric|min:0.01',
            'volume' => 'required_if:booking_type,parcel|numeric|min:0.01|max:' . ($trip->available_volume ?? 999),
            'dimensions' => 'nullable|array',
            'instructions' => 'nullable|string|max:1000',
        ], [
            'seats.required_if' => 'Le nombre de places est requis pour une réservation passager.',
            'weight.required_if' => 'Le poids est requis pour un colis.',
            'volume.required_if' => 'Le volume est requis pour un colis.',
        ]);

        // Vérifications de disponibilité
        if ($validated['booking_type'] === 'passenger') {
            if (!$trip->canAcceptPassengers()) {
                return back()->withErrors(['error' => 'Plus de places disponibles pour les passagers.']);
            }
            if ($validated['seats'] > $trip->available_seats) {
                return back()->withErrors(['error' => 'Nombre de places insuffisant.']);
            }
        } else {
            if (!$trip->canAcceptParcels()) {
                return back()->withErrors(['error' => 'Plus d\'espace disponible pour les colis.']);
            }
            if ($validated['volume'] > $trip->available_volume) {
                return back()->withErrors(['error' => 'Volume disponible insuffisant.']);
            }
            if ($trip->max_weight && $validated['weight'] > $trip->max_weight) {
                return back()->withErrors(['error' => 'Poids maximum dépassé.']);
            }
        }

        DB::beginTransaction();
        try {
            // Calcul du prix
            $price = $validated['booking_type'] === 'passenger'
                ? $trip->price_per_seat * ($validated['seats'] ?? 1)
                : $trip->price_per_seat * ($validated['volume'] ?? 1);

            $booking = Booking::create([
                'trip_id' => $trip->id,
                'user_id' => Auth::id(),
                'booking_type' => $validated['booking_type'],
                'seats' => $validated['booking_type'] === 'passenger' ? ($validated['seats'] ?? 1) : 0,
                'weight' => $validated['booking_type'] === 'parcel' ? ($validated['weight'] ?? null) : null,
                'volume' => $validated['booking_type'] === 'parcel' ? ($validated['volume'] ?? null) : null,
                'dimensions' => $validated['dimensions'] ?? null,
                'instructions' => $validated['instructions'] ?? null,
                'price' => $price,
                'status' => 'pending',
            ]);

            // Mise à jour des disponibilités
            if ($validated['booking_type'] === 'passenger') {
                $seatsToDecrement = $validated['seats'] ?? 1;
                if ($seatsToDecrement > 0) {
                    $trip->decrement('available_seats', $seatsToDecrement);
                }
            } else {
                $volumeToDecrement = $validated['volume'] ?? 0;
                if ($volumeToDecrement > 0) {
                    $trip->decrement('available_volume', $volumeToDecrement);
                }
            }

            DB::commit();

            // Notifier le conducteur de la nouvelle réservation
            $trip->driver->notify(new NewBookingReceived($booking));

            return redirect()->route('bookings.show', $booking)
                ->with('success', 'Réservation créée avec succès !');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Erreur lors de la réservation: ' . $e->getMessage(), [
                'exception' => $e,
                'trip_id' => $trip->id,
                'user_id' => Auth::id(),
                'validated' => $validated,
                'trace' => $e->getTraceAsString()
            ]);
            
            // Message d'erreur plus user-friendly
            $errorMessage = 'Une erreur est survenue lors de la réservation.';
            if (config('app.debug')) {
                $errorMessage .= ' ' . $e->getMessage();
            }
            
            return back()->withErrors(['error' => $errorMessage])->withInput();
        }
    }

    public function show(Booking $booking)
    {
        if ($booking->user_id !== Auth::id() && $booking->trip->driver_id !== Auth::id() && !Auth::user()->isAdmin()) {
            abort(403);
        }

        $booking->load(['trip.driver', 'user', 'messages.sender', 'messages.receiver']);

        return view('bookings.show', compact('booking'));
    }

    public function updateStatus(Request $request, Booking $booking)
    {
        if ($booking->trip->driver_id !== Auth::id() && !Auth::user()->isAdmin()) {
            abort(403);
        }

        $validated = $request->validate([
            'status' => 'required|in:pending,confirmed,in_transit,delivered,cancelled',
        ]);

        $oldStatus = $booking->status;
        $booking->update(['status' => $validated['status']]);

        // Notifier l'utilisateur du changement de statut
        $booking->user->notify(new BookingStatusChanged($booking, $oldStatus));

        // Si la réservation est confirmée, envoyer une notification spéciale
        if ($validated['status'] === 'confirmed' && $oldStatus !== 'confirmed') {
            $booking->user->notify(new BookingConfirmed($booking));
        }

        return back()->with('success', 'Statut mis à jour avec succès !');
    }

    public function myBookings()
    {
        $bookings = Booking::where('user_id', Auth::id())
            ->with(['trip.driver'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('bookings.my-bookings', compact('bookings'));
    }

    public function cancel(Booking $booking)
    {
        if ($booking->user_id !== Auth::id() && !Auth::user()->isAdmin()) {
            abort(403);
        }

        if (!in_array($booking->status, ['pending', 'confirmed'])) {
            return back()->withErrors(['error' => 'Cette réservation ne peut pas être annulée.']);
        }

        DB::beginTransaction();
        try {
            $booking->update(['status' => 'cancelled']);

            // Restaurer les disponibilités
            if ($booking->isPassenger()) {
                $booking->trip->increment('available_seats', $booking->seats);
            } else {
                $booking->trip->increment('available_volume', $booking->volume);
            }

            DB::commit();

            return back()->with('success', 'Réservation annulée avec succès !');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Une erreur est survenue lors de l\'annulation.']);
        }
    }
}

