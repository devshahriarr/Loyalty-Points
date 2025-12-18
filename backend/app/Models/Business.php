<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Multitenancy\Models\Concerns\UsesLandlordConnection;

class Business extends Model
{
    use UsesLandlordConnection;
    protected $fillable = [
        'owner_id',
        'name',
        'slug',
        'email',
        'industry_type',
        'total_branches',
        'branch_locations',
        'registration_date',
        'plan_type',
        'billing_status',
        'status',
    ];


    public function tenants(){
        return $this->hasMany(Tenant::class);
    }
}
