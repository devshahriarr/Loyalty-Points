<?php

namespace App\Console\Commands;

use App\Models\LandlordUser;
use Exception;
use Illuminate\Console\Command;

use Illuminate\Support\Facades\DB;

class DebugTenantCreation extends Command
{
    protected $signature = 'debug:tenant';
    protected $description = 'Debug tenant creation';

    public function handle()
    {
        $this->info('Testing LandlordUser role assignment...');

        try {
            // Create a dummy user for testing
            $user = LandlordUser::create([
                'name' => 'Guard Test User',
                'username' => 'guardtest_' . time(),
                'email' => 'guardtest_' . time() . '@example.com',
                'password' => bcrypt('password'),
                'status' => 'pending',
            ]);

            $this->info("Created LandlordUser: {$user->id}");
            $this->info("User guard name: " . $user->guard_name);

            // Try assigning role
            $user->assignRole('business_owner');
            $this->info("Role assigned successfully!");

        } catch (Exception $e) {
            $this->error("Role assignment failed: " . $e->getMessage());
            $this->error($e->getTraceAsString());
        }
    }
}
