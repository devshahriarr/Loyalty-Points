<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Spatie\Multitenancy\Models\Tenant;

trait TenantAwareModel
{
    public static function bootTenantAwareModel()
    {
        static::creating(function ($model) {
            if (Tenant::checkCurrent()) {
                $model->tenant_id = Tenant::current()->id;
            }
        });

        static::addGlobalScope('tenant', function (Builder $builder) {
            if (Tenant::checkCurrent()) {
                $builder->where('tenant_id', Tenant::current()->id);
            }
        });
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}