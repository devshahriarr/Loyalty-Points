<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Artisan;
use Spatie\Multitenancy\Models\Tenant as BaseTenant;
use Illuminate\Support\Facades\DB;
use Spatie\Multitenancy\Actions\MigrateTenantAction;
use Spatie\Multitenancy\Actions\CreateDatabase;

// use App\Actions\CreateDatabase;
use App\Actions\MigrateTenantSafely;
use Spatie\Permission\Models\Role;

class Tenant extends BaseTenant
{
    protected $fillable = [
        'name',
        'domain',
        'database',
        'business_id',
    ];

}
