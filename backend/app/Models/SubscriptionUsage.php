<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Multitenancy\Models\Concerns\UsesLandlordConnection;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;

class SubscriptionUsage extends Model
{
    use UsesLandlordConnection;
    protected $connection = "landlord";

    protected $fillable = ['user_subscriptions_id', 'key', 'used'];

    public function userSubscription()
    {
        return $this->belongsTo(UserSubscription::class);
    }
}
