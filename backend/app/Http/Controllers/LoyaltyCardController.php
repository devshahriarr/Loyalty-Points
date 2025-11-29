<?php

namespace App\Http\Controllers;

use App\Models\LoyaltyCard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LoyaltyCardController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $loyaltyCards = LoyaltyCard::all();

        return response()->json([
            'status' => 'success',
            'loyaltyCards' => $loyaltyCards,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = Validator::make($request->all(), [
            "name" => "required",
            "design_json" => "required",
            "reward_type" => "required",
            "reward_threshold" => "required",
            "reward_description" => "required",
        ]);

        if ($validated->fails()) {
            return response()->json($validated->errors(), 422);
        }

        LoyaltyCard::create($validated->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Loyalty card created successfully',
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $loyaltyCard = LoyaltyCard::findOrFail($id);

        return response()->json([
            'status' => 'success',
            'loyaltyCard' => $loyaltyCard,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validated = Validator::make($request->all(), [
            "name" => "required",
            "design_json" => "required",
            "reward_type" => "required",
            "reward_threshold" => "required",
            "reward_description" => "required",
        ]);

        if ($validated->fails()) {
            return response()->json($validated->errors(), 422);
        }

        $loyaltyCard = LoyaltyCard::findOrFail($id);

        if (!$loyaltyCard) {

            return response()->json([
                'status' => 'error',
                'message' => 'Loyalty card not found',
            ], 404);

        }else{

            $loyaltyCard->update($validated->validated());

            return response()->json([
                'status' => 'success',
                'message' => 'Loyalty card updated successfully',
                'loyaltyCard' => $loyaltyCard,
            ]);

        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $loyaltyCard = LoyaltyCard::findOrFail($id);

        if (!$loyaltyCard) {

            return response()->json([
                'status' => 'error',
                'message' => 'Loyalty card not found',
            ], 404);

        }else{

            $loyaltyCard->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Loyalty card deleted successfully'
            ]);

        }
    }
}
