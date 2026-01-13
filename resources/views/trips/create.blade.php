@extends('layouts.app')

@section('title', 'Publier un trajet')

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <h1 class="text-3xl font-bold text-gray-900 mb-6">Publier un trajet</h1>

    <form action="{{ route('trips.store') }}" method="POST" class="bg-white rounded-lg shadow-md p-6 space-y-6">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Ville de départ *</label>
                <input type="text" name="departure_city" required class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500" value="{{ old('departure_city') }}" id="departure_city">
                <input type="hidden" name="departure_latitude" id="departure_latitude" value="">
                <input type="hidden" name="departure_longitude" id="departure_longitude" value="">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Ville d'arrivée *</label>
                <input type="text" name="arrival_city" required class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500" value="{{ old('arrival_city') }}" id="arrival_city">
                <input type="hidden" name="arrival_latitude" id="arrival_latitude" value="">
                <input type="hidden" name="arrival_longitude" id="arrival_longitude" value="">
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Date de départ *</label>
                <input type="date" name="departure_date" required class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500" value="{{ old('departure_date') }}" min="{{ date('Y-m-d') }}">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Heure de départ *</label>
                <input type="time" name="departure_time" required class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500" value="{{ old('departure_time') }}">
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Type de trajet *</label>
            <select name="trip_type" required class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500" id="trip_type">
                <option value="passengers" {{ old('trip_type') == 'passengers' ? 'selected' : '' }}>Passagers uniquement</option>
                <option value="parcels" {{ old('trip_type') == 'parcels' ? 'selected' : '' }}>Colis uniquement</option>
                <option value="mixed" {{ old('trip_type') == 'mixed' ? 'selected' : '' }}>Mixte (passagers et colis)</option>
            </select>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Prix par place/unité (€) *</label>
                <input type="number" name="price_per_seat" step="0.01" min="0" required class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500" value="{{ old('price_per_seat') }}">
            </div>
            <div id="seats_field">
                <label class="block text-sm font-medium text-gray-700 mb-2">Nombre de places *</label>
                <input type="number" name="total_seats" min="1" max="8" required class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500" value="{{ old('total_seats', 4) }}">
            </div>
        </div>

        <div id="parcel_fields" style="display: none;">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Volume disponible (m³)</label>
                    <input type="number" name="available_volume" step="0.01" min="0" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500" value="{{ old('available_volume') }}">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Poids maximum (kg)</label>
                    <input type="number" name="max_weight" step="0.01" min="0" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500" value="{{ old('max_weight') }}">
                </div>
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Description (optionnel)</label>
            <textarea name="description" rows="4" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500" placeholder="Informations supplémentaires sur le trajet...">{{ old('description') }}</textarea>
        </div>

        <div class="flex justify-end space-x-4">
            <a href="{{ route('trips.index') }}" class="px-6 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">Annuler</a>
            <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">Publier le trajet</button>
        </div>
    </form>
</div>

<script>
document.getElementById('trip_type').addEventListener('change', function() {
    const seatsField = document.getElementById('seats_field');
    const parcelFields = document.getElementById('parcel_fields');
    
    if (this.value === 'parcels') {
        seatsField.style.display = 'none';
        parcelFields.style.display = 'block';
        document.querySelector('[name="total_seats"]').removeAttribute('required');
    } else {
        seatsField.style.display = 'block';
        parcelFields.style.display = this.value === 'mixed' ? 'block' : 'none';
        document.querySelector('[name="total_seats"]').setAttribute('required', 'required');
    }
});

// Initialiser l'affichage
document.getElementById('trip_type').dispatchEvent(new Event('change'));

// Autocomplete Google Maps
function initAutocomplete() {
    if (typeof google === 'undefined' || !google.maps || !google.maps.places) {
        setTimeout(initAutocomplete, 500);
        return;
    }

    const departureInput = document.getElementById('departure_city');
    const arrivalInput = document.getElementById('arrival_city');
    
    if (!departureInput || !arrivalInput) return;
    
    // Configuration pour limiter à la France
    const options = {
        componentRestrictions: { country: 'fr' },
        types: ['(cities)']
    };
    
    const departureAutocomplete = new google.maps.places.Autocomplete(departureInput, options);
    const arrivalAutocomplete = new google.maps.places.Autocomplete(arrivalInput, options);
    
    departureAutocomplete.addListener('place_changed', function() {
        const place = departureAutocomplete.getPlace();
        const latInput = document.getElementById('departure_latitude');
        const lngInput = document.getElementById('departure_longitude');
        if (place.geometry && place.geometry.location) {
            latInput.value = place.geometry.location.lat();
            lngInput.value = place.geometry.location.lng();
        } else {
            latInput.value = '';
            lngInput.value = '';
        }
    });
    
    arrivalAutocomplete.addListener('place_changed', function() {
        const place = arrivalAutocomplete.getPlace();
        const latInput = document.getElementById('arrival_latitude');
        const lngInput = document.getElementById('arrival_longitude');
        if (place.geometry && place.geometry.location) {
            latInput.value = place.geometry.location.lat();
            lngInput.value = place.geometry.location.lng();
        } else {
            latInput.value = '';
            lngInput.value = '';
        }
    });
}

// Initialiser quand la page est chargée
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initAutocomplete);
} else {
    initAutocomplete();
}
</script>
@endsection

