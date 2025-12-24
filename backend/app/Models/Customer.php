<?php

// namespace App\Models;

// use Illuminate\Database\Eloquent\Model;
// use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;
// use Tymon\JWTAuth\Contracts\JWTSubject;

// class Customer extends Model implements JWTSubject
// {
//     use UsesTenantConnection;

//     protected $connection = 'tenant';

//     protected $fillable = [
//         'tenant_id',
//         'shop_name',
//         'name',
//         'email',
//         'password',
//     ];

//     protected $hidden = [
//         'password',
//         'remember_token',
//     ];

//     protected $casts = [
//         'password' => 'hashed',
//     ];

//     public function loyaltyCards()
//     {
//         return $this->hasMany(LoyaltyCard::class);
//     }

//     public function points()
//     {
//         return $this->hasMany(CustomerPoint::class);
//     }

//     public function analytics()
//     {
//         return $this->hasMany(CustomerAnalytics::class);
//     }

//     public function customerReviews()
//     {
//         return $this->hasMany(CustomerReview::class);
//     }

//     public function branches()
//     {
//         return $this->belongsToMany(Branch::class);
//     }

//     public function walletCards()
//     {
//         return $this->hasMany(CustomerLoyaltyCard::class);
//     }


//     /**
//      * Return the identifier that will be stored in the JWT subject claim.
//      */
//     public function getJWTIdentifier()
//     {
//         return $this->getKey();
//     }

//     /**
//      * Return a key value array, containing any custom claims to be added to the JWT.
//      */
//     public function getJWTCustomClaims()
//     {
//         return [
//             'role' => $this->role,
//         ];
//     }
// }
