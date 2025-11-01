<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerPoint extends Model
{
    use TenantAwareModel;
    
    protected $fillable = [
        'customer_id',
        'points',
        'transaction_type',
        'description',
    ];
    
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
