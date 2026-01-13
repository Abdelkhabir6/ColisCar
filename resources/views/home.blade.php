@extends('layouts.app')

@section('title', 'Accueil')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Hero Section -->
    <div class="text-center mb-12">
        <h1 class="text-4xl font-bold text-gray-900 mb-4">ColisCar</h1>
        <p class="text-xl text-gray-600 mb-8">Covoiturage et transport de colis en un seul service</p>
        
        <div class="max-w-3xl mx-auto">
            <form action="{{ route('trips.index') }}" method="GET" class="bg-white rounded-lg shadow-lg p-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Départ</label>
                        <input type="text" id="home_departure" name="departure" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500" placeholder="Ville de départ" autocomplete="off">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Arrivée</label>
                        <input type="text" id="home_arrival" name="arrival" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500" placeholder="Ville d'arrivée" autocomplete="off">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Date</label>
                        <input type="date" name="date" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500" min="{{ date('Y-m-d') }}">
                    </div>
                </div>
                <button type="submit" class="mt-4 w-full bg-blue-600 text-white px-6 py-3 rounded-md font-medium hover:bg-blue-700 transition">Rechercher</button>
            </form>
        </div>
    </div>

    <!-- Featured Trips -->
    @if($featuredTrips->count() > 0)
    <div class="mb-12">
        <h2 class="text-2xl font-bold text-gray-900 mb-6">Trajets récents</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($featuredTrips as $trip)
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
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-2xl font-bold text-blue-600">{{ number_format($trip->price_per_seat, 2) }} €</p>
                            <p class="text-sm text-gray-500">{{ $trip->available_seats }} places disponibles</p>
                        </div>
                        <a href="{{ route('trips.show', $trip) }}" class="bg-blue-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-blue-700">Voir</a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Features -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mt-12">
        <div class="text-center">
            <div class="bg-blue-100 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
            </div>
            <h3 class="text-lg font-semibold mb-2">Covoiturage</h3>
            <p class="text-gray-600">Partagez vos trajets et économisez sur vos déplacements</p>
        </div>
        <div class="text-center">
            <div class="bg-green-100 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                </svg>
            </div>
            <h3 class="text-lg font-semibold mb-2">Transport de colis</h3>
            <p class="text-gray-600">Envoyez vos colis rapidement et à moindre coût</p>
        </div>
        <div class="text-center">
            <div class="bg-purple-100 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                </svg>
            </div>
            <h3 class="text-lg font-semibold mb-2">Sécurisé</h3>
            <p class="text-gray-600">Paiement sécurisé et suivi en temps réel</p>
        </div>
    </div>
</div>

<script>
function initHomeAutocomplete() {
    // Attendre que Google Maps soit chargé
    if (typeof google === 'undefined' || !google.maps || !google.maps.places) {
        // Réessayer après un court délai
        setTimeout(initHomeAutocomplete, 500);
        return;
    }

    // Configuration pour limiter à la France
    const options = {
        componentRestrictions: { country: 'fr' },
        types: ['(cities)']
    };

    // Autocomplétion pour le champ départ
    const departureInput = document.getElementById('home_departure');
    if (departureInput) {
        const departureAutocomplete = new google.maps.places.Autocomplete(departureInput, options);
        departureAutocomplete.addListener('place_changed', function() {
            const place = departureAutocomplete.getPlace();
            if (place.address_components) {
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
    const arrivalInput = document.getElementById('home_arrival');
    if (arrivalInput) {
        const arrivalAutocomplete = new google.maps.places.Autocomplete(arrivalInput, options);
        arrivalAutocomplete.addListener('place_changed', function() {
            const place = arrivalAutocomplete.getPlace();
            if (place.address_components) {
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
    document.addEventListener('DOMContentLoaded', initHomeAutocomplete);
} else {
    initHomeAutocomplete();
}
</script>
@endsection

