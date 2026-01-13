<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class GoogleMapsService
{
    private $apiKey;

    public function __construct()
    {
        $this->apiKey = config('services.google_maps.api_key');
    }

    public function geocodeAddress(string $address): ?array
    {
        $response = Http::get('https://maps.googleapis.com/maps/api/geocode/json', [
            'address' => $address,
            'key' => $this->apiKey,
        ]);

        $data = $response->json();

        if ($data['status'] === 'OK' && !empty($data['results'])) {
            $location = $data['results'][0]['geometry']['location'];
            return [
                'latitude' => $location['lat'],
                'longitude' => $location['lng'],
                'formatted_address' => $data['results'][0]['formatted_address'],
            ];
        }

        return null;
    }

    public function calculateDistance(string $origin, string $destination): ?array
    {
        $response = Http::get('https://maps.googleapis.com/maps/api/distancematrix/json', [
            'origins' => $origin,
            'destinations' => $destination,
            'key' => $this->apiKey,
            'units' => 'metric',
        ]);

        $data = $response->json();

        if ($data['status'] === 'OK' && !empty($data['rows'][0]['elements'][0])) {
            $element = $data['rows'][0]['elements'][0];
            if ($element['status'] === 'OK') {
                return [
                    'distance' => $element['distance']['value'], // en mètres
                    'duration' => $element['duration']['value'], // en secondes
                    'distance_text' => $element['distance']['text'],
                    'duration_text' => $element['duration']['text'],
                ];
            }
        }

        return null;
    }

    public function getDirections(string $origin, string $destination): ?array
    {
        $response = Http::get('https://maps.googleapis.com/maps/api/directions/json', [
            'origin' => $origin,
            'destination' => $destination,
            'key' => $this->apiKey,
        ]);

        $data = $response->json();

        if ($data['status'] === 'OK' && !empty($data['routes'])) {
            return $data['routes'][0];
        }

        return null;
    }
}

