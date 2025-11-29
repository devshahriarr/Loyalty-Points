<?php

namespace App\Http\Controllers;

use App\Models\Offer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class OfferController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $offers = Offer::all();

        return response()->json([
            'status' => 'success',
            'offers' => $offers
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = Validator::make($request->all(), [
            "branch_id" => "required",
            "title" => "required",
            "description" => "required",
            "start_date" => "required",
            "end_date" => "required",
            "target_segment" => "required",
        ]);

        if ($validated->fails()) {
            return response()->json($validated->errors(), 422);
        }

        $offer = Offer::create($validated->validated());

        if (!$offer) {

            return response()->json([
                'status' => 'error',
                'message' => 'Offer not created',
            ], 500);

        }else{

            return response()->json([
                'status' => 'success',
                'message' => 'Offer created successfully',
                'offer' => $offer
            ]);

        }

    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $offer = Offer::findOrFail($id);

        if (!$offer) {

            return response()->json([
                'status' => 'error',
                'message' => 'Offer not found',
            ], 404);

        } else {

            return response()->json([
                'status' => 'success',
                'offer' => $offer
            ]);

        }

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validated = Validator::make($request->all(), [
           "branch_id" => "required",
           "title" => "required",
           "description" => "required",
           "start_date" => "required",
           "end_date" => "required",
           "target_segment" => "required",
        ]);

        if ($validated->fails()) {
            return response()->json($validated->errors(), 422);
        }

        $offer = Offer::findOrFail($id);

        if (!$offer) {

            return response()->json([
                'status' => 'error',
                'message' => 'Offer not found',
            ], 404);

        }else{

            $offer->update($validated->validated());

            return response()->json([
                'status' => 'success',
                'message' => 'Offer updated successfully',
                'offer' => $offer
            ]);

        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $offer = Offer::findOrFail($id);

        if (!$offer) {

            return response()->json([
                'status' => 'error',
                'message' => 'Offer not found',
            ], 404);

        }else{

            $offer->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Offer deleted successfully'
            ]);

        }
    }
}
