<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Business;
use App\Models\Tenant;
use App\Services\GoogleMapsService;
use Illuminate\Http\Request;

class GeolocationController extends Controller
{
    protected GoogleMapsService $googleMaps;

    public function __construct(GoogleMapsService $googleMaps)
    {
        $this->googleMaps = $googleMaps;
    }

    /* -----------------------------
     Business Search (Admin UI)
    ----------------------------- */
    public function searchBusinesses()
    {
        $businesses = Business::all();
        $allBranches = [];

        foreach ($businesses as $business) {

            $tenant = Tenant::where('business_id', $business->id)->first();

            if (! $tenant) {
                continue;
            }

            $tenant->makeCurrent();

            $branches = Branch::query()->select(
                'id',
                'name',
                'address',
                'latitude',
                'longitude'
            )->whereNotNull('latitude')->whereNotNull('longitude')->get();

            $allBranches[] = [
                'business_id'   => $business->id,
                'business_name' => $business->name,
                'tenant_id'     => $tenant->id,
                'branches'      => $branches,
            ];

            $tenant->forgetCurrent();
        }

        return response()->json([
            'status' => 'success',
            'data'   => $allBranches,
        ]);
    }

    /* -----------------------------
     Branch list by tenant
    ----------------------------- */
    public function branchesByTenant(Tenant $tenant)
    {
        $tenant->makeCurrent();

        $branches = Branch::query()->select('id', 'name', 'address', 'latitude', 'longitude')->get();

        $tenant->forgetCurrent();

        return $branches;
    }


    /* -----------------------------
     Geofence check (100m)
    ----------------------------- */
    public function checkGeofence(Request $request)
    {
        // $data = $this->searchBusinesses();
        // // 2. Response object theke raw content ber kora
        // $content = $data->getContent();
        // // 3. JSON string-ke PHP array-te rupantor kora
        // $allBranches = json_decode($content, true);
        // dd($allBranches);

        $request->validate([
            'tenant_id'     => 'required|exists:tenants,id',
            'lat'           => 'required|numeric',
            'lng'           => 'required|numeric',
            'radius_meters' => 'nullable|numeric'
        ]);

        $radius = $request->radius_meters ?? 100;

        $tenant = Tenant::findOrFail($request->tenant_id);
        $tenant->makeCurrent();

        $branches = Branch::whereNotNull('latitude')
        ->whereNotNull('longitude')
        ->get();

        foreach ($branches as $branch) {
            $distance = $this->distanceMeters(
                $request->lat,
                $request->lng,
                $branch->latitude,
                $branch->longitude
            );

            if ($distance <= $radius) {
                $tenant->forgetCurrent();

                return response()->json([
                    'inside'      => true,
                    'branch_id'   => $branch->id,
                    'branch_name' => $branch->name,
                    'distance_m'  => round($distance, 2)
                ]);
            }
        }

        $tenant->forgetCurrent();

        return response()->json([
            'inside'  => false,
            'message' => 'Not within 100 meters'
        ], 403);
    }

    private function distanceMeters($lat1, $lng1, $lat2, $lng2): float
    {
        $earthRadius = 6371000; // meters

        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat/2) ** 2 +
        cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
        sin($dLng/2) ** 2;

        $c = 2 * atan2(sqrt($a), sqrt(1-$a));

        return $earthRadius * $c;
    }

    public function getAddressFromLatLng(Request $request)
    {
        $request->validate([
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
        ]);

        $address = $this->googleMaps->reverseGeocode($request->lat, $request->lng);

        return response()->json($address);
    }

    // public function searchPlace(Request $request)
    // {
        //     $request->validate([
            //         'query' => 'required|string',
            //     ]);

    //     $places = $this->googleMaps->searchPlace($request->query->get('query'));

    //     return response()->json($places);
    // }


    /* -----------------------------
    Single branch location
    ----------------------------- */
    // public function branchLocation(Branch $branch)
    // {


    //     return response()->json([
    //         'id' => $branch->id,
    //         'name' => $branch->name,
    //         'lat' => $branch->latitude,
    //         'lng' => $branch->longitude,
    //     ]);
    // }
}
