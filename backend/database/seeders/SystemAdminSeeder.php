<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Config;

class SystemAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ensure the "system_admin" role exists in landlord connection with correct guard
        $landlord = Config::get('multitenancy.landlord_database_connection_name', 'landlord');
        $defaultGuard = Config::get('auth.defaults.guard', 'api');

        $adminRole = Role::on($landlord)->firstOrCreate([
            'name' => 'system_admin',
            'guard_name' => $defaultGuard,
        ]);

        // Create the admin user if not already exists
        // Create the admin user in landlord connection (avoid tenant connection during seed)
        $admin = User::on($landlord)->firstOrCreate(
            [
                'email' => 'admin@loyalty.com',
                'username' => 'superadmin',
            ],
            [
                'name' => 'System Admin',
                'password' => Hash::make('Admin@123'), // Default password (change later)
                // 'role' => 'system_admin',
            ]
        );

        // Set non-fillable or additional attributes explicitly
        if ($admin->status !== 'active') {
            $admin->status = 'active';
            $admin->save();
        }

        // $user = User::create([
        //     'name' => 'Test User',
        //     'username' => 'superadmin', // important
        //     'email' => 'test@example.com',
        //     'password' => bcrypt('password'),
        //     'email_verified_at' => now(),
        // ]);


        // Assign role
        $admin->assignRole($adminRole);

        // Assign role
        // if (!$admin->hasRole('system_admin')) {
        //     $admin->assignRole($adminRole);
        // }

        $this->command->info('✅ System Admin created successfully!');
        $this->command->warn('Email: admin@loyalty.com | Password: Admin@123');
    }
}
