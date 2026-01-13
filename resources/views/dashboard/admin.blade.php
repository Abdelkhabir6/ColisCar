@extends('layouts.app')

@section('title', 'Tableau de bord - Admin')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <h1 class="text-3xl font-bold text-gray-900 mb-6">Tableau de bord - Administrateur</h1>

    <!-- Statistiques -->
    <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-7 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-sm font-medium text-gray-500 mb-2">Utilisateurs</h3>
            <p class="text-3xl font-bold text-blue-600">{{ $stats['total_users'] }}</p>
        </div>
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-sm font-medium text-gray-500 mb-2">Trajets</h3>
            <p class="text-3xl font-bold text-green-600">{{ $stats['total_trips'] }}</p>
        </div>
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-sm font-medium text-gray-500 mb-2">Réservations</h3>
            <p class="text-3xl font-bold text-purple-600">{{ $stats['total_bookings'] }}</p>
        </div>
        <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-yellow-500">
            <h3 class="text-sm font-medium text-gray-500 mb-2">Réservations en attente</h3>
            <p class="text-3xl font-bold text-yellow-600">{{ $stats['pending_bookings'] ?? 0 }}</p>
            @if(($stats['pending_bookings'] ?? 0) > 0)
            <a href="{{ route('admin.bookings', ['status' => 'pending']) }}" class="text-xs text-yellow-600 hover:text-yellow-800 mt-1 block">Voir toutes →</a>
            @endif
        </div>
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-sm font-medium text-gray-500 mb-2">Revenus</h3>
            <p class="text-3xl font-bold text-green-600">{{ number_format($stats['total_revenue'], 2) }} €</p>
        </div>
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-sm font-medium text-gray-500 mb-2">Trajets en attente</h3>
            <p class="text-3xl font-bold text-yellow-600">{{ $stats['pending_trips'] }}</p>
        </div>
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-sm font-medium text-gray-500 mb-2">Trajets actifs</h3>
            <p class="text-3xl font-bold text-blue-600">{{ $stats['active_trips'] }}</p>
        </div>
    </div>

    <!-- Trajets récents -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-8">
        <h2 class="text-xl font-semibold mb-4">Trajets récents</h2>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Conducteur</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Trajet</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Réservations</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statut</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($recentTrips as $trip)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ $trip->driver->name }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ $trip->departure_city }} → {{ $trip->arrival_city }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="text-sm text-gray-500">{{ $trip->departure_date->format('d/m/Y') }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="text-sm text-gray-500">{{ $trip->bookings_count }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                {{ ucfirst($trip->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <a href="{{ route('trips.show', $trip) }}" class="text-blue-600 hover:text-blue-900 mr-3">Voir</a>
                            <a href="{{ route('admin.trips.edit', $trip) }}" class="text-green-600 hover:text-green-900 mr-3">Modifier</a>
                            <form action="{{ route('admin.trips.delete', $trip) }}" method="POST" class="inline" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce trajet ?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-900">Supprimer</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">Aucun trajet</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

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
                    <tr class="{{ $booking->status === 'pending' ? 'bg-yellow-50' : '' }}">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ $booking->user->name }}</div>
                            <div class="text-xs text-gray-500">{{ $booking->user->email }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ $booking->trip->departure_city }} → {{ $booking->trip->arrival_city }}</div>
                            <div class="text-xs text-gray-500">Par {{ $booking->trip->driver->name }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $booking->isPassenger() ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800' }}">
                                {{ $booking->isPassenger() ? 'Passager' : 'Colis' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="text-sm font-medium text-blue-600">{{ number_format($booking->price, 2) }} €</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 text-xs font-semibold rounded-full 
                                {{ $booking->status === 'confirmed' ? 'bg-green-100 text-green-800' : '' }}
                                {{ $booking->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                {{ $booking->status === 'in_transit' ? 'bg-blue-100 text-blue-800' : '' }}
                                {{ $booking->status === 'delivered' ? 'bg-gray-100 text-gray-800' : '' }}
                                {{ $booking->status === 'cancelled' ? 'bg-red-100 text-red-800' : '' }}">
                                {{ ucfirst(str_replace('_', ' ', $booking->status)) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex space-x-2">
                                @if($booking->status === 'pending')
                                <form action="{{ route('admin.bookings.confirm', $booking) }}" method="POST" class="inline" onsubmit="return confirm('Confirmer cette réservation ?');">
                                    @csrf
                                    <button type="submit" class="text-green-600 hover:text-green-900 font-semibold" title="Confirmer rapidement">
                                        ✓
                                    </button>
                                </form>
                                <span class="text-gray-300">|</span>
                                @endif
                                <a href="{{ route('bookings.show', $booking) }}" class="text-blue-600 hover:text-blue-900">Voir</a>
                            </div>
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

