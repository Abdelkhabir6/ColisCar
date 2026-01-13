@extends('layouts.app')

@section('title', 'Modifier un utilisateur')

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-900">Modifier l'utilisateur</h1>
        <a href="{{ route('admin.users') }}" class="bg-gray-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-gray-700">Retour</a>
    </div>

    <form action="{{ route('admin.users.update', $user) }}" method="POST" class="bg-white rounded-lg shadow-md p-6 space-y-6">
        @csrf
        @method('PUT')

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Nom complet *</label>
            <input type="text" name="name" required class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500" value="{{ old('name', $user->name) }}">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Email *</label>
            <input type="email" name="email" required class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500" value="{{ old('email', $user->email) }}">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Téléphone</label>
            <input type="tel" name="phone" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500" value="{{ old('phone', $user->phone) }}">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Rôle *</label>
            <select name="role" required class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                <option value="user" {{ old('role', $user->role) == 'user' ? 'selected' : '' }}>Utilisateur</option>
                <option value="driver" {{ old('role', $user->role) == 'driver' ? 'selected' : '' }}>Conducteur</option>
                <option value="sender" {{ old('role', $user->role) == 'sender' ? 'selected' : '' }}>Expéditeur</option>
                <option value="admin" {{ old('role', $user->role) == 'admin' ? 'selected' : '' }}>Administrateur</option>
            </select>
        </div>

        <div>
            <label class="flex items-center">
                <input type="checkbox" name="is_verified" value="1" {{ old('is_verified', $user->is_verified) ? 'checked' : '' }} class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                <span class="ml-2 text-sm text-gray-700">Compte vérifié</span>
            </label>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Nouveau mot de passe (laisser vide pour ne pas changer)</label>
            <input type="password" name="password" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500" placeholder="Nouveau mot de passe">
        </div>

        <div class="flex justify-end space-x-4">
            <a href="{{ route('admin.users') }}" class="px-6 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">Annuler</a>
            <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">Enregistrer</button>
        </div>
    </form>
</div>
@endsection

