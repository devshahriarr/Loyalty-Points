<?php
namespace App\Http\Middleware;

use App\Models\SubscriptionUsage;
use App\Models\Tenant;
use App\Models\UserSubscription;
use App\Services\SubscriptionUsageService;
use Closure;
use Illuminate\Http\Request;

class CheckSubscriptionLimit
{
    public function handle(Request $request, Closure $next, string $key)
    {
        $user = auth('tenant')->user();

        if (! $user) {
            abort(401, 'Unauthorized');
        }

        $service = app(SubscriptionUsageService::class);

        if (! $service->canUse($key)) {
            abort(403, ucfirst($key).' limit exceeded');
        }

        return $next($request);
    }
}
