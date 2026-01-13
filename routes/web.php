<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\TripController;
use Illuminate\Support\Facades\Route;

// Routes publiques
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/trips', [TripController::class, 'index'])->name('trips.index');
Route::get('/trips/{trip}', [TripController::class, 'show'])->name('trips.show')->where('trip', '[0-9]+');

// Authentification
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
    
    // Authentification Google
    Route::get('/auth/google', [AuthController::class, 'redirectToGoogle'])->name('auth.google');
    Route::get('/auth/google/callback', [AuthController::class, 'handleGoogleCallback'])->name('auth.google.callback');
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

// Routes authentifiées
Route::middleware('auth')->group(function () {
    // Tableau de bord
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Trajets (nécessitent le rôle driver ou admin)
    Route::middleware('role:driver,admin')->group(function () {
        Route::get('/trips/create', [TripController::class, 'create'])->name('trips.create');
        Route::post('/trips', [TripController::class, 'store'])->name('trips.store');
        Route::get('/trips/{trip}/edit', [TripController::class, 'edit'])->name('trips.edit');
        Route::put('/trips/{trip}', [TripController::class, 'update'])->name('trips.update');
        Route::delete('/trips/{trip}', [TripController::class, 'destroy'])->name('trips.destroy');
        Route::get('/my-trips', [TripController::class, 'myTrips'])->name('trips.my-trips');
    });

    // Réservations
    Route::post('/trips/{trip}/bookings', [BookingController::class, 'store'])->name('bookings.store');
    Route::get('/bookings/{booking}', [BookingController::class, 'show'])->name('bookings.show');
    Route::get('/my-bookings', [BookingController::class, 'myBookings'])->name('bookings.my-bookings');
    Route::post('/bookings/{booking}/cancel', [BookingController::class, 'cancel'])->name('bookings.cancel');

    // Statut des réservations (conducteur/admin)
    Route::middleware('role:driver,admin')->group(function () {
        Route::put('/bookings/{booking}/status', [BookingController::class, 'updateStatus'])->name('bookings.update-status');
    });

    // Messages
    Route::post('/bookings/{booking}/messages', [MessageController::class, 'store'])->name('messages.store');
    Route::put('/messages/{message}/read', [MessageController::class, 'markAsRead'])->name('messages.read');

    // Routes administrateur
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        // Gestion des utilisateurs
        Route::get('/users', [AdminController::class, 'users'])->name('users');
        Route::get('/users/{user}/edit', [AdminController::class, 'editUser'])->name('users.edit');
        Route::put('/users/{user}', [AdminController::class, 'updateUser'])->name('users.update');
        Route::delete('/users/{user}', [AdminController::class, 'deleteUser'])->name('users.delete');

        // Gestion des trajets
        Route::get('/trips', [AdminController::class, 'trips'])->name('trips');
        Route::get('/trips/{trip}/edit', [AdminController::class, 'editTrip'])->name('trips.edit');
        Route::delete('/trips/{trip}', [AdminController::class, 'deleteTrip'])->name('trips.delete');

        // Gestion des réservations
        Route::get('/bookings', [AdminController::class, 'bookings'])->name('bookings');
        Route::put('/bookings/{booking}/status', [AdminController::class, 'updateBookingStatus'])->name('bookings.update-status');
        Route::post('/bookings/{booking}/confirm', [AdminController::class, 'confirmBooking'])->name('bookings.confirm');
    });
});

