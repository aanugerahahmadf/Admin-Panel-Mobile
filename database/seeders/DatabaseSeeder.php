<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RolesAndPermissionsSeeder::class,
            SuperAdminSeeder::class,
            ProductSeeder::class,
            PackageSeeder::class,
            LegalSeeder::class,
            VoucherSeeder::class,
            ReviewSeeder::class,
            HelpSeeder::class,
        ]);
    }
}
