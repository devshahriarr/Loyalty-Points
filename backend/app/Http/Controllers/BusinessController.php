<?php

namespace App\Http\Controllers;

use App\Models\Business;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BusinessController extends Controller
{
    public function index(){
        $all_business = Business::get();
        return response()->json([
            'status' => 'success',
            'businesses' => $all_business,
        ]);
    }

    public function store(Request $request){
        $validated = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:businesses,slug',
            'email' => 'required|email|max:255|unique:businesses,email',
            'owner_id' => 'required|integer|exists:users,id',
        ]);

        if($validated->fails()){
            return response()->json([
                "status" => "error",
                "message" => "Validation failed",
                "errors" => $validated->errors()
            ], 422);
        }

        $business = Business::create($request->all());
        return response()->json([
            "status" => "success",
            "message" => "Business created successfully",
            "business" => $business
        ]);
    }

    public function show($id){
        $business = Business::findOrFail($id);

        if (!$business) {
            return response()->json([
                "status" => "error",
                "message" => "Business not found"
            ], 404);
        } else {
            return response()->json([
                "status" => "success",
                "message" => "Business found successfully",
                "business" => $business
            ]);
        }
    }

    public function update(Request $request, $id){
        $validated = Validator::make($request->all(), [

            'name' => 'sometimes|string|max:255',
            'slug' => 'required_with:name|string|max:255|unique:businesses,slug,'.$id,
            'logo' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
        ]);

        if($validated->fails()){
            return response()->json([
                "status" => "error",
                "message" => "Validation failed",
                "errors" => $validated->errors()
            ], 422);
        }

        $business = Business::findOrFail($id);

        if (!$business) {
            return response()->json([
                "status" => "error",
                "message" => "Business not found"
            ], 404);
        } else {
            $business->update($request->only(['name', 'slug', 'logo', 'phone', 'address']));
            return response()->json([
                "status" => "success",
                "message" => "Business updated successfully",
                "business" => $business
            ]);
        }
    }

    public function destroy($id){
        $business = Business::findOrFail($id);
        if (!$business) {
            return response()->json([
                "status" => "error",
                "message" => "Business not found"
            ], 404);
        } else {
            $business->delete();
            return response()->json([
                "status" => "success",
                "message" => "Business deleted successfully",
                "business" => $business
            ]);
        }
    }
}
