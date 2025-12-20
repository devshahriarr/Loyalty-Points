<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;

class SubscriptionLimit extends Model
{
    use UsesTenantConnection;

    protected $connection = 'tenant';

    protected $fillable = ['subscription_id', 'key', 'value'];

    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }
}
