@extends('layouts.app')

@section('title', 'Tableau de bord - Conducteur')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <h1 class="text-3xl font-bold text-gray-900 mb-6">Tableau de bord - Conducteur</h1>

    <!-- Statistiques -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-sm font-medium text-gray-500 mb-2">Total des trajets</h3>
            <p class="text-3xl font-bold text-blue-600">{{ $stats['total_trips'] }}</p>
        </div>
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-sm font-medium text-gray-500 mb-2">Trajets actifs</h3>
            <p class="text-3xl font-bold text-green-600">{{ $stats['active_trips'] }}</p>
        </div>
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-sm font-medium text-gray-500 mb-2">Total des réservations</h3>
            <p class="text-3xl font-bold text-purple-600">{{ $stats['total_bookings'] }}</p>
        </div>
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-sm font-medium text-gray-500 mb-2">Revenus totaux</h3>
            <p class="text-3xl font-bold text-green-600">{{ number_format($stats['total_revenue'], 2) }} €</p>
        </div>
    </div>

    <!-- Trajets à venir -->
    @if($upcomingTrips->count() > 0)
    <div class="bg-white rounded-lg shadow-md p-6 mb-8">
        <h2 class="text-xl font-semibold mb-4">Trajets à venir</h2>
        <div class="space-y-4">
            @foreach($upcomingTrips as $trip)
            <div class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50">
                <div class="flex justify-between items-start">
                    <div>
                        <h3 class="font-semibold">{{ $trip->departure_city }} → {{ $trip->arrival_city }}</h3>
                        <p class="text-sm text-gray-600">{{ $trip->departure_date->format('d/m/Y') }} à {{ date('H:i', strtotime($trip->departure_time)) }}</p>
                        <p class="text-sm text-gray-600 mt-1">{{ $trip->available_seats }} places disponibles</p>
                    </div>
                    <div class="text-right">
                        <p class="text-lg font-bold text-blue-600">{{ number_format($trip->price_per_seat, 2) }} €</p>
                        <a href="{{ route('trips.show', $trip) }}" class="text-blue-600 hover:text-blue-700 text-sm font-medium">Voir →</a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Réservations récentes -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-xl font-semibold mb-4">Réservations récentes</h2>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Utilisateur</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Trajet</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Prix</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statut</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($recentBookings as $booking)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ $booking->user->name }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ $booking->trip->departure_city }} → {{ $booking->trip->arrival_city }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="text-sm text-gray-500">{{ $booking->isPassenger() ? 'Passager' : 'Colis' }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="text-sm font-medium text-blue-600">{{ number_format($booking->price, 2) }} €</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 text-xs font-semibold rounded-full 
                                {{ $booking->status === 'confirmed' ? 'bg-green-100 text-green-800' : '' }}
                                {{ $booking->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                {{ $booking->status === 'in_transit' ? 'bg-blue-100 text-blue-800' : '' }}
                                {{ $booking->status === 'delivered' ? 'bg-gray-100 text-gray-800' : '' }}">
                                {{ ucfirst(str_replace('_', ' ', $booking->status)) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <a href="{{ route('bookings.show', $booking) }}" class="text-blue-600 hover:text-blue-900">Voir</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">Aucune réservation</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

