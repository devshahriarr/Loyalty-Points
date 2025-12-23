<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;

class CustomerLoyaltyCard extends Model
{
    use UsesTenantConnection;

    protected $connection = "tenant";
    protected $table = "customer_loyalty_cards";

    protected $fillable = [
        'customer_id',
        'loyalty_card_id',
        'progress',
        'completed'
    ];

    public function customers(){
        return $this->belongsToMany(Customer::class);
    }
}
