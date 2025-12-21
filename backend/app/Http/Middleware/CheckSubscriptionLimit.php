<?php
namespace App\Http\Middleware;

use App\Models\Tenant\User;
use Closure;
use Illuminate\Http\Request;

class CheckSubscriptionLimit
{
    public function handle(Request $request, Closure $next, string $key)
    {
        $user = auth('tenant')->user();
        $tenantUser = User::where('id', $user->id)->first();
        $subscription = $tenantUser
        ->activeSubscription()
        ->with(['subscription.limits', 'usages'])
        ->firstOrFail();

        $limit = $subscription->subscription->limits
            ->where('key', $key)->first();

        $usage = $subscription->usages
            ->where('key', $key)->first();

        if ($limit && $limit->value !== null && $usage->used >= $limit->value) {
            abort(403, ucfirst($key).' limit exceeded');
        }

        return $next($request);
    }
}
