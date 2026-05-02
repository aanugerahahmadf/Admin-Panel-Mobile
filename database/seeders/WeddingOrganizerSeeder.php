<?php

namespace Database\Seeders;

use App\Models\WeddingOrganizer;
use Illuminate\Database\Seeder;

class WeddingOrganizerSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('--- Seeding Wedding Organizer ---');

        $wo = WeddingOrganizer::first();

        $contactData = [
            'name' => __('Dekorasi Bunga Pernikahan'),
            'address' => __('Rajasinga, Kec. Terisi, Kabupaten Indramayu, Jawa Barat'),
            'latitude' => -6.5614,
            'longitude' => 108.1289,
            'description' => __('Spesialis dekorasi bunga pernikahan premium dengan sentuhan artistik dan elegan. Lebih dari 10 tahun pengalaman menghias momen bahagia Anda.'),
            'is_verified' => true,
            'phone' => '(0234) 123-4567',       // Nomor telepon kantor/studio
            'whatsapp' => '+62 812-3456-7890',     // Nomor WhatsApp (HP)
            'email' => 'dekorasibunga@example.com',
            'instagram' => 'dekorasibunga_id',
            'operational_hours' => __('Senin - Minggu: 09:00 - 18:00'),
        ];

        if (! $wo) {
            $wo = WeddingOrganizer::create($contactData);
            $this->command->line('  <info>✓</info> Wedding Organizer created');
        } else {
            $wo->update($contactData);
            $this->command->line('  <info>✓</info> Wedding Organizer updated');
        }

        // Attach logo from public/images/logo.png
        $logoPath = public_path('images/logo.png');
        if (file_exists($logoPath)) {
            $wo->clearMediaCollection('logo');
            $wo->addMedia($logoPath)
                ->preservingOriginal()
                ->toMediaCollection('logo');
            $this->command->line('  <info>✓</info> Logo attached from images/logo.png');
        } else {
            $this->command->warn('  ⚠ Logo not found at public/images/logo.png');
        }

        // Attach gallery image from public/images/image.png
        $galleryPath = public_path('images/image.png');
        if (file_exists($galleryPath)) {
            $wo->clearMediaCollection('gallery');
            $wo->addMedia($galleryPath)
                ->preservingOriginal()
                ->toMediaCollection('gallery');
            $this->command->line('  <info>✓</info> Gallery image attached from images/image.png');
        } else {
            $this->command->warn('  ⚠ Gallery image not found at public/images/image.png');
        }

        // Attach video from public/videos/article/article-video-1.mp4
        $videoPath = public_path('videos/article/article-video-1.mp4');
        if (file_exists($videoPath)) {
            $wo->clearMediaCollection('videos');
            $wo->addMedia($videoPath)
                ->preservingOriginal()
                ->toMediaCollection('videos');
            $this->command->line('  <info>✓</info> Video attached from videos/article/article-video-1.mp4');
        } else {
            $this->command->warn('  ⚠ Video not found at public/videos/article/article-video-1.mp4');
        }

        $this->command->info('--- Wedding Organizer Seeding Complete ---');
    }
}
