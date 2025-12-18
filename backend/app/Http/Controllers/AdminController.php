<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LandlordUser as User;
use App\Models\Business;
use App\Models\Tenant;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class AdminController extends Controller
{
    protected $activeTenantsCount = 0;
    // public function approveBusinessOwner($id)
    // {
    //     try {
    //         // $user = User::where('id', $id)
    //         // ->where('status', 'pending')
    //         // ->first();
    //         $business = Business::findOrFail($id)->where('status', 'inactive')->first();

    //         // add graceful response
    //         if (!$business) {
    //             return response()->json([
    //                 'error' => 'Pending business owner not found or already approved.'
    //             ], 404);
    //         }

    //         // $user->makeCurrent();

    //         // Auto-create business
    //         // $business = Business::create([
    //         //     'owner_id' => $user->id,
    //         //     'name' => $user->name . "'s Business",
    //         //     'slug' => Str::slug($user->name . '-business-' . $user->id),
    //         //     'email' => $user->email,
    //         //     'status' => 'active',
    //         // ]);



    //         // Create tenant for the business
    //         $domain = Str::slug($business->name) . ".127.0.0.1.nip.io";
    //         $database = 'tenant_' . Str::slug($business->name, '_' . time());

    //         $tenant = Tenant::create([
    //             'name' => $business->name,
    //             'domain' => $domain,
    //             'database' => $database,
    //             'business_id' => $business->id,
    //         ]);


    //         // Link user with business
    //         // Update user status
    //         // asign role


    //         // 7. SWITCH to TENANT context
    //         $tenant->makeCurrent();

    //         $roles = ['system_admin', 'business_owner', 'staff', 'customer'];
    //         foreach ($roles as $r) {
    //             \Spatie\Permission\Models\Role::firstOrCreate(['name' => $r]);
    //         }

    //         app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

    //         // 8. CREATE BUSINESS OWNER inside tenant database
    //         $tenantUser = \App\Models\User::create([
    //             'name' => $business->name,
    //             'username' => Str::slug($business->name) . '-' . time(),
    //             'email' => $business->email, // same email
    //             'password' => Hash::make($business->password), // same password (already hashed)
    //             'business_id' => $business->id,
    //             'status' => 'active',
    //         ]);


    //         // assign by name (safer than id)
    //         $tenantUser->assignRole('business_owner');
    //         // $token = JWTAuth::fromUser($user);
    //         $business->status = 'active';
    //         $business->password == null;
    //         $business->owner_id = $tenantUser->id;
    //         $business->save();

    //         return response()->json([
    //             'message' => 'Business owner approved successfully!',
    //             'user' => $tenantUser,
    //             'business' => $business,
    //             'tenant' => $tenant,
    //             'tenant_url' => "http://{$domain}:8000",
    //             // 'token' => $token,
    //         ]);
    //     } catch (Exception $e) {
    //         return response()->json([
    //             "status" => "error",
    //             "message" => "Server error. Please contact with support.",
    //             'error' => $e->getMessage()
    //         ], 500);
    //     }
    // }
    public function approveBusinessOwner($id)
    {
        try {
            // সঠিকভাবে ব্যবসা লোড করো — chaining bug ঠিক করা
            $business = Business::where('id', $id)->where('status', 'inactive')->first();
            if (!$business) {
                return response()->json([
                    'error' => 'Pending business owner not found or already approved.'
                ], 404);
            }

            // Transaction: সব কাজ একসাথে, fail হলে rollback হবে
            DB::beginTransaction();

            // 1) Create Tenant (ensure domain/database unique-ish)
            $baseSlug = Str::slug($business->name);
            $timestamp = time();
            $rand = substr(Str::random(6), 0, 6);

            $domain = "{$baseSlug}.127.0.0.1.nip.io"; // local host pattern
            // ensure domain uniqueness by suffixing timestamp+rand when necessary
            if (Tenant::where('domain', $domain)->exists()) {
                $domain = "{$baseSlug}-{$timestamp}-{$rand}.127.0.0.1.nip.io";
            }

            $database = 'tenant_' . Str::slug($business->name . '_' . $timestamp . '_' . $rand, '_');

            $tenant = Tenant::create([
                'name' => $business->name,
                'domain' => $domain,
                'database' => $database,
                'business_id' => $business->id,
            ]);

            // 2) Switch to tenant context
            $tenant->makeCurrent();

            // OPTIONAL: If you need to run tenant migrations automatically, you could
            // trigger them here (uncomment if your setup supports it):
            // \Artisan::call('tenants:migrate', ['--tenant' => $tenant->id]);

            // 3) Ensure Spatie roles exist inside tenant DB
            $roles = ['system_admin', 'business_owner', 'staff', 'customer'];
            foreach ($roles as $r) {
                Role::firstOrCreate(['name' => $r]);
            }

            // 4) Clear Spatie permission cache (important!)
            app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

            // 5) Prepare password: avoid double-hash
            $rawOrHashed = $business->password ?? null;
            if (!$rawOrHashed) {
                // If no password stored on business, generate a random one and consider notifying owner
                $generated = Str::random(10);
                $hashedPassword = Hash::make($generated);
                // You might want to send $generated to the owner via email
            } else {
                // Detect if already hashed (bcrypt usually starts with $2y$ or $2a$ or argon prefix)
                if (Str::startsWith($rawOrHashed, ['$2y$', '$2a$', '$argon'])) {
                    $hashedPassword = $rawOrHashed; // already hashed
                } else {
                    $hashedPassword = Hash::make($rawOrHashed);
                }
            }

            // 6) Create tenant user inside tenant DB
            $tenantUser = \App\Models\User::create([
                'name' => $business->name,
                'username' => Str::slug($business->name) . '-' . $timestamp,
                'email' => $business->email,
                'password' => $hashedPassword,
                'business_id' => $business->id,
                'status' => 'active',
            ]);

            // 7) Assign role by name (safer). This will insert into model_has_roles.
            $tenantUser->assignRole('business_owner');

            // 8) Update landlord business record (back on landlord connection)
            // If makeCurrent changed default connection, ensure we use landlord connection:
            // Assuming Business model uses default connection; if not, you can force it:
            // \App\Models\Business::on('landlord')->find($business->id) ...
            $business->status = 'active';
            $business->password = null; // set password to null safely
            $business->owner_id = $tenantUser->id;
            $business->save();

            DB::commit();

            return response()->json([
                'message' => 'Business owner approved successfully!',
                'user' => $tenantUser,
                'business' => $business,
                'tenant' => $tenant,
                'tenant_url' => "http://{$domain}:8000",
            ], 201);
        } catch (Exception $e) {
            DB::rollBack();

            // Optional: if tenant DB was partially created, you may want to cleanup:
            // try { $tenant && $tenant->delete(); } catch (\Throwable $t) {}

            return response()->json([
                "status" => "error",
                "message" => "Server error. Please contact with support.",
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getAllTenants()
    {

        $tenants = Tenant::all();
        $allTenants = collect();

        foreach ($tenants as $tenant) {
            $users = $tenant->execute(function () {
                return \App\Models\User::all();
            });
            $allTenants = $allTenants->merge($users);
        }
        return response()->json([
            'status' => 'success',
            'tenants' => $allTenants,
        ]);
    }

    public function getTenantsCount()
    {
        // $tenantsCount = Tenant::count();
        $tenants = Tenant::all();
        $tenantsCount = collect();

        foreach ($tenants as $tenant) {
            $users = $tenant->execute(function () {
                return \App\Models\User::all();
            });
            $tenantsCount = $tenantsCount->merge($users);
        }
        $total = $tenantsCount->count();
        return response()->json([
            'status' => 'success',
            'tenantsCount' => $total,
        ]);
    }

    public function getActiveTenantsCount()
    {
        $tenants = Tenant::all();
        $activeTenantsCount = collect();

        foreach ($tenants as $tenant) {
            $users = $tenant->execute(function () {
                return \App\Models\User::where('status', 'active')->get();
            });
            $activeTenantsCount = $activeTenantsCount->merge($users);
        }

        $total = $activeTenantsCount->count();

        return response()->json([
            'status' => 'success',
            'activeTenantsCount' => $total,
        ]);
    }

    public function getActiveTenants()
    {
        $tenants = Tenant::all();
        $activeTenants = collect();

        foreach ($tenants as $tenant) {
            $users = $tenant->execute(function () {
                return \App\Models\User::where('status', 'active')->get();
            });
            $activeTenants = $activeTenants->merge($users);
        }

        return response()->json([
            'status' => 'success',
            'activeTenants' => $activeTenants,
        ]);
    }

    public function getInactiveTenants()
    {
        $tenants = Tenant::all();
        $inactiveTenants = collect();

        foreach ($tenants as $tenant) {
            $users = $tenant->execute(function () {
                return \App\Models\User::where('status', 'inactive')->get();
            });
            $inactiveTenants = $inactiveTenants->merge($users);
        }

        return response()->json([
            'status' => 'success',
            'inactiveTenants' => $inactiveTenants,
        ]);
    }

    public function getPendingTenants()
    {
        $tenants = Tenant::all();
        $pendingTenants = collect();

        foreach ($tenants as $tenant) {
            $users = $tenant->execute(function () {
                return \App\Models\User::where('status', 'pending')->get();
            });
            $pendingTenants = $pendingTenants->merge($users);
        }

        return response()->json([
            'status' => 'success',
            'pendingTenants' => $pendingTenants,
        ]);
    }
}
