<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Product;
use App\Models\Package;
use App\Services\CBIRService;

class SyncAICoreCommand extends Command
{
    protected $signature = 'ai:sync';
    protected $description = 'Sync all existing products and packages to the AI Core index';

    public function handle(CBIRService $cbirService)
    {
        $this->info('Starting Comprehensive AI Core synchronization...');

        $products = Product::with('media', 'category', 'weddingOrganizer')->get();
        $packages = Package::with('media', 'category', 'weddingOrganizer')->get();

        $totalMedia = 0;
        foreach($products as $i) $totalMedia += $i->media->count();
        foreach($packages as $p) $totalMedia += $p->media->count();

        $this->info("Found {$totalMedia} media products to process.");
        $bar = $this->output->createProgressBar($totalMedia);
        $bar->start();

        $csvData = [];
        $csvHeader = ['ID', 'Type', 'Name', 'Category', 'Price', 'Discount_Price', 'Organizer', 'Image_Path', 'Description'];

        foreach ($products as $product) {
            foreach ($product->media as $media) {
                $cbirService->indexMedia($media);
                $csvData[] = [
                    $product->id,
                    'product',
                    $product->name,
                    $product->category?->name ?? 'N/A',
                    $product->price,
                    $product->discount_price,
                    $product->weddingOrganizer?->name ?? 'N/A',
                    $media->getPath(),
                    $product->description
                ];
                $bar->advance();
            }
        }

        foreach ($packages as $package) {
            foreach ($package->media as $media) {
                $cbirService->indexMedia($media);
                $csvData[] = [
                    $package->id,
                    'package',
                    $package->name,
                    $package->category?->name ?? 'N/A',
                    $package->price,
                    $package->discount_price,
                    $package->weddingOrganizer?->name ?? 'N/A',
                    $media->getPath(),
                    $package->description
                ];
                $bar->advance();
            }
        }

        $bar->finish();
        $this->newLine();

        // Generate CSV Dataset
        $csvPath = base_path('../ai_core/data/dataset.csv');
        $fp = fopen($csvPath, 'w');
        fputcsv($fp, $csvHeader);
        foreach ($csvData as $row) {
            fputcsv($fp, $row);
        }
        fclose($fp);

        $this->info("Synchronization completed! Dataset CSV generated at: {$csvPath}");
    }
}
