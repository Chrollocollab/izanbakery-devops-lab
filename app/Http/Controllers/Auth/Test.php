<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email'],
            [
                'name' => 'System Administor',
                'password' => Hash::make('admin23'),
                'utype' => 'ADM',
                'mobile' => '0123456789',
                'email_verified_at' => now(),
            ]
        );

        User::firstOrCreate(
            ['email' => 'user@izanbakery.com'],
            [
                'name' => 'Default User',
                'password' => Hash::make('user123'),
                'utype' => 'USR',
                'mobile' => '0129876543',
                'email_verified_at' => now(),
            ]
        );

        $this->command->info('Default User had been created');
    }
}