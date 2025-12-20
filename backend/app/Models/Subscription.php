<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;

class Subscription extends Model
{
    use UsesTenantConnection;

    protected $connection = 'tenant';

    protected $fillable = ['code', 'name', 'price', 'billing_cycle', 'is_active'];

    public function limits() {
        return $this->hasMany(SubscriptionLimit::class);
    }

    public function features() {
        return $this->hasMany(SubscriptionFeature::class);
    }
}
