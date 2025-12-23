<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;

class LoyaltyCard extends Model
{
    use UsesTenantConnection;

    // protected $table = "loyalty_cards";
    protected $connection = "tenant";

    protected $fillable = [
        'type','name','company_name','description',
        'barcode_type','status','qr_code'
    ];

    public function rule()
    {
        return $this->hasOne(LoyaltyCardRule::class);
    }

    public function design()
    {
        return $this->hasOne(LoyaltyCardDesign::class);
    }
}
