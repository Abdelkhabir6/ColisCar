<?php

namespace App\Http\Controllers;

use App\Models\Trip;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TripController extends Controller
{
    public function index(Request $request)
    {
        $query = Trip::with('driver')
            ->where('status', '!=', 'cancelled');
        
        // Filtrer uniquement les trajets futurs si aucune date n'est spécifiée
        if (!$request->filled('date')) {
            $query->where('departure_date', '>=', now()->toDateString());
        }

        // Filtres de recherche
        if ($request->filled('departure')) {
            $departure = trim($request->departure);
            $query->where(function($q) use ($departure) {
                $q->whereRaw('LOWER(departure_city) LIKE ?', ['%' . mb_strtolower($departure, 'UTF-8') . '%']);
            });
        }

        if ($request->filled('arrival')) {
            $arrival = trim($request->arrival);
            $query->where(function($q) use ($arrival) {
                $q->whereRaw('LOWER(arrival_city) LIKE ?', ['%' . mb_strtolower($arrival, 'UTF-8') . '%']);
            });
        }

        if ($request->filled('date')) {
            $query->whereDate('departure_date', $request->date);
        }

        if ($request->filled('type')) {
            $query->where('trip_type', $request->type);
        }

        if ($request->filled('min_price')) {
            $query->where('price_per_seat', '>=', $request->min_price);
        }

        if ($request->filled('max_price')) {
            $query->where('price_per_seat', '<=', $request->max_price);
        }

        $trips = $query->orderBy('departure_date')->orderBy('departure_time')->paginate(12);

        return view('trips.index', compact('trips'));
    }

    public function show(Trip $trip)
    {
        $trip->load(['driver', 'bookings.user']);
        return view('trips.show', compact('trip'));
    }

    public function create()
    {
        return view('trips.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'departure_city' => 'required|string|max:255',
            'arrival_city' => 'required|string|max:255',
            'departure_latitude' => 'nullable|numeric',
            'departure_longitude' => 'nullable|numeric',
            'arrival_latitude' => 'nullable|numeric',
            'arrival_longitude' => 'nullable|numeric',
            'departure_date' => 'required|date|after_or_equal:today',
            'departure_time' => 'required',
            'price_per_seat' => 'required|numeric|min:0',
            'total_seats' => 'required|integer|min:1|max:8',
            'available_volume' => 'nullable|numeric|min:0',
            'max_weight' => 'nullable|numeric|min:0',
            'trip_type' => 'required|in:passengers,parcels,mixed',
            'description' => 'nullable|string',
        ]);

        // Convertir les chaînes vides en NULL pour les coordonnées
        $validated['departure_latitude'] = !empty($validated['departure_latitude']) ? $validated['departure_latitude'] : null;
        $validated['departure_longitude'] = !empty($validated['departure_longitude']) ? $validated['departure_longitude'] : null;
        $validated['arrival_latitude'] = !empty($validated['arrival_latitude']) ? $validated['arrival_latitude'] : null;
        $validated['arrival_longitude'] = !empty($validated['arrival_longitude']) ? $validated['arrival_longitude'] : null;
        $validated['available_volume'] = !empty($validated['available_volume']) ? $validated['available_volume'] : null;
        $validated['max_weight'] = !empty($validated['max_weight']) ? $validated['max_weight'] : null;

        $validated['driver_id'] = Auth::id();
        $validated['available_seats'] = $validated['total_seats'];

        Trip::create($validated);

        return redirect()->route('trips.my-trips')->with('success', 'Trajet publié avec succès !');
    }

    public function edit(Trip $trip)
    {
        if ($trip->driver_id !== Auth::id() && !Auth::user()->isAdmin()) {
            abort(403);
        }

        return view('trips.edit', compact('trip'));
    }

    public function update(Request $request, Trip $trip)
    {
        if ($trip->driver_id !== Auth::id() && !Auth::user()->isAdmin()) {
            abort(403);
        }

        $validated = $request->validate([
            'departure_city' => 'required|string|max:255',
            'arrival_city' => 'required|string|max:255',
            'departure_latitude' => 'nullable|numeric',
            'departure_longitude' => 'nullable|numeric',
            'arrival_latitude' => 'nullable|numeric',
            'arrival_longitude' => 'nullable|numeric',
            'departure_date' => 'required|date',
            'departure_time' => 'required',
            'price_per_seat' => 'required|numeric|min:0',
            'total_seats' => 'required|integer|min:1|max:8',
            'available_seats' => 'required|integer|min:0',
            'available_volume' => 'nullable|numeric|min:0',
            'max_weight' => 'nullable|numeric|min:0',
            'trip_type' => 'required|in:passengers,parcels,mixed',
            'description' => 'nullable|string',
            'status' => 'required|in:pending,confirmed,in_progress,completed,cancelled',
        ]);

        // Vérifier que available_seats ne dépasse pas total_seats
        if ($validated['available_seats'] > $validated['total_seats']) {
            return back()->withErrors(['available_seats' => 'Le nombre de places disponibles ne peut pas dépasser le nombre total de places.'])->withInput();
        }

        // Convertir les chaînes vides en NULL pour les coordonnées
        $validated['departure_latitude'] = !empty($validated['departure_latitude']) ? $validated['departure_latitude'] : null;
        $validated['departure_longitude'] = !empty($validated['departure_longitude']) ? $validated['departure_longitude'] : null;
        $validated['arrival_latitude'] = !empty($validated['arrival_latitude']) ? $validated['arrival_latitude'] : null;
        $validated['arrival_longitude'] = !empty($validated['arrival_longitude']) ? $validated['arrival_longitude'] : null;
        $validated['available_volume'] = !empty($validated['available_volume']) ? $validated['available_volume'] : null;
        $validated['max_weight'] = !empty($validated['max_weight']) ? $validated['max_weight'] : null;

        $trip->update($validated);

        return redirect()->route('trips.my-trips')->with('success', 'Trajet mis à jour avec succès !');
    }

    public function destroy(Trip $trip)
    {
        if ($trip->driver_id !== Auth::id() && !Auth::user()->isAdmin()) {
            abort(403);
        }

        $trip->delete();

        return redirect()->route('trips.my-trips')->with('success', 'Trajet supprimé avec succès !');
    }

    public function myTrips()
    {
        $trips = Trip::where('driver_id', Auth::id())
            ->withCount('bookings')
            ->orderBy('departure_date', 'desc')
            ->paginate(10);

        return view('trips.my-trips', compact('trips'));
    }
}

