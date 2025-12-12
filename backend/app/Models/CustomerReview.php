<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;

class CustomerReview extends Model
{
    use UsesTenantConnection;

    protected $table = "customer_reviews";

    protected $fillable = [
        'customer_id', 'tenant_id', 'review_text', 'rating', 'visited_at', 'visible'
    ];

    protected $casts = [
        'visible' => 'boolean',
        'visited_at' => 'datetime'
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
