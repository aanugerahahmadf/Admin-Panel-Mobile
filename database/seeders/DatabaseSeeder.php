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
            WeddingOrganizerSeeder::class,  // Wedding Organizer + logo
            ProductSeeder::class,           // Products + images/product/product-N.png
            PackageSeeder::class,           // Packages + images/package/package-N.png
            BannerSeeder::class,
            ArticleSeeder::class,
            TermsAndConditionsSeeder::class,
            VoucherSeeder::class,           // Voucher public & per-user
        ]);
    }
}
