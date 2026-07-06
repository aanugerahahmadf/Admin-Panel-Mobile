<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Traits\TranslatesContent;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    use TranslatesContent;

    public function run(): void
    {
        $this->command->info('--- Seeding Categories ---');

        $productData = [
            'traditional' => 'Produk Tradisional',
            'modern'      => 'Produk Modern',
            'rustic'      => 'Produk Rustic',
            'minimalist'  => 'Produk Minimalis',
            'garden'      => 'Produk Taman',
            'royal'       => 'Produk Royal',
        ];

        $packageData = [
            'traditional' => 'Paket Tradisional',
            'modern'      => 'Paket Modern',
            'rustic'      => 'Paket Rustic',
            'minimalist'  => 'Paket Minimalis',
            'garden'      => 'Paket Taman',
            'royal'       => 'Paket Royal',
        ];

        foreach ($productData as $slug => $idName) {
            $translations = $this->translateToAllLocales($idName);
            Category::updateOrCreate(
                ['slug' => $slug, 'type' => 'product'],
                ['name' => $idName, 'name_translations' => $translations]
            );
            $this->command->line("  <info>✓</info> $idName");
        }

        foreach ($packageData as $slug => $idName) {
            $translations = $this->translateToAllLocales($idName);
            Category::updateOrCreate(
                ['slug' => $slug, 'type' => 'package'],
                ['name' => $idName, 'name_translations' => $translations]
            );
            $this->command->line("  <info>✓</info> $idName");
        }

        $this->command->info('--- Category Seeding Complete ---');
    }
}
