<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Business;
use Illuminate\Support\Str;

class AdminController extends Controller
{
    public function approveBusinessOwner($id)
    {
        $user = User::where('id', $id)
            ->where('role', 'business_owner')
            ->where('status', 'pending')
            ->firstOrFail();

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

        return response()->json([
            'message' => 'Business owner approved successfully!',
            'user' => $user,
            'business' => $business,
        ]);
    }
}
