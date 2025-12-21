<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Tenant;
use App\Services\SubscriptionUsageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BranchController extends Controller
{
    protected $host = "";
    protected $tenant;

    public function __construct(Request $request){
        $this->host = $request->getHost();
        $tenant = Tenant::where("domain", $this->host)->first();
        $this->tenant = $tenant;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $branches = Branch::where('tenant_id', $this->tenant->id)->get();

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
            'manager_name'=> 'required|string|max:255',
            'staffs'=> 'required|integer',
            // 'tenant_id'=> 'required|integer|exists:tenants,id',
            // 'latitude' => 'required',
            // 'longitude' => 'required',
        ]);

        if ($validated->fails()) {
            return response()->json($validated->errors(), 422);
        }

        $branch = Branch::create([
            'name' => $request->input('name'),
            'address' => $request->input('address'),
            'manager_name' => $request->input('manager_name'),
            'staffs' => $request->input('staffs'),
            'tenant_id' => $this->tenant->id?? null,
            'phone' => $request->input('phone') ?? null,
            'email' => $request->input('email') ?? null,
            'latitude' => $request->input('latitude') ?? null,
            'longitude =>' => $request->input('longitude') ?? null,
        ]);

        SubscriptionUsageService::increment('locations');

        return response()->json([
            'status' => 'success',
            'message' => 'Branch created successfully',
            'branch' => $branch
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $branch = Branch::where('tenant_id', $this->tenant->id)->findOrFail($id);

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
            'manager_name'=> 'required',
            'staffs'=> 'required',
            // 'latitude' => 'required',
            // 'longitude' => 'required',
        ]);

        if ($validated->fails()) {
            return response()->json($validated->errors(), 422);
        }

        $branch = Branch::where('tenant_id', $this->tenant->id)->findOrFail($id);

        if (!$branch) {

            return response()->json([
                'status' => 'error',
                'message' => 'Branch not found',
            ], 404);

        }else{

            $branch->update($request->only('name','address','manager_name','staffs','phone','email','latitude','longitude'));

            return response()->json([
                'status' => 'success',
                'message' => 'Branch updated successfully',
                'branch' => $branch
            ]);

        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $branch = Branch::where('tenant_id', $this->tenant->id)->findOrFail($id);

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
