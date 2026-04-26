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
            'name' => 'Dekorasi Bunga Pernikahan',
            'address' => 'Rajasinga, Kec. Terisi, Kabupaten Indramayu, Jawa Barat',
            'latitude' => -6.5614,
            'longitude' => 108.1289,
            'description' => 'Spesialis dekorasi bunga pernikahan premium dengan sentuhan artistik dan elegan. Lebih dari 10 tahun pengalaman menghias momen bahagia Anda.',
            'is_verified' => true,
            'phone' => '(0234) 123-4567',       // Nomor telepon kantor/studio
            'whatsapp' => '+62 812-3456-7890',     // Nomor WhatsApp (HP)
            'email' => 'dekorasibunga@example.com',
            'instagram' => 'dekorasibunga_id',
            'operational_hours' => 'Senin - Minggu: 09:00 - 18:00',
        ];

        if (! $wo) {
            $wo = WeddingOrganizer::create($contactData);
            $this->command->info('  ✓ Wedding Organizer created');
        } else {
            $wo->update($contactData);
            $this->command->info('  ✓ Wedding Organizer updated with contact info');
        }

        // Attach logo from public/images/logo.png
        $logoPath = public_path('images/logo.png');
        if (file_exists($logoPath)) {
            $wo->clearMediaCollection('logo');
            $wo->addMedia($logoPath)
                ->preservingOriginal()
                ->toMediaCollection('logo');
            $this->command->info('  ✓ Logo attached from images/logo.png');
        } else {
            $this->command->warn('  ⚠ Logo not found at public/images/logo.png — please add your logo there.');
        }

        $this->command->info('--- Wedding Organizer Seeding Complete ---');
    }
}
