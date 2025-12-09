<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LandlordUser as User;
use App\Models\Business;
use App\Models\Tenant;
use Exception;
use Illuminate\Support\Str;

class AdminController extends Controller
{
    public function approveBusinessOwner($id)
    {
        try {
            $user = User::where('id', $id)
            ->where('status', 'pending')
            ->first();

            // add graceful response
            if (!$user) {
                return response()->json([
                    'error' => 'Pending business owner not found or already approved.'
                ], 404);
            }

            // $user->makeCurrent();

            // Auto-create business
            $business = Business::create([
                'owner_id' => $user->id,
                'name' => $user->name . "'s Business",
                'slug' => Str::slug($user->name . '-business-' . $user->id),
                'email' => $user->email,
                'status' => 'active',
            ]);


            // Create tenant for the business
            $domain = Str::slug($business->name). ".127.0.0.1.nip.io";
            $database = 'tenant_' . Str::slug($business->name, '_' . time());

            $tenant = Tenant::create([
                'name' => $business->name,
                'domain' => $domain,
                'database' => $database,
                'business_id' => $business->id,
            ]);

            // Link user with business
            // Update user status
            // asign role
            $user->business_id = $business->id;
            $user->status = 'active';
            $user->assignRole('business_owner');
            $user->save();

            // 7. SWITCH to TENANT context
            $tenant->makeCurrent();

            // 8. CREATE BUSINESS OWNER inside tenant database
            $tenantUser = \App\Models\User::create([
                'name' => $user->name,
                'username' => $user->username,
                'email' => $user->email, // same email
                'password' => $user->password, // same password (already hashed)
                'status' => 'active',
            ]);

            // $token = JWTAuth::fromUser($user);

            return response()->json([
                'message' => 'Business owner approved successfully!',
                'user' => $user,
                'business' => $business,
                'tenant' => $tenant,
                'tenant_user' => $tenantUser,
                'tenant_url' =>"http://{$domain}:8000",
                // 'token' => $token,
            ]);
        } catch (Exception $e) {
            return response()->json([
                "status" => "error",
                "message"=> "Server error. Please contact with support.",
                'error' => $e->getMessage()
            ], 500);
        }
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
