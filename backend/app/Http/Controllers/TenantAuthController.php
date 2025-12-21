<?php

namespace App\Http\Controllers;

use App\Models\Tenant\User as TenantUser;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;

class TenantAuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (! $token = auth('tenant')->attempt($credentials)) {
            return response()->json(['error' => 'Invalid credentials'], 401);
        }

        $user = auth('tenant')->user();

        if ($user->status !== 'active') {
            return response()->json(['error' => 'Account inactive'], 403);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Business owner login successful',
            'token' => $token,
            'user' => $user,
            'role' => $user->role,
            // 'tenant' => $user->name,
        ]);
    }

    public function me(){
        $user = auth('tenant')->user();
        $owner = TenantUser::where('email', $user->email)->first();

        if (!$owner->role === 'business_owner') {
            return response()->json([
                'message' => 'You are not a business owner',
            ], 403);
        }

        return response()->json([
            'status'=> 'success',
            'user' => $owner,
        ]);
    }

    public function refresh(){
        try {
            $user = auth('tenant')->user();
            $owner = TenantUser::where('id', $user->id)->first();

            if ($owner->role === 'business_owner') {
                $newToken = auth('tenant')->refresh();
                return response()->json([
                    'message' => 'Token refreshed successfully',
                    'token' => $newToken,
                    'user' => auth('tenant')->user()
                ]);
            }

            return response()->json([
                'message' => 'You are not a system admin',
            ], 403);
        } catch (TokenInvalidException $e) {
            return response()->json(['error' => 'Invalid token'], 401);
        }
    }
}
