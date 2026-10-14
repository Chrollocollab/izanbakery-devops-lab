<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create hardcoded admin user
        User::firstOrCreate(
            ['email' => 'admin@izanbakery.com'],
            [
                'name' => 'System Administrator',
                'password' => Hash::make('admin123'),
                'utype' => 'ADM',
                'mobile' => '0123456789',
                'email_verified_at' => now(),
            ]
        );

        // Create hardcoded regular user
        User::firstOrCreate(
            ['email' => 'user@izanbakery.com'],
            [
                'name' => 'Default User',
                'password' => Hash::make('user123'),
                'utype' => 'USR',
                'mobile' => '0987654321',
                'email_verified_at' => now(),
            ]
        );

        // Create additional test user
        User::firstOrCreate(
            ['email' => 'customer@test.com'],
            [
                'name' => 'Test Customer',
                'password' => Hash::make('password'),
                'utype' => 'USR',
                'mobile' => '0111222333',
                'email_verified_at' => now(),
            ]
        );

        $this->command->info('Default users created successfully!');
    }
}