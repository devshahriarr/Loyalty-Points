<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;

class Visit extends Model
{
    use UsesTenantConnection;

    protected $fillable = [
        'customer_id', 'branch_id', 'amount', 'visited_at', 'meta'
    ];
    protected $casts = [
        'visited_at' => 'datetime',
        'meta' => 'array'
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
