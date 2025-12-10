<?php

namespace App\Http\Controllers;

use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Spatie\Multitenancy\Models\Tenant;
use Illuminate\Support\Facades\DB;

class TenantTestController extends Controller
{
    /**
     * Get tenant information
     */
    public function info()
    {
        // Ensure we have a current tenant
        if (!Tenant::checkCurrent()) {
            return response()->json([
                'error' => 'No tenant found for this domain',
                'domain' => request()->getHost()
            ], 400);
        }

        return response()->json([
            'success' => true,
            'tenant' => Tenant::current()->toArray(),
            'database' => config('database.connections.tenant.database'),
            'domain' => request()->getHost()
        ]);
    }

    /**
     * Test database connection
     */
    public function testDatabase()
    {
        // Ensure we have a current tenant
        if (!Tenant::checkCurrent()) {
            return response()->json([
                'error' => 'No tenant found for this domain',
                'domain' => request()->getHost()
            ], 400);
        }

        try {
            // Try to get some data from the tenant database
            $tables = DB::connection('tenant')->select('SHOW TABLES');
            $tenants = User::all();
            return response()->json([
                'success' => true,
                'tenant' => $tenants->toArray(),
                'tables' => $tables,
                'database' => config('database.connections.tenant.database')
            ]);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Database connection failed',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
