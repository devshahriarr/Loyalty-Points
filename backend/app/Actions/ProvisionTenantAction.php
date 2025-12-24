<?php
namespace App\Actions;

use App\Models\Tenant;
use Database\Seeders\RoleSeeder;
use Database\Seeders\SubscriptionSeeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class ProvisionTenantAction
{
    public function execute(Tenant $tenant): void
    {
        // 1. Create database
        DB::statement(
            "CREATE DATABASE IF NOT EXISTS `{$tenant->database}`
             CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
        );

        // 2. Switch context
        $tenant->makeCurrent();

        // 3. Run tenant migrations
        Artisan::call('migrate', [
            '--database' => 'tenant',
            '--path' => 'database/migrations/tenants',
            '--force' => true,
        ]);

        // 4. (Optional) Seed tenant defaults
        Artisan::call('db:seed', [
            '--class' => RoleSeeder::class,
            '--database' => 'tenant',
            '--force' => true,
        ]);

        // // Run tenant-only seeders
        // Artisan::call('db:seed', [
        //     '--class' => SubscriptionSeeder::class,
        //     '--force' => true,
        // ]);

        logger("Tenant {$tenant->name} migrated successfully");

        // 5. Leave tenant context
        $tenant->forgetCurrent();
    }
}
