@extends('layouts.app')

@section('title', 'Modifier le trajet')

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <h1 class="text-3xl font-bold text-gray-900 mb-6">Modifier le trajet</h1>

    <form action="{{ route('trips.update', $trip) }}" method="POST" class="bg-white rounded-lg shadow-md p-6 space-y-6">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Ville de départ *</label>
                <input type="text" name="departure_city" required class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500" value="{{ old('departure_city', $trip->departure_city) }}">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Ville d'arrivée *</label>
                <input type="text" name="arrival_city" required class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500" value="{{ old('arrival_city', $trip->arrival_city) }}">
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Date de départ *</label>
                <input type="date" name="departure_date" required class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500" value="{{ old('departure_date', $trip->departure_date->format('Y-m-d')) }}">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Heure de départ *</label>
                <input type="time" name="departure_time" required class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500" value="{{ old('departure_time', date('H:i', strtotime($trip->departure_time))) }}">
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Type de trajet *</label>
            <select name="trip_type" required class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                <option value="passengers" {{ old('trip_type', $trip->trip_type) == 'passengers' ? 'selected' : '' }}>Passagers uniquement</option>
                <option value="parcels" {{ old('trip_type', $trip->trip_type) == 'parcels' ? 'selected' : '' }}>Colis uniquement</option>
                <option value="mixed" {{ old('trip_type', $trip->trip_type) == 'mixed' ? 'selected' : '' }}>Mixte</option>
            </select>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Prix par place/unité (€) *</label>
                <input type="number" name="price_per_seat" step="0.01" min="0" required class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500" value="{{ old('price_per_seat', $trip->price_per_seat) }}">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Nombre total de places *</label>
                <input type="number" name="total_seats" min="1" max="8" required class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500" value="{{ old('total_seats', $trip->total_seats) }}">
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Places disponibles *</label>
                <input type="number" name="available_seats" min="0" max="{{ $trip->total_seats }}" required class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500" value="{{ old('available_seats', $trip->available_seats) }}">
                <p class="mt-1 text-sm text-gray-500">Doit être inférieur ou égal au nombre total de places</p>
            </div>
            @if(in_array($trip->trip_type, ['parcels', 'mixed']))
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Volume disponible (m³)</label>
                <input type="number" name="available_volume" step="0.01" min="0" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500" value="{{ old('available_volume', $trip->available_volume) }}">
            </div>
            @endif
        </div>

        @if(in_array($trip->trip_type, ['parcels', 'mixed']))
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Poids maximum accepté (kg)</label>
            <input type="number" name="max_weight" step="0.01" min="0" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500" value="{{ old('max_weight', $trip->max_weight) }}">
        </div>
        @endif

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Statut *</label>
            <select name="status" required class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                <option value="pending" {{ old('status', $trip->status) == 'pending' ? 'selected' : '' }}>En attente</option>
                <option value="confirmed" {{ old('status', $trip->status) == 'confirmed' ? 'selected' : '' }}>Confirmé</option>
                <option value="in_progress" {{ old('status', $trip->status) == 'in_progress' ? 'selected' : '' }}>En cours</option>
                <option value="completed" {{ old('status', $trip->status) == 'completed' ? 'selected' : '' }}>Terminé</option>
                <option value="cancelled" {{ old('status', $trip->status) == 'cancelled' ? 'selected' : '' }}>Annulé</option>
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Description (optionnel)</label>
            <textarea name="description" rows="4" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">{{ old('description', $trip->description) }}</textarea>
        </div>

        <div class="flex justify-end space-x-4">
            <a href="{{ route('trips.my-trips') }}" class="px-6 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">Annuler</a>
            <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">Enregistrer</button>
        </div>
    </form>
</div>

<script>
    // Mettre à jour la valeur max de available_seats quand total_seats change
    document.addEventListener('DOMContentLoaded', function() {
        const totalSeatsInput = document.querySelector('[name="total_seats"]');
        const availableSeatsInput = document.querySelector('[name="available_seats"]');
        
        if (totalSeatsInput && availableSeatsInput) {
            totalSeatsInput.addEventListener('input', function() {
                const maxSeats = parseInt(this.value) || 0;
                availableSeatsInput.setAttribute('max', maxSeats);
                
                // Si available_seats dépasse le nouveau max, le réduire
                if (parseInt(availableSeatsInput.value) > maxSeats) {
                    availableSeatsInput.value = maxSeats;
                }
            });
        }
    });
</script>
@endsection

