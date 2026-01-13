@extends('layouts.app')

@section('title', 'Détails de la réservation')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Réservation #{{ $booking->id }}</h1>
                        <p class="text-gray-600 mt-2">
                            Trajet: {{ $booking->trip->departure_city }} → {{ $booking->trip->arrival_city }}
                        </p>
                        <p class="text-gray-600">
                            {{ $booking->trip->departure_date->format('d/m/Y') }} à {{ date('H:i', strtotime($booking->trip->departure_time)) }}
                        </p>
                    </div>
                    <span class="px-3 py-1 text-sm font-semibold rounded-full 
                        {{ $booking->status === 'confirmed' ? 'bg-green-100 text-green-800' : '' }}
                        {{ $booking->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                        {{ $booking->status === 'in_transit' ? 'bg-blue-100 text-blue-800' : '' }}
                        {{ $booking->status === 'delivered' ? 'bg-gray-100 text-gray-800' : '' }}
                        {{ $booking->status === 'cancelled' ? 'bg-red-100 text-red-800' : '' }}">
                        {{ ucfirst(str_replace('_', ' ', $booking->status)) }}
                    </span>
                </div>

                <div class="border-t border-gray-200 pt-4 mt-4">
                    <h2 class="text-lg font-semibold mb-4">Détails</h2>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-500">Type</p>
                            <p class="font-medium">{{ $booking->isPassenger() ? 'Passager' : 'Colis' }}</p>
                        </div>
                        @if($booking->isPassenger())
                        <div>
                            <p class="text-sm text-gray-500">Nombre de places</p>
                            <p class="font-medium">{{ $booking->seats }}</p>
                        </div>
                        @else
                        <div>
                            <p class="text-sm text-gray-500">Poids</p>
                            <p class="font-medium">{{ $booking->weight }} kg</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Volume</p>
                            <p class="font-medium">{{ $booking->volume }} m³</p>
                        </div>
                        @if($booking->dimensions)
                        <div>
                            <p class="text-sm text-gray-500">Dimensions</p>
                            <p class="font-medium">{{ $booking->dimensions['length'] ?? '-' }} x {{ $booking->dimensions['width'] ?? '-' }} x {{ $booking->dimensions['height'] ?? '-' }} cm</p>
                        </div>
                        @endif
                        @endif
                        <div>
                            <p class="text-sm text-gray-500">Prix</p>
                            <p class="text-xl font-bold text-blue-600">{{ number_format($booking->price, 2) }} €</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Paiement</p>
                            <p class="font-medium">{{ $booking->is_paid ? 'Payé' : 'En attente' }}</p>
                        </div>
                    </div>
                </div>

                @if($booking->instructions)
                <div class="border-t border-gray-200 pt-4 mt-4">
                    <h2 class="text-lg font-semibold mb-2">Instructions</h2>
                    <p class="text-gray-600">{{ $booking->instructions }}</p>
                </div>
                @endif

                @if($booking->trip->driver_id === auth()->id() || auth()->user()->isAdmin())
                <div class="border-t border-gray-200 pt-4 mt-4">
                    <h2 class="text-lg font-semibold mb-4">Mettre à jour le statut</h2>
                    <form action="{{ route('bookings.update-status', $booking) }}" method="POST" class="flex space-x-2">
                        @csrf
                        @method('PUT')
                        <select name="status" class="flex-1 px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                            <option value="pending" {{ $booking->status === 'pending' ? 'selected' : '' }}>En attente</option>
                            <option value="confirmed" {{ $booking->status === 'confirmed' ? 'selected' : '' }}>Confirmé</option>
                            <option value="in_transit" {{ $booking->status === 'in_transit' ? 'selected' : '' }}>En transit</option>
                            <option value="delivered" {{ $booking->status === 'delivered' ? 'selected' : '' }}>Livré</option>
                            <option value="cancelled" {{ $booking->status === 'cancelled' ? 'selected' : '' }}>Annulé</option>
                        </select>
                        <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">Mettre à jour</button>
                    </form>
                </div>
                @endif

                @if($booking->user_id === auth()->id() && in_array($booking->status, ['pending', 'confirmed']))
                <div class="border-t border-gray-200 pt-4 mt-4">
                    <form action="{{ route('bookings.cancel', $booking) }}" method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir annuler cette réservation ?');">
                        @csrf
                        <button type="submit" class="px-6 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">Annuler la réservation</button>
                    </form>
                </div>
                @endif
            </div>

            <!-- Messagerie -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-semibold mb-4">Messages</h2>
                <div class="space-y-4 mb-4" style="max-height: 400px; overflow-y: auto;">
                    @foreach($booking->messages as $message)
                    <div class="flex {{ $message->sender_id === auth()->id() ? 'justify-end' : 'justify-start' }}">
                        <div class="max-w-xs lg:max-w-md px-4 py-2 rounded-lg {{ $message->sender_id === auth()->id() ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-800' }}">
                            <p class="text-sm font-medium">{{ $message->sender->name }}</p>
                            <p class="mt-1">{{ $message->content }}</p>
                            <p class="text-xs mt-1 opacity-75">{{ $message->created_at->format('d/m/Y H:i') }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
                <form action="{{ route('messages.store', $booking) }}" method="POST" class="flex space-x-2">
                    @csrf
                    <input type="text" name="content" required class="flex-1 px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500" placeholder="Tapez votre message...">
                    <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">Envoyer</button>
                </form>
            </div>
        </div>

        <div class="lg:col-span-1">
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-lg font-semibold mb-4">Contact</h2>
                <div class="space-y-4">
                    <div>
                        <p class="text-sm text-gray-500">Conducteur</p>
                        <p class="font-medium">{{ $booking->trip->driver->name }}</p>
                        <p class="text-sm text-gray-600">Note: {{ number_format($booking->trip->driver->rating, 1) }}/5</p>
                    </div>
                    @if($booking->user_id === auth()->id())
                    <div>
                        <p class="text-sm text-gray-500">Passager/Expéditeur</p>
                        <p class="font-medium">{{ $booking->user->name }}</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

