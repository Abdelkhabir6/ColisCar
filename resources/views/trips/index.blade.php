@extends('layouts.app')

@section('title', 'Rechercher un trajet')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <h1 class="text-3xl font-bold text-gray-900 mb-6">Rechercher un trajet</h1>

    <!-- Filtres -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <form method="GET" action="{{ route('trips.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Départ</label>
                <input type="text" id="departure" name="departure" value="{{ request('departure') }}" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500" placeholder="Ville de départ" autocomplete="off">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Arrivée</label>
                <input type="text" id="arrival" name="arrival" value="{{ request('arrival') }}" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500" placeholder="Ville d'arrivée" autocomplete="off">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Date</label>
                <input type="date" name="date" value="{{ request('date') }}" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500" min="{{ date('Y-m-d') }}">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Type</label>
                <select name="type" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Tous</option>
                    <option value="passengers" {{ request('type') == 'passengers' ? 'selected' : '' }}>Passagers</option>
                    <option value="parcels" {{ request('type') == 'parcels' ? 'selected' : '' }}>Colis</option>
                    <option value="mixed" {{ request('type') == 'mixed' ? 'selected' : '' }}>Mixte</option>
                </select>
            </div>
            <div class="md:col-span-4">
                <button type="submit" class="w-full bg-blue-600 text-white px-6 py-2 rounded-md font-medium hover:bg-blue-700">Rechercher</button>
            </div>
        </form>
    </div>

    <!-- Résultats -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($trips as $trip)
        <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition">
            <div class="p-6">
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">{{ $trip->departure_city }} → {{ $trip->arrival_city }}</h3>
                        <p class="text-sm text-gray-500">{{ $trip->departure_date->format('d/m/Y') }} à {{ date('H:i', strtotime($trip->departure_time)) }}</p>
                    </div>
                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                        {{ ucfirst($trip->trip_type) }}
                    </span>
                </div>
                <div class="mb-4">
                    <p class="text-sm text-gray-600">Conducteur: <span class="font-medium">{{ $trip->driver->name }}</span></p>
                    <p class="text-sm text-gray-600">Note: {{ number_format($trip->driver->rating, 1) }}/5 ({{ $trip->driver->total_ratings }} avis)</p>
                </div>
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-2xl font-bold text-blue-600">{{ number_format($trip->price_per_seat, 2) }} €</p>
                        @if($trip->trip_type === 'passengers' || $trip->trip_type === 'mixed')
                            <p class="text-sm text-gray-500">{{ $trip->available_seats }} places disponibles</p>
                        @endif
                        @if($trip->trip_type === 'parcels' || $trip->trip_type === 'mixed')
                            <p class="text-sm text-gray-500">{{ $trip->available_volume }} m³ disponibles</p>
                        @endif
                    </div>
                    <a href="{{ route('trips.show', $trip) }}" class="bg-blue-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-blue-700">Voir détails</a>
                </div>
            </div>
        </div>
        @empty
        <div class="col-span-full text-center py-12">
            <p class="text-gray-500 text-lg">Aucun trajet trouvé pour votre recherche.</p>
        </div>
        @endforelse
    </div>

    <!-- Pagination -->
    <div class="mt-6">
        {{ $trips->links() }}
    </div>
</div>

<script>
function initAutocomplete() {
    // Attendre que Google Maps soit chargé
    if (typeof google === 'undefined' || !google.maps || !google.maps.places) {
        // Réessayer après un court délai
        setTimeout(initAutocomplete, 500);
        return;
    }

    // Configuration pour limiter à la France
    const options = {
        componentRestrictions: { country: 'fr' },
        types: ['(cities)']
    };

    // Autocomplétion pour le champ départ
    const departureInput = document.getElementById('departure');
    if (departureInput) {
        const departureAutocomplete = new google.maps.places.Autocomplete(departureInput, options);
        departureAutocomplete.addListener('place_changed', function() {
            const place = departureAutocomplete.getPlace();
            if (place.address_components) {
                // Extraire uniquement le nom de la ville
                const cityComponent = place.address_components.find(component => 
                    component.types.includes('locality')
                );
                if (cityComponent) {
                    departureInput.value = cityComponent.long_name;
                } else {
                    departureInput.value = place.name;
                }
            }
        });
    }

    // Autocomplétion pour le champ arrivée
    const arrivalInput = document.getElementById('arrival');
    if (arrivalInput) {
        const arrivalAutocomplete = new google.maps.places.Autocomplete(arrivalInput, options);
        arrivalAutocomplete.addListener('place_changed', function() {
            const place = arrivalAutocomplete.getPlace();
            if (place.address_components) {
                // Extraire uniquement le nom de la ville
                const cityComponent = place.address_components.find(component => 
                    component.types.includes('locality')
                );
                if (cityComponent) {
                    arrivalInput.value = cityComponent.long_name;
                } else {
                    arrivalInput.value = place.name;
                }
            }
        });
    }
}

// Initialiser quand la page est chargée
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initAutocomplete);
} else {
    initAutocomplete();
}
</script>
@endsection

