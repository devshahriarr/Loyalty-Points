<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Tenant;
use App\Models\VisitLog;
use Illuminate\Http\Request;
use App\Services\GoogleMapsService;

class GeolocationController extends Controller
{
    protected $google;

    public function __construct(GoogleMapsService $google)
    {
        $this->google = $google;
    }

    /**
     * Get all branches with lat/lng
     */
    public function allBranches()
    {
        // $branches = Branch::select('id', 'name', 'latitude', 'longitude')->get();

        // return response()->json([
        //     'status' => 'success',
        //     'branches' => $branches
        // ]);

        $tenants = Tenant::all();
        $results = [];

        foreach ($tenants as $tenant) {
            $tenant->makeCurrent();

            $branches = Branch::select('id', 'name', 'latitude', 'longitude')->get();

            $results[] = [
                'tenant_id' => $tenant->id,
                'tenant_name' => $tenant->name,
                'branches' => $branches,
            ];
        }

        return response()->json($results);
    }

    /**
     * Reverse Geocode lat/lng → address
     */
    public function reverseGeocode(Request $request)
    {
        $request->validate([
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
        ]);

        $result = $this->google->reverseGeocode($request->lat, $request->lng);

        return response()->json([
            'status' => 'success',
            'data' => $result
        ]);
    }

    /**
     * Geocode address → lat/lng
     */
    public function geocodeAddress(Request $request)
    {
        $request->validate([
            'address' => 'required|string',
        ]);

        $result = $this->google->geocodeAddress($request->address);

        return response()->json([
            'status' => 'success',
            'data' => $result
        ]);
    }

    /**
     * OPTIONAL — Search a place
     */
    public function searchPlace(Request $request)
    {
        $request->validate([
            'query' => 'required|string',
        ]);

        $result = $this->google->searchPlace($request->query);

        return response()->json([
            'status' => 'success',
            'data' => $result
        ]);
    }

    /**
     * Return nearest branches to a given lat/lng
     * Request: { lat, lng, max_distance_km (optional, default 50), limit (optional) }
     */
    public function nearestBranch(Request $request)
    {
        $request->validate([
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
            'max_distance_km' => 'nullable|numeric',
            'limit' => 'nullable|integer',
        ]);

        $lat = (float) $request->lat;
        $lng = (float) $request->lng;
        $maxDistanceKm = $request->filled('max_distance_km') ? (float)$request->max_distance_km : 50.0;
        $limit = $request->filled('limit') ? (int)$request->limit : 10;

        // Bounding box for quick filtering (approx)
        [$minLat, $maxLat, $minLng, $maxLng] = $this->bbox($lat, $lng, $maxDistanceKm);

        $candidates = Branch::select('id','name','latitude','longitude','address')
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->whereBetween('latitude', [$minLat, $maxLat])
            ->whereBetween('longitude', [$minLng, $maxLng])
            ->get();

        // calculate distance and sort
        $results = [];
        foreach ($candidates as $b) {
            $distanceKm = $this->haversine($lat, $lng, (float)$b->latitude, (float)$b->longitude);
            if ($distanceKm <= $maxDistanceKm) {
                $results[] = [
                    'branch' => $b,
                    'distance_km' => $distanceKm,
                ];
            }
        }

        // sort by distance
        usort($results, function($a, $b) {
            return $a['distance_km'] <=> $b['distance_km'];
        });

        return response()->json([
            'status' => 'success',
            'data' => array_slice($results, 0, $limit),
        ]);
    }

    /**
     * Check geofence: is lat/lng within radius_meters of any branch
     * Request: { lat, lng, radius_meters (optional, default 100), record_visit (bool) }
     */
    public function checkGeofence(Request $request)
    {
        $request->validate([
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
            'radius_meters' => 'nullable|numeric',
            'record_visit' => 'nullable|boolean',
            'customer_id' => 'nullable|integer' // optional if you want to log who entered
        ]);

        $lat = (float) $request->lat;
        $lng = (float) $request->lng;
        $radiusMeters = $request->filled('radius_meters') ? (float)$request->radius_meters : 100.0;

        // small conversion: radius in km
        $radiusKm = $radiusMeters / 1000.0;

        // quick bbox of radiusKm
        [$minLat, $maxLat, $minLng, $maxLng] = $this->bbox($lat, $lng, $radiusKm);

        $candidates = Branch::select('id','name','latitude','longitude','address')
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->whereBetween('latitude', [$minLat, $maxLat])
            ->whereBetween('longitude', [$minLng, $maxLng])
            ->get();

        $inside = [];
        foreach ($candidates as $b) {
            $distanceKm = $this->haversine($lat, $lng, (float)$b->latitude, (float)$b->longitude);
            $distanceMeters = $distanceKm * 1000;
            if ($distanceMeters <= $radiusMeters) {
                $inside[] = [
                    'branch' => $b,
                    'distance_m' => $distanceMeters,
                ];
            }
        }

        $response = [
            'status' => 'success',
            'inside_geofence' => count($inside) > 0,
            'matches' => $inside,
        ];

        // optional: record visit(s)
        if ($request->filled('record_visit') && $request->boolean('record_visit') && $request->filled('customer_id')) {
            foreach ($inside as $match) {
                VisitLog::create([
                    'customer_id' => $request->customer_id,
                    'branch_id' => $match['branch']->id,
                    'detected_at' => now(),
                    'lat' => $lat,
                    'lng' => $lng,
                    'distance_m' => $match['distance_m'],
                ]);
            }
            $response['recorded'] = true;
        }

        return response()->json($response);
    }

    /**
     * Create branch with automatic geocoding if lat/lng not provided
     * Request: { name, address (optional), lat (optional), lng (optional), other branch fields... }
     */
    // public function createBranchAuto(Request $request)
    // {
    //     $request->validate([
    //         'name' => 'required|string|max:255',
    //         'address' => 'nullable|string',
    //         'latitude' => 'nullable|numeric',
    //         'longitude' => 'nullable|numeric',
    //         // add other branch fields validation as needed
    //     ]);

    //     $data = $request->only(['name', 'address', /* other fields */]);

    //     if (! $request->filled('latitude') || ! $request->filled('longitude')) {
    //         if (! $request->filled('address')) {
    //             return response()->json([
    //                 'status' => 'error',
    //                 'message' => 'Either latitude/longitude or address must be provided'
    //             ], 422);
    //         }

    //         // geocode
    //         $geo = $this->google->geocodeAddress($request->address);
    //         if (isset($geo['results'][0]['geometry']['location'])) {
    //             $loc = $geo['results'][0]['geometry']['location'];
    //             $data['latitude'] = $loc['lat'];
    //             $data['longitude'] = $loc['lng'];
    //         } else {
    //             return response()->json([
    //                 'status' => 'error',
    //                 'message' => 'Unable to geocode address'
    //             ], 422);
    //         }
    //     } else {
    //         $data['latitude'] = (float)$request->latitude;
    //         $data['longitude'] = (float)$request->longitude;
    //     }

    //     // create branch (you said BranchController exists — adjust to use your logic)
    //     $branch = Branch::create($data);

    //     return response()->json([
    //         'status' => 'success',
    //         'branch' => $branch
    //     ]);
    // }

    /* ----------------------
       Helper methods
       ---------------------- */

    // haversine distance (km)
    private function haversine($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371; // km
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat/2) * sin($dLat/2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon/2) * sin($dLon/2);
        $c = 2 * atan2(sqrt($a), sqrt(1-$a));
        return $earthRadius * $c;
    }

    /**
     * Bounding box (minLat, maxLat, minLng, maxLng) given center & distance in km
     * This is approximate but good for pre-filtering.
     */
    private function bbox($lat, $lng, $distanceKm)
    {
        $earthRadius = 6371; // km

        $deltaLat = rad2deg($distanceKm / $earthRadius);
        $deltaLng = rad2deg($distanceKm / ($earthRadius * cos(deg2rad($lat))));

        $minLat = $lat - $deltaLat;
        $maxLat = $lat + $deltaLat;
        $minLng = $lng - $deltaLng;
        $maxLng = $lng + $deltaLng;

        return [$minLat, $maxLat, $minLng, $maxLng];
    }
}
