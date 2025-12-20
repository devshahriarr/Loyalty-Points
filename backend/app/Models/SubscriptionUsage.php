<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;

class SubscriptionUsage extends Model
{
    use UsesTenantConnection;

    protected $connection = 'tenant';

    protected $fillable = ['user_subscription_id', 'key', 'used'];

    public function userSubscription()
    {
        return $this->belongsTo(UserSubscription::class);
    }
}
