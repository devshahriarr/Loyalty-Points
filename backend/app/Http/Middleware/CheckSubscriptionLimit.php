<?php
namespace App\Http\Middleware;

use App\Models\SubscriptionUsage;
use App\Models\Tenant;
use App\Models\UserSubscription;
use Closure;
use Illuminate\Http\Request;

class CheckSubscriptionLimit
{
    public function handle(Request $request, Closure $next, string $key)
    {
        $host = $request->getHost();
        $tenant = Tenant::where("domain", $host)->first();

        $subscription = UserSubscription::with('subscription.limits')
        ->where('tenant_id', $tenant->id)
        ->where('status', 'active')
        ->firstOrFail();

        $limit = $subscription->subscription->limits->where('key', $key)->first();
        $usage = SubscriptionUsage::firstOrCreate(['key' => $key]);

        if ($limit && $limit->value !== null && $usage->used >= $limit->value) {
            abort(403, ucfirst($key) . ' limit exceeded');
        }

        return $next($request);
    }
}
