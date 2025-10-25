<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class SystemAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ensure the "system_admin" role exists
        $adminRole = Role::firstOrCreate(['name' => 'system_admin']);

        // Create the admin user if not already exists
        $admin = User::firstOrCreate(
            [
                'name' => 'Test User',
                'email' => 'admin@loyalty.com',
                'username' => 'superadmin',
                'password' => Hash::make('Admin@123'), // ✅ Default password (change later)
                'status'   => 'active',
            ]
        );

        // $user = User::create([
        //     'name' => 'Test User',
        //     'username' => 'superadmin', // important
        //     'email' => 'test@example.com',
        //     'password' => bcrypt('password'),
        //     'email_verified_at' => now(),
        // ]);


         $admin->assignRole($adminRole);

        // Assign role
        // if (!$admin->hasRole('system_admin')) {
        //     $admin->assignRole($adminRole);
        // }

        $this->command->info('✅ System Admin created successfully!');
        $this->command->warn('Email: admin@loyalty.com | Password: Admin@123');
    }
}
