<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BranchController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $host = $request->getHost();

        $tenant = Tenant::where('domain', $host)->first();

        $branches = Branch::where('tenant_id', $tenant->id)->get();

        return response()->json([
            'status' => 'success',
            'branches' => $branches?? "No branches found",
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = Validator::make($request->all(), [
            'name' => 'required',
            'address' => 'required',
            'latitude' => 'required',
            'longitude' => 'required',
        ]);

        if ($validated->fails()) {
            return response()->json($validated->errors(), 422);
        }

        Branch::create($request->all());

        return response()->json([
            'status' => 'success',
            'message' => 'Branch created successfully',
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $branch = Branch::findOrFail($id);

        return response()->json([
            'status' => 'success',
            'branch' => $branch,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validated = Validator::make($request->all(), [
            'name' => 'required',
            'address' => 'required',
            'latitude' => 'required',
            'longitude' => 'required',
        ]);

        if ($validated->fails()) {
            return response()->json($validated->errors(), 422);
        }

        $branch = Branch::findOrFail($id);

        if (!$branch) {

            return response()->json([
                'status' => 'error',
                'message' => 'Branch not found',
            ], 404);

        }else{

            $branch->update($validated->validated());

            return response()->json([
                'status' => 'success',
                'message' => 'Branch updated successfully',
            ]);

        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $branch = Branch::findOrFail($id);

        if (!$branch) {

            return response()->json([
                'status' => 'error',
                'message' => 'Branch not found',
            ], 404);

        }else{

            $branch->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Branch deleted successfully'
            ]);

        }
    }
}
