<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Business extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'email',
        'owner_id',
        'logo',
        'phone',
        'address',
        'status',
        'role',
    ];

    public function tenants()
    {
        return $this->hasMany(Tenant::class);
    }

    public function landlorduser()
    {
        return $this->belongsTo(LandlordUser::class);
    }
}
