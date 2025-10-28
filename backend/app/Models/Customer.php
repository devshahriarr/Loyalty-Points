<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use TenantAwareModel;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
        'status',
        'tenant_id',
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
