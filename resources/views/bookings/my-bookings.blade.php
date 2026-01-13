@extends('layouts.app')

@section('title', 'Mes réservations')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <h1 class="text-3xl font-bold text-gray-900 mb-6">Mes réservations</h1>

    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Trajet</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Prix</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statut</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($bookings as $booking)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-gray-900">{{ $booking->trip->departure_city }} → {{ $booking->trip->arrival_city }}</div>
                        <div class="text-sm text-gray-500">Conducteur: {{ $booking->trip->driver->name }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-500">{{ $booking->trip->departure_date->format('d/m/Y') }} à {{ date('H:i', strtotime($booking->trip->departure_time)) }}</div>
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
                            {{ $booking->status === 'delivered' ? 'bg-gray-100 text-gray-800' : '' }}
                            {{ $booking->status === 'cancelled' ? 'bg-red-100 text-red-800' : '' }}">
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

    <div class="mt-6">
        {{ $bookings->links() }}
    </div>
</div>
@endsection

