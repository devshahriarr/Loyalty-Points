<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LandlordUser as User;
use App\Models\Business;
use App\Models\Tenant;
use Illuminate\Support\Str;

class AdminController extends Controller
{
    public function approveBusinessOwner($id)
    {
        $user = User::where('id', $id)
        ->where('status', 'pending')
        ->first();

        // add graceful response
        if (!$user) {
            return response()->json([
                'error' => 'Pending business owner not found or already approved.'
            ], 404);
        }
        // asign role
        $user->assignRole('business_owner');

        // Update user status
        $user->status = 'active';
        $user->save();

        // Auto-create business
        $business = Business::create([
            'owner_id' => $user->id,
            'name' => $user->name . "'s Business",
            'slug' => Str::slug($user->name . '-business-' . $user->id),
            'email' => $user->email,
            'status' => 'active',
        ]);

        // Link user with business
        $user->business_id = $business->id;
        $user->save();

        // Create tenant for the business
        $domain = Str::slug($business->name). ".127.0.0.1:8000";
        $database = 'tenant_' . Str::slug($business->name, '_' . time());

        $tenant = Tenant::create([
            'name' => $business->name,
            'domain' => $domain,
            'database' => $database,
            'business_id' => $business->id,
        ]);

        return response()->json([
            'message' => 'Business owner approved successfully!',
            'user' => $user,
            'business' => $business,
            'tenant' => $tenant,
            // 'tenant_url' => 'http://' . $domain . '.' . config('app.domain')
            'tenant_url' => 'http://' . $domain
        ]);
    }

    public function getAllTenants()
    {
        $tenants = Tenant::with('business')->get();
        return response()->json([
            'status' => 'success',
            'tenants' => $tenants,
        ]);
    }

    public function getTenantsCount()
    {
        $tenantsCount = Tenant::count();
        return response()->json([
            'status' => 'success',
            'tenantsCount' => $tenantsCount,
        ]);
    }

    public function getActiveTenantsCount()
    {
        $activeTenantsCount = Business::with('tenants')->where('status', 'active')->count();
        return response()->json([
            'status' => 'success',
            'activeTenantsCount' => $activeTenantsCount,
        ]);
    }

    public function getActiveTenants()
    {
        $activeTenants = Business::with('tenants')->where('status', 'active')->get();
        return response()->json([
            'status' => 'success',
            'activeTenants' => $activeTenants,
        ]);
    }

    public function getInactiveTenants()
    {
        $inactiveTenants = Business::with('tenants')->where('status', 'inactive')->get();
        return response()->json([
            'status' => 'success',
            'inactiveTenants' => $inactiveTenants,
        ]);
    }

    public function getPendingTenants()
    {
        $pendingTenants = Business::with('tenants')->where('status', 'pending')->get();
        return response()->json([
            'status' => 'success',
            'pendingTenants' => $pendingTenants,
        ]);
    }



}
