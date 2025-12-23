<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;

class LoyaltyCardDesign extends Model
{
    use UsesTenantConnection;

    protected $connection = "tenant";
    protected $table = "loyalty_card_designs";

    protected $fillable = [
        'loyalty_card_id',
        'stamp_count',
        'logo',
        'background',
        'primary_color',
        'text_color',
        'active_stamp_color',
        'inactive_stamp_color',
    ];

    public function loyaltyCard()
    {
        return $this->belongsTo(LoyaltyCard::class);
    }
}
