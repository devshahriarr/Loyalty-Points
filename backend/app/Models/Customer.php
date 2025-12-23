<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;

class Customer extends Model
{
    use TenantAwareModel, UsesTenantConnection;

    protected $connection = 'tenant';

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

    public function analytics()
    {
        return $this->hasMany(CustomerAnalytics::class);
    }

    public function customerReviews()
    {
        return $this->hasMany(CustomerReview::class);
    }

    public function branches()
    {
        return $this->belongsToMany(Branch::class);
    }

    public function walletCards()
    {
        return $this->hasMany(CustomerLoyaltyCard::class);
    }
}
