@extends('layouts.app')

@section('title', 'Gestion des réservations')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-900">Gestion des réservations</h1>
        <a href="{{ route('dashboard') }}" class="bg-gray-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-gray-700">Retour au tableau de bord</a>
    </div>

    <!-- Statistiques -->
    <div class="grid grid-cols-2 md:grid-cols-6 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow-md p-4">
            <h3 class="text-sm font-medium text-gray-500 mb-1">Total</h3>
            <p class="text-2xl font-bold text-gray-900">{{ $stats['total'] }}</p>
        </div>
        <div class="bg-white rounded-lg shadow-md p-4 border-l-4 border-yellow-500">
            <h3 class="text-sm font-medium text-gray-500 mb-1">En attente</h3>
            <p class="text-2xl font-bold text-yellow-600">{{ $stats['pending'] }}</p>
        </div>
        <div class="bg-white rounded-lg shadow-md p-4 border-l-4 border-green-500">
            <h3 class="text-sm font-medium text-gray-500 mb-1">Confirmées</h3>
            <p class="text-2xl font-bold text-green-600">{{ $stats['confirmed'] }}</p>
        </div>
        <div class="bg-white rounded-lg shadow-md p-4 border-l-4 border-blue-500">
            <h3 class="text-sm font-medium text-gray-500 mb-1">En transit</h3>
            <p class="text-2xl font-bold text-blue-600">{{ $stats['in_transit'] }}</p>
        </div>
        <div class="bg-white rounded-lg shadow-md p-4 border-l-4 border-gray-500">
            <h3 class="text-sm font-medium text-gray-500 mb-1">Livrées</h3>
            <p class="text-2xl font-bold text-gray-600">{{ $stats['delivered'] }}</p>
        </div>
        <div class="bg-white rounded-lg shadow-md p-4 border-l-4 border-red-500">
            <h3 class="text-sm font-medium text-gray-500 mb-1">Annulées</h3>
            <p class="text-2xl font-bold text-red-600">{{ $stats['cancelled'] }}</p>
        </div>
    </div>

    <!-- Filtres -->
    <div class="bg-white rounded-lg shadow-md p-4 mb-6">
        <form method="GET" action="{{ route('admin.bookings') }}" class="flex flex-wrap gap-4">
            <div class="flex-1 min-w-[200px]">
                <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Filtrer par statut</label>
                <select name="status" id="status" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Tous les statuts</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>En attente</option>
                    <option value="confirmed" {{ request('status') === 'confirmed' ? 'selected' : '' }}>Confirmées</option>
                    <option value="in_transit" {{ request('status') === 'in_transit' ? 'selected' : '' }}>En transit</option>
                    <option value="delivered" {{ request('status') === 'delivered' ? 'selected' : '' }}>Livrées</option>
                    <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Annulées</option>
                </select>
            </div>
            <div class="flex-1 min-w-[200px]">
                <label for="type" class="block text-sm font-medium text-gray-700 mb-1">Filtrer par type</label>
                <select name="type" id="type" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Tous les types</option>
                    <option value="passenger" {{ request('type') === 'passenger' ? 'selected' : '' }}>Passager</option>
                    <option value="parcel" {{ request('type') === 'parcel' ? 'selected' : '' }}>Colis</option>
                </select>
            </div>
            <div class="flex items-end">
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">Filtrer</button>
                @if(request('status') || request('type'))
                <a href="{{ route('admin.bookings') }}" class="ml-2 px-6 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">Réinitialiser</a>
                @endif
            </div>
        </form>
    </div>

    @if(session('success'))
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
        {{ session('success') }}
    </div>
    @endif

    @if(session('error'))
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
        {{ session('error') }}
    </div>
    @endif

    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Utilisateur</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Trajet</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Prix</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statut</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($bookings as $booking)
                <tr class="{{ $booking->status === 'pending' ? 'bg-yellow-50' : '' }} hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-gray-900">{{ $booking->user->name }}</div>
                        <div class="text-sm text-gray-500">{{ $booking->user->email }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900">{{ $booking->trip->departure_city }} → {{ $booking->trip->arrival_city }}</div>
                        <div class="text-sm text-gray-500">Conducteur: {{ $booking->trip->driver->name }}</div>
                        <div class="text-xs text-gray-400">{{ $booking->trip->departure_date->format('d/m/Y') }} à {{ date('H:i', strtotime($booking->trip->departure_time)) }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $booking->isPassenger() ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800' }}">
                            {{ $booking->isPassenger() ? 'Passager' : 'Colis' }}
                        </span>
                        @if($booking->isPassenger())
                        <div class="text-xs text-gray-500 mt-1">{{ $booking->seats }} place(s)</div>
                        @else
                        <div class="text-xs text-gray-500 mt-1">{{ $booking->weight }} kg / {{ $booking->volume }} m³</div>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="text-sm font-medium text-blue-600">{{ number_format($booking->price, 2) }} €</span>
                        @if($booking->is_paid)
                        <div class="text-xs text-green-600 mt-1">✓ Payé</div>
                        @else
                        <div class="text-xs text-yellow-600 mt-1">En attente</div>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <form action="{{ route('admin.bookings.update-status', $booking) }}" method="POST" class="inline">
                            @csrf
                            @method('PUT')
                            <select name="status" onchange="this.form.submit()" class="text-xs border border-gray-300 rounded px-2 py-1 font-semibold
                                {{ $booking->status === 'confirmed' ? 'bg-green-100 text-green-800' : '' }}
                                {{ $booking->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                {{ $booking->status === 'in_transit' ? 'bg-blue-100 text-blue-800' : '' }}
                                {{ $booking->status === 'delivered' ? 'bg-gray-100 text-gray-800' : '' }}
                                {{ $booking->status === 'cancelled' ? 'bg-red-100 text-red-800' : '' }}">
                                <option value="pending" {{ $booking->status === 'pending' ? 'selected' : '' }}>En attente</option>
                                <option value="confirmed" {{ $booking->status === 'confirmed' ? 'selected' : '' }}>Confirmé</option>
                                <option value="in_transit" {{ $booking->status === 'in_transit' ? 'selected' : '' }}>En transit</option>
                                <option value="delivered" {{ $booking->status === 'delivered' ? 'selected' : '' }}>Livré</option>
                                <option value="cancelled" {{ $booking->status === 'cancelled' ? 'selected' : '' }}>Annulé</option>
                            </select>
                        </form>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="text-sm text-gray-500">{{ $booking->created_at->format('d/m/Y H:i') }}</span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <div class="flex space-x-2">
                            @if($booking->status === 'pending')
                            <form action="{{ route('admin.bookings.confirm', $booking) }}" method="POST" class="inline" onsubmit="return confirm('Confirmer cette réservation ?');">
                                @csrf
                                <button type="submit" class="text-green-600 hover:text-green-900 font-semibold" title="Confirmer rapidement">
                                    ✓ Confirmer
                                </button>
                            </form>
                            <span class="text-gray-300">|</span>
                            @endif
                            <a href="{{ route('bookings.show', $booking) }}" class="text-blue-600 hover:text-blue-900">Voir détails</a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-6 py-4 text-center text-gray-500">Aucune réservation trouvée</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">
        {{ $bookings->links() }}
    </div>
</div>
@endsection
