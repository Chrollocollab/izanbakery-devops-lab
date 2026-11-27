<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * No passwords live in the code (this repo is public). Each password comes from
     * .env (SEED_ADMIN_PASSWORD / SEED_USER_PASSWORD); if it isn't set, a random one
     * is generated and printed once to the console of whoever runs the seeder.
     */
    public function run(): void
    {
        $adminPassword = $this->password('SEED_ADMIN_PASSWORD', 'admin@izanbakery.com');
        $userPassword = $this->password('SEED_USER_PASSWORD', 'user@izanbakery.com and customer@test.com');

        // Admin user
        User::firstOrCreate(
            ['email' => 'admin@izanbakery.com'],
            [
                'name' => 'System Administrator',
                'password' => Hash::make($adminPassword),
                'utype' => 'ADM',
                'mobile' => '0123456789',
                'email_verified_at' => now(),
            ]
        );

        // Regular user
        User::firstOrCreate(
            ['email' => 'user@izanbakery.com'],
            [
                'name' => 'Default User',
                'password' => Hash::make($userPassword),
                'utype' => 'USR',
                'mobile' => '0987654321',
                'email_verified_at' => now(),
            ]
        );

        // Test customer
        User::firstOrCreate(
            ['email' => 'customer@test.com'],
            [
                'name' => 'Test Customer',
                'password' => Hash::make($userPassword),
                'utype' => 'USR',
                'mobile' => '0111222333',
                'email_verified_at' => now(),
            ]
        );

        $this->command->info('Default users created successfully!');
    }

    /**
     * Password from the environment, or a random one that is shown once.
     * firstOrCreate never changes an existing user, so this only matters on a fresh DB.
     */
    private function password(string $envKey, string $forWho): string
    {
        $password = env($envKey);

        if (filled($password)) {
            return $password;
        }

        $password = Str::password(20);
        $this->command->warn("{$envKey} not set. Generated password for {$forWho}: {$password}");

        return $password;
    }
}
