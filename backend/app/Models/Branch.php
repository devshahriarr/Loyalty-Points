<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    use TenantAwareModel;
    
    protected $fillable = [
        'name',
        'address',
        'phone',
        'email',
        'manager_name',
        'status',
        'tenant_id',
    ];
}
