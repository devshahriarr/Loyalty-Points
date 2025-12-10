<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Multitenancy\Models\Concerns\UsesLandlordConnection;

class Business extends Model
{
    use UsesLandlordConnection;
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
        'password',
    ];


    public function landlorduser()
    {
        return $this->belongsTo(LandlordUser::class);
    }
}
