<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GoogleMapsService
{
    protected string $baseUrl = 'https://maps.googleapis.com/maps/api';
    protected string $apiKey;

    public function __construct()
    {
        $this->apiKey = config('services.google.maps_key');
    }

    /**
     * Address → Latitude / Longitude
     * Used when:
     * - Admin creates branch
     * - Admin searches shop location
     */
    public function geocodeAddress(string $address): ?array
    {
        $response = Http::get("{$this->baseUrl}/geocode/json", [
            'address' => $address,
            'key' => $this->apiKey,
        ]);

        if (! $response->successful()) {
            Log::error('Google Geocode API failed', [
                'address' => $address,
                'response' => $response->body(),
            ]);
            return null;
        }

        $data = $response->json();

        if (empty($data['results'][0]['geometry']['location'])) {
            return null;
        }

        return [
            'lat' => $data['results'][0]['geometry']['location']['lat'],
            'lng' => $data['results'][0]['geometry']['location']['lng'],
            'formatted_address' => $data['results'][0]['formatted_address'] ?? null,
        ];
    }

    /**
     * Latitude / Longitude → Address
     * Used for:
     * - Map tooltip
     * - Branch details
     */
    public function reverseGeocode(float $lat, float $lng): ?array
    {
        $response = Http::get("{$this->baseUrl}/geocode/json", [
            'latlng' => "{$lat},{$lng}",
            'key' => $this->apiKey,
        ]);

        if (! $response->successful()) {
            Log::error('Google Reverse Geocode failed', [
                'lat' => $lat,
                'lng' => $lng,
            ]);
            return null;
        }

        $data = $response->json();

        if (empty($data['results'][0])) {
            return null;
        }

        return [
            'formatted_address' => $data['results'][0]['formatted_address'],
            // "address_components" => $data['results'][0]['address_components'],
            'place_id' => $data['results'][0]['place_id'] ?? null,
        ];
    }

    /**
     * Place search (Admin UI search bar)
     * Example: "Aminpass Gulshan"
     */
    public function searchPlace(string $query, int $limit = 5): array
    {
        $response = Http::get("{$this->baseUrl}/place/textsearch/json", [
            'query' => $query,
            'key' => $this->apiKey,
        ]);

        if (! $response->successful()) {
            Log::error('Google Place Search failed', [
                'query' => $query,
            ]);
            return [];
        }

        $results = $response->json('results') ?? [];

        return collect($results)->take($limit)->map(function ($item) {
            return [
                'name' => $item['name'] ?? null,
                'address' => $item['formatted_address'] ?? null,
                'lat' => $item['geometry']['location']['lat'] ?? null,
                'lng' => $item['geometry']['location']['lng'] ?? null,
                'place_id' => $item['place_id'] ?? null,
            ];
        })->toArray();
    }
}
