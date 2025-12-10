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
            // DB::statement("CREATE DATABASE IF NOT EXISTS `{$tenant->database}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");

            // $dbName = $tenant->database;

        // DB::statement("CREATE DATABASE IF NOT EXISTS `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
            // app(CreateDatabase::class)->execute($tenant);

            // Tenant কে current tenant হিসেবে সেট করো
            DB::statement("CREATE DATABASE IF NOT EXISTS `{$tenant->database}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
            $tenant->makeCurrent();

            // app(MigrateTenantSafely::class)->execute($tenant);
            // app(MigrateTenantAction::class)->execute($tenant);

            // // মাইগ্রেশন চালাও
            Artisan::call('migrate', [
                '--database' => 'tenant', // tenant connection
                '--path' => 'database/migrations/tenants', // migration folder
                '--force' => true,
            ]);
            logger("Tenant {$tenant->name} migrated successfully");

            // Current tenant মুছে ফেলো
            $tenant->forgetCurrent();

            // app(MigrateTenantAction::class)->execute($tenant);

            // মাইগ্রেশন চালাও
            // app(MigrateTenantAction::class)->execute($tenant);

        });
    }


    public function tenantPermissions(){
        return $this->hasMany(TenantPermission::class);
    }
}
