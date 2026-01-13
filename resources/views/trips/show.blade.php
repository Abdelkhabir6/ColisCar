@extends('layouts.app')

@section('title', 'Détails du trajet')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Informations principales -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ $trip->departure_city }} → {{ $trip->arrival_city }}</h1>
                        <p class="text-gray-600">{{ $trip->departure_date->format('l d F Y') }} à {{ date('H:i', strtotime($trip->departure_time)) }}</p>
                    </div>
                    <span class="px-3 py-1 text-sm font-semibold rounded-full bg-blue-100 text-blue-800">
                        {{ ucfirst($trip->trip_type) }}
                    </span>
                </div>

                <div class="border-t border-gray-200 pt-4 mt-4">
                    <h2 class="text-xl font-semibold mb-4">Informations du trajet</h2>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-500">Prix par place/unité</p>
                            <p class="text-2xl font-bold text-blue-600">{{ number_format($trip->price_per_seat, 2) }} €</p>
                        </div>
                        @if($trip->trip_type === 'passengers' || $trip->trip_type === 'mixed')
                        <div>
                            <p class="text-sm text-gray-500">Places disponibles</p>
                            <p class="text-xl font-semibold">{{ $trip->available_seats }} / {{ $trip->total_seats }}</p>
                        </div>
                        @endif
                        @if($trip->trip_type === 'parcels' || $trip->trip_type === 'mixed')
                        <div>
                            <p class="text-sm text-gray-500">Volume disponible</p>
                            <p class="text-xl font-semibold">{{ $trip->available_volume }} m³</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Poids maximum</p>
                            <p class="text-xl font-semibold">{{ $trip->max_weight ?? 'Illimité' }} kg</p>
                        </div>
                        @endif
                    </div>
                </div>

                @if($trip->description)
                <div class="border-t border-gray-200 pt-4 mt-4">
                    <h2 class="text-xl font-semibold mb-2">Description</h2>
                    <p class="text-gray-600">{{ $trip->description }}</p>
                </div>
                @endif

                <!-- Carte (si coordonnées disponibles) -->
                @if($trip->departure_latitude && $trip->arrival_latitude)
                <div class="border-t border-gray-200 pt-4 mt-4">
                    <h2 class="text-xl font-semibold mb-4">Itinéraire</h2>
                    <div id="map" style="height: 400px; width: 100%;" class="rounded-lg"></div>
                </div>
                @endif
            </div>

            <!-- Conducteur -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-semibold mb-4">Conducteur</h2>
                <div class="flex items-center space-x-4">
                    <div class="w-16 h-16 bg-gray-300 rounded-full flex items-center justify-center">
                        <span class="text-2xl font-semibold text-gray-600">{{ substr($trip->driver->name, 0, 1) }}</span>
                    </div>
                    <div>
                        <p class="font-semibold text-lg">{{ $trip->driver->name }}</p>
                        <p class="text-gray-600">Note: {{ number_format($trip->driver->rating, 1) }}/5 ({{ $trip->driver->total_ratings }} avis)</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Réservation -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-lg shadow-md p-6 sticky top-4">
                <h2 class="text-xl font-semibold mb-4">Réserver</h2>
                
                @auth
                    @if($trip->driver_id !== auth()->id())
                        @if($trip->canAcceptPassengers() || $trip->canAcceptParcels())
                            <form action="{{ route('bookings.store', $trip) }}" method="POST" class="space-y-4">
                                @csrf
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Type de réservation</label>
                                    <select name="booking_type" id="booking_type" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500" required>
                                        @if($trip->canAcceptPassengers())
                                            <option value="passenger" selected>Passager</option>
                                        @endif
                                        @if($trip->canAcceptParcels())
                                            <option value="parcel" {{ !$trip->canAcceptPassengers() ? 'selected' : '' }}>Colis</option>
                                        @endif
                                    </select>
                                </div>

                                <div id="passenger_fields" style="display: {{ $trip->canAcceptPassengers() ? 'block' : 'none' }};">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Nombre de places *</label>
                                        <input type="number" name="seats" id="seats_input" min="1" max="{{ $trip->available_seats }}" value="1" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500" {{ $trip->canAcceptPassengers() ? 'required' : '' }}>
                                    </div>
                                </div>

                                <div id="parcel_fields" style="display: none;">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Poids (kg) *</label>
                                        <input type="number" name="weight" id="weight_input" step="0.01" min="0" max="{{ $trip->max_weight ?? 999 }}" required class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Volume (m³) *</label>
                                        <input type="number" name="volume" id="volume_input" step="0.01" min="0" max="{{ $trip->available_volume }}" required class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Dimensions (cm)</label>
                                        <div class="grid grid-cols-3 gap-2">
                                            <input type="number" name="dimensions[length]" placeholder="Longueur" class="px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                                            <input type="number" name="dimensions[width]" placeholder="Largeur" class="px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                                            <input type="number" name="dimensions[height]" placeholder="Hauteur" class="px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Instructions (optionnel)</label>
                                    <textarea name="instructions" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500" placeholder="Instructions spéciales..."></textarea>
                                </div>

                                <button type="submit" class="w-full bg-blue-600 text-white px-6 py-3 rounded-md font-medium hover:bg-blue-700">Réserver</button>
                            </form>
                        @else
                            <p class="text-gray-500">Ce trajet n'accepte plus de réservations.</p>
                        @endif
                    @else
                        <p class="text-gray-500">Vous ne pouvez pas réserver votre propre trajet.</p>
                    @endif
                @else
                    <p class="text-gray-500 mb-4">Vous devez être connecté pour réserver.</p>
                    <a href="{{ route('login') }}" class="block w-full bg-blue-600 text-white px-6 py-3 rounded-md font-medium hover:bg-blue-700 text-center">Se connecter</a>
                @endauth
            </div>
        </div>
    </div>
</div>

<script>
function updateBookingFields() {
    const bookingType = document.getElementById('booking_type');
    if (!bookingType) return;
    
    const passengerFields = document.getElementById('passenger_fields');
    const parcelFields = document.getElementById('parcel_fields');
    const seatsInput = document.getElementById('seats_input');
    const weightInput = document.getElementById('weight_input');
    const volumeInput = document.getElementById('volume_input');
    
    if (bookingType.value === 'passenger') {
        if (passengerFields) passengerFields.style.display = 'block';
        if (parcelFields) parcelFields.style.display = 'none';
        if (seatsInput) {
            seatsInput.required = true;
            seatsInput.removeAttribute('disabled');
        }
        if (weightInput) {
            weightInput.required = false;
            weightInput.setAttribute('disabled', 'disabled');
        }
        if (volumeInput) {
            volumeInput.required = false;
            volumeInput.setAttribute('disabled', 'disabled');
        }
    } else {
        if (passengerFields) passengerFields.style.display = 'none';
        if (parcelFields) parcelFields.style.display = 'block';
        if (seatsInput) {
            seatsInput.required = false;
            seatsInput.setAttribute('disabled', 'disabled');
        }
        if (weightInput) {
            weightInput.required = true;
            weightInput.removeAttribute('disabled');
        }
        if (volumeInput) {
            volumeInput.required = true;
            volumeInput.removeAttribute('disabled');
        }
    }
}

// Initialiser au chargement de la page
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function() {
        updateBookingFields();
        const bookingType = document.getElementById('booking_type');
        if (bookingType) {
            bookingType.addEventListener('change', updateBookingFields);
        }
    });
} else {
    updateBookingFields();
    const bookingType = document.getElementById('booking_type');
    if (bookingType) {
        bookingType.addEventListener('change', updateBookingFields);
    }
}

@if($trip->departure_latitude && $trip->arrival_latitude)
// Initialiser la carte Google Maps
function initMap() {
    const departure = { lat: {{ $trip->departure_latitude }}, lng: {{ $trip->departure_longitude }} };
    const arrival = { lat: {{ $trip->arrival_latitude }}, lng: {{ $trip->arrival_longitude }} };
    
    const map = new google.maps.Map(document.getElementById('map'), {
        zoom: 6,
        center: departure
    });
    
    new google.maps.Marker({ position: departure, map: map, label: 'D' });
    new google.maps.Marker({ position: arrival, map: map, label: 'A' });
    
    const directionsService = new google.maps.DirectionsService();
    const directionsRenderer = new google.maps.DirectionsRenderer();
    directionsRenderer.setMap(map);
    
    directionsService.route({
        origin: departure,
        destination: arrival,
        travelMode: google.maps.TravelMode.DRIVING
    }, (response, status) => {
        if (status === 'OK') {
            directionsRenderer.setDirections(response);
        }
    });
}
initMap();
@endif
</script>
@endsection

