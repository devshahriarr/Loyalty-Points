<?php

namespace App\Actions;

use Illuminate\Support\Facades\DB;
use Spatie\Multitenancy\Models\Tenant;

class CreateDatabase
{
    public function execute(Tenant $tenant): void
    {
        $dbName = $tenant->database;

        DB::statement("CREATE DATABASE IF NOT EXISTS `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
    }
}
