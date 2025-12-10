<?php

namespace App\Http\Controllers;

use App\Models\Business;
use Illuminate\Http\Request;
use App\Models\LandlordUser;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;

class AuthController extends Controller
{
    // Register
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'username' => 'required|string|max:100',
            'email' => 'required|string|email|max:100|unique:users',
            'password' => 'required|string|min:6|confirmed', // include password_confirmation
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $user = User::create([
            'name' => $request->input('name'),
            'username' => $request->input('username'),
            'email' => $request->input('email'),
            'password' => Hash::make($request->input('password')),
            'phone' => $request->input('phone') ?? null,
            'status' => 'active',
        ]);

        // Auto-create business
        $business = Business::create([
            'owner_id' => $user->id,
            'name' => $user->name . "'s Business",
            'slug' => Str::slug($user->name . '-business-' . $user->id),
            'email' => $user->email,
            'status' => 'active',
        ]);


        // Create tenant for the business
        $domain = Str::slug($business->name) . ".127.0.0.1.nip.io";
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

        // $token = JWTAuth::fromUser($user);

        return response()->json([
            'message' => 'Tenant successfully registered',
            'business_owner' => $user,
            'business' => $business,
            'tenant' => $tenant,
            // 'token' => $token
        ], 201);
    }

    public function registerBusinessOwner(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            // 'username' => 'required|string|max:100',
            'email' => 'required|string|email|max:100|unique:users',
            'password' => 'required|string|min:6|confirmed', // include password_confirmation
            'phone' => 'nullable|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        $business = Business::create([
            // 'owner_id' => $user->id,
            'name' => $request->name . "'s Business",
            'slug' => Str::slug($request->name . '-business-' . time()),
            'email' => $request->email,
            'password' => $request->password,
            'phone' => $request->phone ?? null,
        ]);
        // $user = LandlordUser::create([
        //     'name' => $request->input('name'),
        //     'username' => $request->input('username'),
        //     'email' => $request->input('email'),
        //     'password' => Hash::make($request->input('password')),
        //     'phone' => $request->input('phone'),
        //     'status' => 'pending',
        // ]);

        return response()->json([
            'message' => 'Registration submitted successfully. Waiting for admin approval.',
            'business' => $business ?? null,
        ], 201);
    }


    // ✅ Login
    // public function login(Request $request)
    // {
    //     $credentials = $request->only('email', 'password');

    //     try {
    //         if (!$token = JWTAuth::attempt($credentials)) {
    //             return response()->json(['error' => 'Invalid credentials'], 401);
    //         }
    //     } catch (JWTException $e) {
    //         return response()->json(['error' => 'Could not create token'], 500);
    //     }

    //     return response()->json([
    //         'token' => $token,
    //         'user' => auth()->user()
    //     ]);
    // }

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        // Validation
        $validator = Validator::make($credentials, [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Check user first
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['error' => 'Invalid credentials'], 401);
        }

        // Prevent login if user not active
        if ($user->status !== 'active') {
            return response()->json(['error' => 'Your account is not approved yet.'], 403);
        }

        // Attempt to verify credentials and create a token
        if (!$token = JWTAuth::attempt($credentials)) {
            return response()->json(['error' => 'Invalid credentials'], 401);
        }


        // Return token + user info
        return response()->json([
            'message' => 'Login successful',
            'domain' => 'domain',
            'token' => $token,
            'user' => $user,
        ]);
    }

    // ✅ Get logged in user info
    public function me()
    {
        // dd("me");
        return response()->json(auth('api')->user());
    }

    // logged out user and destroy auth token
    public function logout()
    {
        auth('api')->logout();

        return response()->json([
            'message' => 'Successfully logged out'
        ]);
    }

    public function refresh()
    {
        try {
            $newToken = auth('api')->refresh();
            return response()->json([
                'message' => 'Token refreshed successfully',
                'token' => $newToken,
                'user' => auth('api')->user()
            ]);
        } catch (TokenInvalidException $e) {
            return response()->json(['error' => 'Invalid token'], 401);
        }
    }
}
