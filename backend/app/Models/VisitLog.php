<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;

class VisitLog extends Model
{
    use UsesTenantConnection;

    protected $connection = "tenant";

    protected $fillable = [
        'customer_id',
        'branch_id',
        'detected_at',
        'lat',
        'lng',
        'distance_m',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
}
