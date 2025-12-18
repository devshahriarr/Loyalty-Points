<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

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
}
