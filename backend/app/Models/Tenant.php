<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Multitenancy\Models\Tenant as BaseTenant;

class Tenant extends BaseTenant
{
    protected $fillable = [
        'name',
        'domain',
        'database',
        'business_id',
    ];

    public static function booted()
    {
        static::creating(function (Tenant $tenant) {
            $tenant->database = 'tenant_' . $tenant->id;
        });
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }
}
