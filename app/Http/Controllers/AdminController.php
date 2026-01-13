<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Trip;
use App\Models\User;
use App\Notifications\BookingStatusChanged;
use App\Notifications\BookingConfirmed;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:admin');
    }

    // Gestion des utilisateurs
    public function users()
    {
        $users = User::withCount(['trips', 'bookings'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('admin.users', compact('users'));
    }

    public function editUser(User $user)
    {
        return view('admin.edit-user', compact('user'));
    }

    public function updateUser(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'role' => 'required|in:user,driver,sender,admin',
            'is_verified' => 'boolean',
        ]);

        if ($request->filled('password')) {
            $validated['password'] = Hash::make($request->password);
        }

        $user->update($validated);

        return redirect()->route('admin.users')->with('success', 'Utilisateur mis à jour avec succès !');
    }

    public function deleteUser(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->withErrors(['error' => 'Vous ne pouvez pas supprimer votre propre compte.']);
        }

        $user->delete();

        return redirect()->route('admin.users')->with('success', 'Utilisateur supprimé avec succès !');
    }

    // Gestion des trajets
    public function trips()
    {
        $trips = Trip::with(['driver'])
            ->withCount('bookings')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('admin.trips', compact('trips'));
    }

    public function editTrip(Trip $trip)
    {
        return view('trips.edit', compact('trip'));
    }

    public function deleteTrip(Trip $trip)
    {
        $trip->delete();

        return redirect()->route('admin.trips')->with('success', 'Trajet supprimé avec succès !');
    }

    // Gestion des réservations
    public function bookings(Request $request)
    {
        $query = Booking::with(['trip.driver', 'user']);
        
        // Filtre par statut
        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }
        
        // Filtre par type
        if ($request->has('type') && $request->type !== '') {
            $query->where('booking_type', $request->type);
        }
        
        $bookings = $query->orderBy('created_at', 'desc')->paginate(15);
        
        // Statistiques
        $stats = [
            'total' => Booking::count(),
            'pending' => Booking::where('status', 'pending')->count(),
            'confirmed' => Booking::where('status', 'confirmed')->count(),
            'in_transit' => Booking::where('status', 'in_transit')->count(),
            'delivered' => Booking::where('status', 'delivered')->count(),
            'cancelled' => Booking::where('status', 'cancelled')->count(),
        ];

        return view('admin.bookings', compact('bookings', 'stats'));
    }

    public function updateBookingStatus(Request $request, Booking $booking)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,confirmed,in_transit,delivered,cancelled',
        ]);

        $oldStatus = $booking->status;
        $booking->update(['status' => $validated['status']]);

        // Recharger la relation user pour s'assurer qu'elle est disponible
        $booking->load('user');

        try {
            // Notifier l'utilisateur du changement de statut
            $booking->user->notify(new BookingStatusChanged($booking, $oldStatus));

            // Si la réservation est confirmée, envoyer une notification spéciale
            if ($validated['status'] === 'confirmed' && $oldStatus !== 'confirmed') {
                $booking->user->notify(new BookingConfirmed($booking));
            }
        } catch (\Exception $e) {
            // Logger l'erreur mais ne pas bloquer la mise à jour du statut
            \Log::error('Erreur lors de l\'envoi de notification email: ' . $e->getMessage(), [
                'booking_id' => $booking->id,
                'user_id' => $booking->user_id,
                'error' => $e->getTraceAsString()
            ]);
        }

        $message = 'Statut de la réservation mis à jour !';
        if ($oldStatus === 'pending' && $validated['status'] === 'confirmed') {
            $message = 'Réservation confirmée avec succès !';
        }

        return back()->with('success', $message);
    }
    
    // Confirmation rapide d'une réservation
    public function confirmBooking(Booking $booking)
    {
        if ($booking->status === 'pending') {
            $booking->update(['status' => 'confirmed']);
            return back()->with('success', 'Réservation confirmée avec succès !');
        }
        
        return back()->withErrors(['error' => 'Cette réservation ne peut pas être confirmée.']);
    }
}

