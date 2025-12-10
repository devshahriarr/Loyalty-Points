<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;

class Customer extends Model
{
    use TenantAwareModel, UsesTenantConnection;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
        'status',
    ];

    public function loyaltyCards()
    {
        return $this->hasMany(LoyaltyCard::class);
    }

    public function points()
    {
        return $this->hasMany(CustomerPoint::class);
    }
}
