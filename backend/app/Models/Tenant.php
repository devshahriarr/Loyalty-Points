<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Artisan;
use Spatie\Multitenancy\Models\Tenant as BaseTenant;
use Illuminate\Support\Facades\DB;
use Spatie\Multitenancy\Actions\MigrateTenantAction;

class Tenant extends BaseTenant
{
    protected $fillable = [
        'name',
        'domain',
        'database',
        'business_id',
    ];

    // public static function booted()
    // {
    //     static::creating(function (Tenant $tenant) {
    //         $tenant->database = 'tenant_' . $tenant->id;
    //     });
    // }

    protected static function booted()
    {
        static::created(function (Tenant $tenant) {
            // যদি database আগে না থাকে, তাহলে তৈরি করো
            DB::statement("CREATE DATABASE IF NOT EXISTS `{$tenant->database}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");

            // Tenant কে current tenant হিসেবে সেট করো
            $tenant->makeCurrent();

            // মাইগ্রেশন চালাও
            Artisan::call('migrate', [
                '--database' => 'tenant', // tenant connection
                '--path' => '/database/migrations/tenant', // migration folder
                '--force' => true,
            ]);

            // Current tenant মুছে ফেলো
            $tenant->forgetCurrent();
        });
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }
}
