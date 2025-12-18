<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;

class Reward extends Model
{
    use UsesTenantConnection;

    protected $fillable = [
        'name', 'reward_type', 'earning_rule', 'threshold',
        'start_date', 'expire_date', 'logo', 'is_active'
    ];

    protected $casts = [
        'start_date' => 'date',
        'expire_date' => 'date',
        'is_active' => 'boolean'
    ];
}
