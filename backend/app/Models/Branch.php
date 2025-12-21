<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;

class Branch extends Model
{
    use TenantAwareModel, UsesTenantConnection;

    protected $fillable = [
        'tenant_id',
        'name',
        'address',
        'phone',
        'email',
        'manager_name',
        'staffs',
        'latitude',
        'longitude',
        'status',
    ];

    public function customers()
    {
        return $this->belongsToMany(Customer::class);
    }
}
