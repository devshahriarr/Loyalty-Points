<?php

namespace App\Http\Controllers;

use App\Models\Staff;
use App\Models\Tenant;
use App\Models\Tenant\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class StaffController extends Controller
{
    protected $host;
    protected $tenant;
    public function __construct(){
        $this->host = request()->getHost();
        $tenant = Tenant::where("domain", $this->host)->first();
        $this->tenant = $tenant;
    }

    public function index(Request $request){
        $staffs = User::where([
            'role' => 'staff',
            'tenant_id'=> $this->tenant->id
        ])->first();

        return response()->json([
            'status' => 'success',
            'staffs' => $staffs
        ]);
    }

    public function logout()
    {
        $user = auth('tenant')->user();
        $staff = User::where('email', $user->email)->first();

        if (!$staff) {
            return response()->json([
                'message'=> 'Unauthenticated staff. Logout is not possible'
            ], 401);
        }else{
            auth('tenant')->logout();

            return response()->json([
                'message' => 'Successfully logged out'
            ]);
        }
    }

    public function me(){
        $user = auth('tenant')->user();
        $staff = User::where('email', $user->email)->first();

        return response()->json([
            'status'=> 'success',
            'user' => $staff,
        ]);
    }

    public function refresh(){
        $user = auth('staff')->user();
        $staff = User::where('email', $user->email)->first();

        if (!$staff) {
            return response()->json([
                'message'=> 'Unauthenticated staff. Refresh is not possible'
            ], 401);
        }else{
            $newToken = auth('staff')->refresh();

            return response()->json([
                'message' => 'Token refreshed successfully',
                'token' => $newToken,
                'staff' => auth('staff')->user()
            ]);
        }
    }

    public function register(Request $request){
        $validated = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if ($validated->fails()) {
            return response()->json($validated->errors(), 422);
        }

        $staff = User::create([
            'tenant_id'=> $this->tenant->id,
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'staff',
            'status' => 'active',
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Staff created successfully',
            'staff' => $staff
        ]);
    }

    public function show($id){
        $staff = User::where('role', 'staff')->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'message' => 'Staff details',
            'staff' => $staff
        ]);
    }

    public function update(Request $request, $id){
        $validated = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if ($validated->fails()) {
            return response()->json($validated->errors(), 422);
        }

        $staff = User::where('role', 'staff')->findOrFail($id);

        $staff->update([
            'name'=> $request->name,
            'password' => Hash::make($request->password),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Staff updated successfully',
            'staff' => $staff
        ]);
    }

    public function destroy($id){
        $staff = User::where('role', 'staff')->findOrFail($id);
        $staff->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Staff deleted successfully'
        ]);
    }

    public function login(Request $request){
        $credentials = $request->only('email', 'password');

        $staff = User::where('email', $credentials['email'])->where('role', 'staff')->first();

        $token = auth('tenant')->attempt($credentials);

        if (!$token) {

            return response()->json(['error' => 'Invalid credentials'], 401);

        } elseif(!$staff) {

            return response()->json(['error'=> 'Invalid credentials'],401);

        } elseif ($staff->status !== 'active') {

            return response()->json(['error' => 'Account inactive'], 403);

        } else {
            $staff = auth('tenant')->user();
            $this->tenant->forget();

            return response()->json([
                'status' => 'success',
                'message' => 'Staff login successful',
                'token' => $token,
                'customer' => $staff,
            ]);
        }
    }


}
