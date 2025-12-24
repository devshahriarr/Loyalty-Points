<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Tenant;
use App\Models\Tenant\User;
use Doctrine\Common\Lexer\Token;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;

class CustomerController extends Controller
{
    protected $host;
    protected $tenant;

    public function __construct(Request $request){
        $this->host = $request->getHost();
        $tenant = Tenant::where("domain", $this->host)->first();
        $this->tenant = $tenant;
        $this->tenant->makeCurrent();
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->tenant->makeCurrent();

        $customers = User::where([
            'role' => 'customer',
            'tenant_id'=> $this->tenant->id
        ])->first();

        $this->tenant->forget();

        return response()->json([
            'status' => 'success',
            'customers' => $customers
        ]);
    }

    public function login(Request $request){
        $credentials = $request->only('email', 'password');

        $this->tenant->makeCurrent();

        $customer = User::where([
            'role' => 'customer',
            'email' => $credentials['email'],
            'tenant_id'=> $this->tenant->id
        ])->first();

        $this->tenant->forget();

        if (! $token = auth('tenant')->attempt($credentials) && !$customer) {
            return response()->json(['error' => 'Invalid credentials'], 401);
        } else {

            $customer = auth('tenant')->user();

            if ($customer->status !== 'active') {
                return response()->json(['error' => 'Account inactive'], 403);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Customer login successful',
                'token' => $token,
                'customer' => $customer,
            ]);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'shop_name' => 'required|string|max:255',
            'name'=> 'required|string|max:255',
            'email'=> 'required|email|unique:customers',
            'password' => 'required|string|min:6|confirmed', // include password_confirmation

        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $this->tenant->makeCurrent();

        $customer = User::create([
            'tenant_id'=> $this->tenant->id,
            'shop_name' => $request->shop_name,
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'customer',
            'status' => 'active',
        ]);

        $this->tenant->forget();

        return response()->json([
            'status' => 'success',
            'message' => 'Customer created successfully',
            'customer' => $customer
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $this->tenant->makeCurrent();

        $customer = User::where([
            'role' => 'customer',
            'tenant_id'=> $this->tenant->id
        ])->findOrFail($id)->first();

        $this->tenant->forget();

        if (!$customer && $customer->tenant_id !== $this->tenant->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Customer not found'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Customer details',
            'customer' => $customer
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validated = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if ($validated->fails()) {
            return response()->json($validated->errors(), 422);
        }

        $this->tenant->makeCurrent();

        $customer = User::where([
            'role' => 'customer',
            'tenant_id'=> $this->tenant->id
        ])->findOrFail($id)->first();

        $customer->update([
            'name'=> $request->name,
            'password' => Hash::make($request->password),
        ]);

        $this->tenant->forget();

        return response()->json([
            'status' => 'success',
            'message' => 'Customer updated successfully',
            'customer' => $customer
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $this->tenant->makeCurrent();

        $customer = User::where('tenant_id', $this->tenant->id)->where('role', 'customer')->findOrFail($id);

        $this->tenant->forget();

        $customer->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Customer deleted successfully'
        ]);
    }

    public function logout()
    {
        $user = auth('tenant')->user();

        $this->tenant->makeCurrent();

        $customer = User::where([
            'role' => 'customer',
            'email' => $user->email,
            'tenant_id'=> $this->tenant->id
        ])->first();

        $this->tenant->forget();

        if (!$customer) {
            return response()->json([
                'message'=> 'Unauthenticated customer or customer not found.'
            ], 401);
        } else {
            auth('tenant')->logout();

            return response()->json([
                'message' => 'Customer successfully logged out'
            ]);
        }
    }

    public function me(){
        $user = auth('tenant')->user();

        $this->tenant->makeCurrent();

        $customer = User::where([
            'role' => 'customer',
            'email' => $user->email,
            'tenant_id'=> $this->tenant->id
        ])->first();

        $this->tenant->forget();

        if (!$customer) {
            return response()->json([
            'message'=> 'Unauthenticated customer or customer not found.'
            ]);
        }

        return response()->json([
            'status'=> 'success',
            'user' => $customer,
        ]);
    }

    public function refresh(){
        try{
            $user = auth('tenant')->user();

            $this->tenant->makeCurrent();

            $customer = User::where([
                'role' => 'customer',
                'email' => $user->email,
                'tenant_id'=> $this->tenant->id
            ])->first();

            $this->tenant->forget();

            if (!$customer) {
                return response()->json([
                    'message'=> 'Unauthenticated customer or customer not found.'
                ], 401);
            } else {
                $newToken = auth('tenant')->refresh();

                return response()->json([
                    'message' => 'Token refreshed successfully',
                    'token' => $newToken,
                    'customer' => $customer,
                ]);
            }
        } catch (TokenInvalidException $e) {
            return response()->json(['error' => 'Invalid token'], 401);
        }
    }

}
