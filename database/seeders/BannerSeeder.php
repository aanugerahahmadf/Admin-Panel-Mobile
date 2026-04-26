<?php

namespace Database\Seeders;

use App\Models\Banner;
use Illuminate\Database\Seeder;

class BannerSeeder extends Seeder
{
    public function run(): void
    {
        $banners = [
            [
                'title' => __('Nikmati Hari Spesial Anda Bersama Kami'),
                'image_url' => 'https://images.unsplash.com/photo-1594463750939-ebb28c3f7f05?q=80&w=1000&auto=format&fit=crop',
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'title' => __('The Perfect Decoration for Your Wedding'),
                'image_url' => 'https://images.unsplash.com/photo-1519741497674-611481863552?q=80&w=1000&auto=format&fit=crop',
                'is_active' => true,
                'sort_order' => 2,
            ],
        ];

        foreach ($banners as $banner) {
            Banner::firstOrCreate(
                ['title' => $banner['title']],
                $banner
            );
        }
    }
}
