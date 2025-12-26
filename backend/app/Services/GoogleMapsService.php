<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class GoogleMapsService
{
    public function geocodeAddress($address)
    {
        return Http::get("https://maps.googleapis.com/maps/api/geocode/json", [
            'address' => $address,
            'key' => config('services.google.maps_key'),
        ])->json();
    }

    public function reverseGeocode($branches)
    {
        return Http::get("https://maps.googleapis.com/maps/api/geocode/json", [
            'latlng' => "{$branches[0]['latitude']},{$branches[0]['longitude']}",
            'key' => config('services.google.maps_key'),
        ])->json();
    }

    // OPTIONAL: place search
    public function searchPlace($query)
    {
        return Http::get("https://maps.googleapis.com/maps/api/place/textsearch/json", [
            'query' => $query,
            'key' => config('services.google.maps_key'),
        ])->json();
    }
}
