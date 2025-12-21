<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomerAnalytics;
use App\Models\Tenant;
use App\Services\CustomerAnalyticsService;
use Illuminate\Http\Request;

class CustomerAnalyticsController extends Controller
{

    protected $service;

    public function __construct(CustomerAnalyticsService $service)
    {
        $this->service = $service;
    }

    /**
     * Dashboard listing: totals + paginated analytics
     */
    public function index(Request $request)
    {
        // Tenant context must already be active by middleware
        $perPage = (int) $request->get('per_page', 15);

        $totalCustomers = Customer::count();

        $currentMonth = now()->month;
        $visitsThisMonth = \App\Models\Visit::whereMonth('visited_at', now()->month)->count();

        $query = CustomerAnalytics::with('customer')->orderByDesc('rfm');

        // optional filters: segment, min_points, search
        if ($request->filled('segment')) {
            $query->where('segment', $request->segment);
        }
        if ($request->filled('min_points')) {
            $query->where('reward_points', '>=', (int)$request->min_points);
        }
        if ($request->filled('q')) {
            $q = $request->q;
            $query->whereHas('customer', function($qry) use ($q) {
                $qry->where('name', 'like', "%$q%")->orWhere('email', 'like', "%$q%");
            });
        }

        $customers = $query->paginate($perPage);

        return response()->json([
            'total_customers' => $totalCustomers,
            'visits_this_month' => $visitsThisMonth,
            'data' => $customers,
        ]);
    }

    /**
     * Force recalculation for a single customer (admin action)
     */
    public function recalcCustomer($customerId)
    {
        $analytics = $this->service->recalcCustomer($customerId);
        return response()->json(['success' => true, 'analytics' => $analytics]);
    }

    /**
     * Recalculate all customers (dispatch job or run synchronously)
     */
    public function recalcAll()
    {
        // For small tenants you may run synchronously (blocking)
        // For larger tenants, dispatch a job per customer
        $customers = Customer::select('id')->cursor();
        foreach ($customers as $c) {
            $this->service->recalcCustomer($c->id);
        }

        return response()->json(['success' => true, 'message' => 'Recalculation started']);
    }

}
