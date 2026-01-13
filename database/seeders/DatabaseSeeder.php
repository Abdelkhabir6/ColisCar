<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Trip;
use App\Models\Booking;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Créer un admin
        $admin = User::create([
            'name' => 'Administrateur',
            'email' => 'admin@coliscar.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'is_verified' => true,
            'rating' => 5.00,
            'total_ratings' => 10,
        ]);

        // Créer un conducteur
        $driver = User::create([
            'name' => 'Jean Dupont',
            'email' => 'conducteur@coliscar.com',
            'password' => Hash::make('password'),
            'phone' => '0612345678',
            'role' => 'driver',
            'is_verified' => true,
            'rating' => 4.5,
            'total_ratings' => 25,
        ]);

        // Créer un utilisateur
        $user = User::create([
            'name' => 'Marie Martin',
            'email' => 'user@coliscar.com',
            'password' => Hash::make('password'),
            'phone' => '0698765432',
            'role' => 'user',
            'is_verified' => true,
            'rating' => 4.8,
            'total_ratings' => 15,
        ]);

        // Créer quelques trajets
        $trips = [
            [
                'driver_id' => $driver->id,
                'departure_city' => 'Paris',
                'arrival_city' => 'Lyon',
                'departure_latitude' => 48.8566,
                'departure_longitude' => 2.3522,
                'arrival_latitude' => 45.7640,
                'arrival_longitude' => 4.8357,
                'departure_date' => now()->addDays(3),
                'departure_time' => '08:00:00',
                'price_per_seat' => 25.00,
                'available_seats' => 3,
                'total_seats' => 4,
                'available_volume' => 2.5,
                'max_weight' => 50,
                'trip_type' => 'mixed',
                'description' => 'Trajet confortable avec arrêts possibles',
                'status' => 'pending',
            ],
            [
                'driver_id' => $driver->id,
                'departure_city' => 'Lyon',
                'arrival_city' => 'Marseille',
                'departure_latitude' => 45.7640,
                'departure_longitude' => 4.8357,
                'arrival_latitude' => 43.2965,
                'arrival_longitude' => 5.3698,
                'departure_date' => now()->addDays(5),
                'departure_time' => '14:30:00',
                'price_per_seat' => 30.00,
                'available_seats' => 2,
                'total_seats' => 4,
                'available_volume' => 1.5,
                'max_weight' => 30,
                'trip_type' => 'passengers',
                'description' => 'Trajet direct sans arrêt',
                'status' => 'pending',
            ],
            [
                'driver_id' => $driver->id,
                'departure_city' => 'Paris',
                'arrival_city' => 'Bordeaux',
                'departure_latitude' => 48.8566,
                'departure_longitude' => 2.3522,
                'arrival_latitude' => 44.8378,
                'arrival_longitude' => -0.5792,
                'departure_date' => now()->addDays(7),
                'departure_time' => '10:00:00',
                'price_per_seat' => 35.00,
                'available_seats' => 0,
                'total_seats' => 4,
                'available_volume' => 3.0,
                'max_weight' => 100,
                'trip_type' => 'parcels',
                'description' => 'Transport de colis uniquement',
                'status' => 'confirmed',
            ],
        ];

        foreach ($trips as $tripData) {
            Trip::create($tripData);
        }
    }
}

