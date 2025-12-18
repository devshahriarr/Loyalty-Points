<?php

namespace App\Http\Controllers;

use App\Models\Reward;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class RewardController extends Controller
{
    // List rewards with search
    public function index(Request $request)
    {
        $query = Reward::query();

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where('name', 'like', "%$q%");
        }

        $rewards = $query->orderByDesc('created_at')->paginate(10);

        return response()->json([
            'status' => 'success',
            'data' => $rewards
        ]);
    }

    // Create reward
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'reward_type'   => 'required|string|max:255',
            'earning_rule'  => 'required|string|max:255',
            'threshold'     => 'required|integer|min:1',
            'start_date'    => 'nullable|date',
            'expire_date'   => 'nullable|date',
            'logo'          => 'nullable|file|image|mimes:jpg,png,jpeg|max:2048',
            'is_active'     => 'boolean'
        ]);

        if ($request->hasFile('logo')) {
            $validated['logo'] = $request->file('logo')->store('reward_logos', 'public');
        }

        $reward = Reward::create($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Reward created successfully',
            'data' => $reward
        ]);
    }

    // Update reward
    public function update(Request $request, $id)
    {
        $reward = Reward::findOrFail($id);

        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'reward_type'   => 'required|string|max:255',
            'earning_rule'  => 'required|string|max:255',
            'threshold'     => 'required|integer|min:1',
            'start_date'    => 'nullable|date',
            'expire_date'   => 'nullable|date',
            'logo'          => 'nullable|file|image|mimes:jpg,png,jpeg|max:2048',
            'is_active'     => 'boolean'
        ]);

        if ($request->hasFile('logo')) {
            // Delete old logo if exists
            if ($reward->logo) {
                Storage::disk('public')->delete($reward->logo);
            }
            $validated['logo'] = $request->file('logo')->store('reward_logos', 'public');
        }

        $reward->update($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Reward updated successfully',
            'data' => $reward
        ]);
    }

    // Toggle active/inactive
    public function toggle($id)
    {
        $reward = Reward::findOrFail($id);

        $reward->is_active = ! $reward->is_active;
        $reward->save();

        return response()->json([
            'status' => 'success',
            'is_active' => $reward->is_active
        ]);
    }

    // Delete reward
    public function destroy($id)
    {
        $reward = Reward::findOrFail($id);

        if ($reward->logo) {
            Storage::disk('public')->delete($reward->logo);
        }

        $reward->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Reward deleted successfully'
        ]);
    }
}
