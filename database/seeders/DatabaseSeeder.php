<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Always seed users first (hardcoded system users)
        $this->call([
            UserSeeder::class,
            ProductSeeder::class,
            OrderSeeder::class,
            InfaqGallerySeeder::class,
        ]);
    }
}
