<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;

class LoyaltyCardRule extends Model
{
    use UsesTenantConnection;

    protected $connection = "tenant";
    protected $table = "loyalty_card_rules";

    protected $fillable = [
        'loyalty_card_id',
        'spend_amount',
        'earn_value',
        'earn_type',
        'earned_message',
    ];

    public function loyaltyCard()
    {
        return $this->belongsTo(LoyaltyCard::class);
    }
}
