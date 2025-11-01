<?php

namespace App\Actions;

use Spatie\Multitenancy\Actions\MigrateTenantAction;
use Symfony\Component\Console\Output\NullOutput;

class MigrateTenantSafely extends MigrateTenantAction
{
    public function __construct()
    {
        $this->output = new NullOutput();
    }

    public function execute($tenant)
    {
        // parent class-এর migrate call override করে দিচ্ছি
        $this->runMigration($tenant);
    }

    protected function runMigration($tenant)
    {
        // migrate command manually call
        \Artisan::call('migrate', [
            '--database' => 'tenant',
            '--path' => 'database/migrations/tenant',
            '--force' => true,
        ], $this->output);

        logger("Tenant {$tenant->name} migrated successfully");
    }
}
