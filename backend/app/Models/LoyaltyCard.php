<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoyaltyCard extends Model
{
    use TenantAwareModel;
    
    protected $fillable = [
        'customer_id',
        'card_number',
        'points',
        'status',
        'expiry_date',
        'tenant_id',
    ];
    
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
