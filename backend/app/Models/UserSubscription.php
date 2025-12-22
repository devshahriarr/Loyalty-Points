<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Multitenancy\Models\Concerns\UsesLandlordConnection;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;

class UserSubscription extends Model
{
    use UsesLandlordConnection;

    protected $connection = 'landlord';

    protected $fillable = ['tenant_id', 'subscription_id', 'status'];

    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }

    public function SubscriptionUsages()
    {
        return $this->hasMany(SubscriptionUsage::class);
    }

}
