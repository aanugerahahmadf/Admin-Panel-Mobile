<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call([
            RolesAndPermissionsSeeder::class,
            SuperAdminSeeder::class,
            CustomerSeeder::class,
            CategorySeeder::class,
            ProductSeeder::class,
            PackageSeeder::class,
            LegalSeeder::class,
            VoucherSeeder::class,
            ReviewSeeder::class,
            HelpSeeder::class,
        ]);

        if (File::exists('D:/Weeding-Organizer-CBIR/ai_core/data/dataset/decorations/')) {
            $this->call(DecorationDatasetSeeder::class);
        }
    }
}
