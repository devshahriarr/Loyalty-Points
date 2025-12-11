<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;

class Branch extends Model
{
    use TenantAwareModel, UsesTenantConnection;

    protected $fillable = [
        'name',
        'address',
        'phone',
        'email',
        'manager_name',
        'staffs',
        'tenant_id',
        'status',
    ];
}
