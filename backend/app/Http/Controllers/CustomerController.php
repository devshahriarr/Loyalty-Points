<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $customers = Customer::all();
        return response()->json([
            'status' => 'success',
            'customers' => $customers
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = Validator::make($request->all(), [
            'user_id' => 'required',
            'branch_id' => 'required',
            'loyalty_card_id' => 'required',
            'total_points' => 'required',
            'total_visits' => 'required',
            'last_visit' => 'required',
        ]);

        if ($validated->fails()) {
            return response()->json($validated->errors(), 422);
        }

        $customer = Customer::create($validated->validated());

        return response()->json([
            'status' => 'success',
            'customer' => $customer
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $customer = Customer::findOrFail($id);

        return response()->json([
            'status' => 'success',
            'customer' => $customer
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validated = Validator::make($request->all(), [
            'user_id' => 'required',
            'branch_id' => 'required',
            'loyalty_card_id' => 'required',
            'total_points' => 'required',
            'total_visits' => 'required',
            'last_visit' => 'required',
        ]);

        if ($validated->fails()) {
            return response()->json($validated->errors(), 422);
        }

        $customer = Customer::findOrFail($id);
        $customer->update($validated->validated());

        return response()->json([
            'status' => 'success',
            'customer' => $customer
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $customer = Customer::findOrFail($id);
        $customer->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Customer deleted successfully'
        ]);
    }
}
