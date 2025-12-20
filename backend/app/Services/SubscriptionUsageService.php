<?php
namespace App\Services;

use App\Models\SubscriptionUsage;
use App\Models\Tenant\User;

class SubscriptionUsageService
{
    public static function increment(string $key): void
    {
        $user = auth('tenant')->user();
        $tenantUser = User::where('id', $user->id)->first();
        $subscription = $tenantUser
            ->activeSubscription()
            ->with('usages')
            ->first();

        $usage = $subscription->usages
            ->where('key', $key)
            ->first();

        if ($usage) {
            $usage->increment('used');
        }
    }
}
