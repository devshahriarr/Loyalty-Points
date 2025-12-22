<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\Tenant;
use App\Models\Tenant\User as TenantUser;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\SubscriptionSeeder;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

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
        try {
            $validated = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'email' => 'required|email|max:255|unique:businesses,email',
                'password' => 'required|string|min:6|confirmed',
                // 'slug' => 'required|string|max:255|unique:businesses,slug',
            ]);

            if($validated->fails()){
                return response()->json([
                    "status" => "error",
                    "message" => "Validation failed",
                    "errors" => $validated->errors()
                ], 422);
            }

            if ($request->registration_date) {
                $carbon_date = Carbon::parse($request->registration_date);
                $registration_date = $carbon_date->format('Y-m-d H:i:s');
            }

            $business = Business::create([
                // "owner_id" => $user->id,
                'name' => $request->name . "'s Business",
                'slug' => Str::slug($request->name . '-business-' . rand(1,100)),
                'email' => $request->email,
                "industry_type" => $request->industry_type,
                "total_branches" => $request->total_branches,
                "branch_locations" => $request->branch_locations,
                "registration_date" => $registration_date ?? "Not provided yet",
                "plan_type" => $request->plan_type,
                "billing_status" => $request->billing_status,
                "status" => "active",
            ]);

            $domain = Str::slug($business->name) . ".127.0.0.1.nip.io";
            $database = 'tenant_' . Str::slug($business->name, '_' . time());

            $tenant = Tenant::create([
                'name' => $business->name,
                'domain' => $domain,
                'database' => $database,
                'business_id' => $business->id,
            ]);

            $business->update([
                "owner_id"=> $tenant->id,
            ]);

            $tenant->makeCurrent();

            // Run tenant-only seeders
            Artisan::call('db:seed', [
                '--class' => SubscriptionSeeder::class,
                '--force' => true,
            ]);

            $tenantUser = TenantUser::create([
                "name" => $request->name,
                "username" => $request->name."-".Str::random(3),
                "email" => $request->email,
                "password" => Hash::make($request->password),
                "phone" => $user->phone ?? null,
                "address" => $request->address,
                "role" => "business_owner",
                "status" => "active",
            ]);

            // $tenantUser->assignRole('business_owner');

            $tenant->forget();

            return response()->json([
                "status" => "success",
                "message" => "Business created successfully",
                "business" => $business,
                "tenant" => $tenant,
                "tenantUser" => $tenantUser,
                "tenant_domain" => $domain,
                "tenant_url" => "http://{$domain}:8000",
            ]);
        } catch (Exception $e) {
            return response()->json([
                "status" => "error",
                "message"=> "Something went error. Please contact with support.",
                "error" => $e->getMessage()
            ], 500);
        }
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

    // public function update(Request $request, $id){
    //     $validated = Validator::make($request->all(), [

    //         'name' => 'sometimes|string|max:255',
    //         'slug' => 'required_with:name|string|max:255|unique:businesses,slug,'.$id,
    //         'logo' => 'nullable|string|max:255',
    //         'phone' => 'nullable|string|max:255',
    //         'address' => 'nullable|string|max:255',
    //     ]);

    //     if($validated->fails()){
    //         return response()->json([
    //             "status" => "error",
    //             "message" => "Validation failed",
    //             "errors" => $validated->errors()
    //         ], 422);
    //     }

    //     $business = Business::findOrFail($id);

    //     if (!$business) {
    //         return response()->json([
    //             "status" => "error",
    //             "message" => "Business not found"
    //         ], 404);
    //     } else {
    //         $business->update($request->only(['name', 'slug', 'logo', 'phone', 'address']));
    //         return response()->json([
    //             "status" => "success",
    //             "message" => "Business updated successfully",
    //             "business" => $business
    //         ]);
    //     }
    // }

    // public function destroy($id){
    //     $business = Business::findOrFail($id);
    //     if (!$business) {
    //         return response()->json([
    //             "status" => "error",
    //             "message" => "Business not found"
    //         ], 404);
    //     } else {
    //         $business->delete();
    //         return response()->json([
    //             "status" => "success",
    //             "message" => "Business deleted successfully",
    //             "business" => $business
    //         ]);
    //     }
    // }
}
