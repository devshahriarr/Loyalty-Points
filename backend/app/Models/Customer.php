<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;
use Tymon\JWTAuth\Contracts\JWTSubject;

class Customer extends Model
{
    use UsesTenantConnection;

    protected $connection = 'tenant';

    protected $fillable = [
        'tenant_id',
        'shop_name',
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'password' => 'hashed',
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
