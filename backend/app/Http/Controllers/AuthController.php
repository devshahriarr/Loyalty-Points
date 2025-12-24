<?php

namespace App\Http\Controllers;

use App\Actions\ProvisionTenantAction;
use App\Models\Business;
use Illuminate\Http\Request;
use App\Models\LandlordUser;
use App\Models\Tenant;
use App\Models\Tenant\User as TenantUser;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\SubscriptionSeeder;
use Exception;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;

class AuthController extends Controller
{
    // Register
    // public function register(Request $request)
    // {
    //     $validator = Validator::make($request->all(), [
    //         'name' => 'required|string|max:100',
    //         'username' => 'required|string|max:100',
    //         'email' => 'required|string|email|max:100|unique:users',
    //         'password' => 'required|string|min:6|confirmed', // include password_confirmation
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json($validator->errors(), 422);
    //     }

    //     $user = User::create([
    //         'name' => $request->input('name'),
    //         'username' => $request->input('username'),
    //         'email' => $request->input('email'),
    //         'password' => Hash::make($request->input('password')),
    //         'phone' => $request->input('phone') ?? null,
    //         'status' => 'active',
    //     ]);

    //     // Auto-create business
    //     $business = Business::create([
    //         'owner_id' => $user->id,
    //         'name' => $user->name . "'s Business",
    //         'slug' => Str::slug($user->name . '-business-' . $user->id),
    //         'email' => $user->email,
    //         'status' => 'active',
    //     ]);


    //     // Create tenant for the business
    //     $domain = Str::slug($business->name) . ".127.0.0.1.nip.io";
    //     $database = 'tenant_' . Str::slug($business->name, '_' . time());

    //     $tenant = Tenant::create([
    //         'name' => $business->name,
    //         'domain' => $domain,
    //         'database' => $database,
    //         'business_id' => $business->id,
    //     ]);

    //     // Link user with business
    //     // Update user status
    //     // asign role
    //     $user->business_id = $business->id;
    //     $user->status = 'active';
    //     $user->assignRole('business_owner');
    //     $user->save();

    //     // $token = JWTAuth::fromUser($user);

    //     return response()->json([
    //         'message' => 'Tenant successfully registered',
    //         'business_owner' => $user,
    //         'business' => $business,
    //         'tenant' => $tenant,
    //         // 'token' => $token
    //     ], 201);
    // }

    public function registerBusinessOwner(Request $request)
    {
        // $validator = Validator::make($request->all(), [
        //     'name' => 'required|string|max:100',
        //     // 'username' => 'required|string|max:100',
        //     'email' => 'required|string|email|max:100|unique:users',
        //     'password' => 'required|string|min:6|confirmed', // include password_confirmation
        //     'phone' => 'nullable|string|max:20',
        // ]);

        // if ($validator->fails()) {
        //     return response()->json($validator->errors(), 422);
        // }
        // $business = Business::create([
        //     // 'owner_id' => $user->id,
        //     'name' => $request->name . "'s Business",
        //     'slug' => Str::slug($request->name . '-business-' . time()),
        //     'email' => $request->email,
        //     'password' => $request->password,
        //     'phone' => $request->phone ?? null,
        // ]);
        // $user = LandlordUser::create([
        //     'name' => $request->input('name'),
        //     'username' => $request->input('username'),
        //     'email' => $request->input('email'),
        //     'password' => Hash::make($request->input('password')),
        //     'phone' => $request->input('phone'),
        //     'status' => 'pending',
        // ]);

    //     return response()->json([
    //         'message' => 'Registration submitted successfully. Waiting for admin approval.',
    //         'business' => $business ?? null,
    //     ], 201);
        try {
            $validated = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'email' => 'required|email|max:255|unique:businesses,email',
                'password' => 'required|string|min:6|confirmed',
                // 'slug' => 'required|string|max:255|unique:businesses,slug',
            ]);

            if($validated->fails()){
                return response()->json([
                    "status" => "error",
                    "message" => "Validation failed",
                    "errors" => $validated->errors()
                ], 422);
            }

            if ($request->registration_date) {
                $carbon_date = Carbon::parse($request->registration_date);
                $registration_date = $carbon_date->format('Y-m-d H:i:s');
            }

            $business = Business::create([
                // "owner_id" => $user->id,
                'name' => $request->name . "'s Business",
                'slug' => Str::slug($request->name . '-business-' . rand(1,100)),
                'email' => $request->email,
                'country' => $request->country??null,
                "industry_type" => $request->industry_type,
                "total_branches" => $request->total_branches,
                "branch_locations" => $request->branch_locations,
                "registration_date" => $registration_date ?? null,
                "plan_type" => $request->plan_type,
                "billing_status" => $request->billing_status,
                "status" => "pending",
            ]);

            $domain = Str::slug($business->name) . ".127.0.0.1.nip.io";
            $database = 'tenant_' . Str::slug($business->name, '_' . time());

            $tenant = Tenant::create([
                'name' => $business->name,
                'domain' => $domain,
                'database' => $database,
                'business_id' => $business->id,
            ]);


            $business->update([
                "owner_id"=> $tenant->id,
            ]);

            app(ProvisionTenantAction::class)->execute($tenant);

            $tenant->makeCurrent();

            $tenantUser = TenantUser::create([
                "name" => $request->name,
                "username" => $request->name."-".Str::random(3),
                "email" => $request->email,
                "password" => Hash::make($request->input('password') ?? $request->password),
                "phone" => $request->phone ?? null,
                "address" => $request->address,
                "role" => "business_owner",
                "status" => "pending",
            ]);

            // $tenantUser->assignRole('business_owner');

            $tenant->forget();

            return response()->json([
                "status" => "success",
                "message" => "Business created successfully, waiting for admin approval.",
                "business" => $business,
                // "tenant" => $tenant,
                // "tenantUser" => $tenantUser,
                // "tenant_domain" => $domain,
                // "tenant_url" => "http://{$domain}:8000",
            ]);
        } catch (Exception $e) {
            return response()->json([
                "status" => "error",
                "message"=> "Something went error. Please contact with support.",
                "error" => $e->getMessage()
            ], 500);
        }

    }

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        $admin = User::where('email', $credentials['email'])->first();

        if (!$admin->hasRole('system_admin')) {
            return response()->json([
                'message' => 'You are not a system admin',
            ], 403);
        }

        if (! $token = auth('api')->attempt($credentials)) {
            return response()->json(['error' => 'Invalid credentials'], 401);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Super admin login successful',
            'token' => $token,
            'user' => auth('api')->user(),
        ]);
    }

    // ✅ Get logged in user info
    public function me()
    {
        // dd("me");
        $user = auth('api')->user();
        $admin = User::where('email', $user->email)->first();

        if (!$admin->hasRole('system_admin')) {
            return response()->json([
                'message' => 'You are not a system admin',
            ], 403);
        }

        return response()->json([
            'status'=> 'success',
            'user' => $admin,
        ]);
    }

    public function refresh()
    {
        try {
            $user = auth('api')->user();
            $admin = User::where('id', $user->id)->first();

            if ($admin->hasRole('system_admin')) {
                $newToken = auth('api')->refresh();
                return response()->json([
                    'message' => 'Token refreshed successfully',
                    'token' => $newToken,
                    'user' => auth('api')->user()
                ]);
            }

            return response()->json([
                'message' => 'You are not a system admin',
            ], 403);
        } catch (TokenInvalidException $e) {
            return response()->json(['error' => 'Invalid token'], 401);
        }
    }

    public function adminLogout()
    {
        $user = auth('api')->user();
        $admin = User::where('id', $user->id)->first();

        if (!$admin) {
            return response()->json([
                'message'=> 'Unauthenticated'
            ], 401);
        }

        if (!$admin->hasRole('system_admin')) {
            return response()->json([
                'message'=> 'You are not a system admin'
            ], 403);

        }else{

            auth('api')->logout();

            return response()->json([
                'message' => 'Successfully logged out'
            ]);
        }
    }

    // logged out user and destroy auth token
    public function ownerLogout()
    {
        $businessUser = auth('tenant')->user();

        if (!$businessUser) {
            return response()->json([
                'message'=> 'Unauthenticated'
            ], 401);
        }

        if ($businessUser->role !== 'business_owner') {
            return response()->json([
                'message'=> 'You are not a business owner'
            ], 403);

        }elseif($businessUser->role === 'business_owner') {

            auth('tenant')->logout();

            return response()->json([
                'message' => 'Successfully logged out'
            ]);
        }
    }


}
