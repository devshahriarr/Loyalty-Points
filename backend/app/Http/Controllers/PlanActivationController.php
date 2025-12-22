<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use App\Models\SubscriptionFeature;
use App\Models\SubscriptionLimit;
use App\Models\SubscriptionUsage;
use App\Models\Tenant\User;
use App\Models\UserSubscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PlanActivationController extends Controller
{
    public function index()
    {
        /**
         * Show all plans (table view)
         */
        $plans = Subscription::with(['limits', 'features'])
            ->orderBy('price')
            ->get();

        return response()->json($plans);
    }

    /**
     * Update plan (Edit modal save)
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'price' => 'required|numeric',
            'is_active' => 'required|boolean',
            'limits' => 'required|array',
            'features' => 'required|array'
        ]);

        DB::transaction(function () use ($request, $id) {

            $plan = Subscription::findOrFail($id);

            $plan->update([
                'price' => $request->price,
                'is_active' => $request->is_active
            ]);

            /** -------------------------
             * Update Limits
             * ------------------------*/
            foreach ($request->limits as $key => $value) {
                SubscriptionLimit::updateOrCreate(
                    [
                        'subscription_id' => $plan->id,
                        'key' => $key
                    ],
                    [
                        'value' => $value
                    ]
                );
            }

            /** -------------------------
             * Update Features
             * ------------------------*/
            SubscriptionFeature::where('subscription_id', $plan->id)->delete();

            foreach ($request->features as $feature) {
                SubscriptionFeature::create([
                    'subscription_id' => $plan->id,
                    'feature' => $feature
                ]);
            }
        });

        return response()->json([
            'message' => 'Plan updated successfully'
        ]);
    }

    public function assignToTenant(Request $request)
    {
        $request->validate([
            'tenant_id' => 'required|exists:tenants,id',
            'subscription_id' => 'required|exists:subscriptions,id'
        ]);

        DB::transaction(function () use ($request) {

            // Disable previous subscriptions
            UserSubscription::where('tenant_id', $request->tenant_id)
                ->update(['status' => 'inactive']);

            // Assign new plan
            UserSubscription::create([
                'tenant_id' => $request->tenant_id,
                'subscription_id' => $request->subscription_id,
                'status' => 'active'
            ]);

            // Reset usage
            // app(\App\Services\SubscriptionUsageService::class)
                // ->resetUsage($request->tenant_id);
        });

        return response()->json([
            'message' => 'Plan activated for tenant'
        ]);
    }

    /**
     * Toggle plan status (enable / disable)
     */
    public function toggle($id)
    {
        $plan = Subscription::findOrFail($id);
        $plan->update(['is_active' => ! $plan->is_active]);

        return response()->json([
            'message' => 'Plan status updated',
            'is_active' => $plan->is_active
        ]);
    }
}
