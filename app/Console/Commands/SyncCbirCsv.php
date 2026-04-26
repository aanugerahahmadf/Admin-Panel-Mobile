<?php

namespace App\Console\Commands;

use App\Models\Package;
use App\Models\Product;
use Illuminate\Console\Command;

class SyncCbirCsv extends Command
{
    protected $signature = 'cbir:sync';

    protected $description = 'Sync database products and packages to CBIR dataset CSV';

    public function handle()
    {
        $this->info('Starting CBIR Dataset Sync...');

        $csvPath = 'D:/Weeding-Organizer-CBIR/ai_core/outputs/wedding_decorations_dataset.csv';
        $handle = fopen($csvPath, 'w');

        // Header
        fputcsv($handle, ['id', 'name', 'category', 'type', 'image_path', 'image_url', 'price']);

        // 1. Export Products
        $products = Product::all();
        foreach ($products as $product) {
            $media = $product->getFirstMedia('product_image');
            if ($media) {
                fputcsv($handle, [
                    $product->id,
                    $product->name,
                    $product->category?->slug ?? 'unknown',
                    'product',
                    $media->getPath(),
                    $product->image_url,
                    $product->price,
                ]);
            }
        }
        $this->info('Exported '.count($products).' products.');

        // 2. Export Packages
        $packages = Package::all();
        foreach ($packages as $package) {
            $media = $package->getFirstMedia('package_image');
            if ($media) {
                fputcsv($handle, [
                    $package->id,
                    $package->name,
                    $package->category?->slug ?? 'unknown',
                    'package',
                    $media->getPath(),
                    $package->image_url,
                    $package->price,
                ]);
            }
        }
        $this->info('Exported '.count($packages).' packages.');

        fclose($handle);
        $this->info("Sync complete! CSV saved to: {$csvPath}");

        return 0;
    }
}
