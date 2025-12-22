<?php
namespace App\Services;

use App\Models\SubscriptionUsage;

class SubscriptionUsageService
{
    public static function increment($key)
    {
        SubscriptionUsage::firstOrCreate(['key' => $key])
            ->increment('used');
    }

    /**
     * Reset all usage counters for a tenant
     */
    // public function resetUsage(int $tenantId): void
    // {
    //     SubscriptionUsage::where('_id', $tenantId)->delete();
    // }
}
