<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;

class CustomerAnalytics extends Model
{
    use UsesTenantConnection;

    protected $fillable = [
        'customer_id', 'rfm', 'segment', 'visits_count',
        'monetary_total', 'last_visit_at', 'reward_points', 'extra'
    ];

    protected $casts = [
        'last_visit_at' => 'datetime',
        'extra' => 'array'
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
