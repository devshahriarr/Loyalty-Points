<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use App\Models\SubscriptionUsage;
use App\Models\Tenant\User;
use App\Models\UserSubscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PlanActivationController extends Controller
{
    public function index()
    {
        return Subscription::with(['limits', 'features'])->get();
    }

    public function current()
    {
        $user = auth('tenant')->user();
        $tenantUser = User::where('id', $user->id)->first();
        return $tenantUser
            ->activeSubscription()
            ->with(['subscription.limits', 'subscription.features', 'usages'])
            ->first();
    }

    public function activate(Request $request)
    {
        $user = auth('tenant')->user();

        DB::transaction(function () use ($request, $user) {

            // Disable previous
            UserSubscription::where('user_id', $user->id)
                ->update(['status' => 'inactive']);

            $userSub = UserSubscription::create([
                'user_id' => $user->id,
                'subscription_id' => $request->subscription_id,
                'status' => 'active',
            ]);

            // reset usages
            foreach (['locations', 'cards'] as $key) {
                SubscriptionUsage::create([
                    'user_subscription_id' => $userSub->id,
                    'key' => $key,
                    'used' => 0,
                ]);
            }
        });

        return response()->json(['message' => 'Plan activated']);
    }
}
