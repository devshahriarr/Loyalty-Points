<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;

class UserSubscription extends Model
{
    use UsesTenantConnection;

    protected $connection = 'tenant';

    protected $fillable = [
        'user_id',
        'subscription_id',
        'status',
        'starts_at',
        'ends_at'
    ];

    public function subscription() {
        return $this->belongsTo(Subscription::class);
    }

    public function usages() {
        return $this->hasMany(SubscriptionUsage::class);
    }
}
