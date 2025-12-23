<?php
namespace App\Services;

use App\Models\SubscriptionUsage;
use App\Models\UserSubscription;

class SubscriptionUsageService
{
    protected $subscription;

    public function __construct()
    {
        $this->subscription = UserSubscription::where('status', 'active')
            ->with(['subscription.limits', 'usages'])
            ->first();
    }

    public function canUse(string $key): bool
    {
        if (! $this->subscription) {
            return false;
        }

        $limit = $this->subscription->subscription
            ->limits
            ->where('key', $key)
            ->first();

        if (! $limit || $limit->value === null) {
            return true; // unlimited
        }

        $usage = $this->getUsage($key);

        return $usage->used < $limit->value;
    }

    public function increment(string $key, int $by = 1): void
    {
        $usage = $this->getUsage($key);
        $usage->increment('used', $by);
    }

    protected function getUsage(string $key)
    {
        return SubscriptionUsage::firstOrCreate(
            [
                'user_subscription_id' => $this->subscription->id,
                'key' => $key,
            ],
            ['used' => 0]
        );
    }

    public function decrement(string $key, int $by = 1): void
    {
        if (! $this->subscription) {
            return;
        }

        $usage = $this->getUsage($key);

        // Prevent negative values
        if ($usage->used <= 0) {
            return;
        }

        $usage->decrement('used', $by);
    }
}
