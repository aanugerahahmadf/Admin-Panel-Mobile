<?php

namespace Database\Seeders;

use App\Models\Help;
use Illuminate\Database\Seeder;

class HelpSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('--- Seeding Help Center ---');

        Help::updateOrCreate(
            ['id' => 1],
            [
                'title' => 'Pusat Bantuan',
                'subtitle' => 'Tim kami siap membantu kebutuhan dekorasi pernikahan Anda.',
                'contact_options' => [
                    [
                        'label' => 'WhatsApp Support',
                        'subLabel' => '+62 812-3456-7890',
                        'url' => 'https://wa.me/6281234567890',
                        'icon' => 'whatsapp',
                    ],
                    [
                        'label' => 'Email Support',
                        'subLabel' => 'support@weddingapp.com',
                        'url' => 'mailto:support@weddingapp.com',
                        'icon' => 'mail',
                    ],
                ],
                'faqs' => [
                    [
                        'question' => 'Bagaimana cara memesan paket dekorasi?',
                        'answer' => 'Anda dapat memilih produk atau paket yang diinginkan melalui aplikasi, lalu melanjutkan ke proses pembayaran Down Payment (DP) untuk mengamankan tanggal acara.',
                    ],
                    [
                        'question' => 'Apakah saya bisa menjadwal ulang (reschedule) tanggal acara?',
                        'answer' => 'Penjadwalan ulang diperbolehkan selambat-lambatnya 30 hari sebelum acara, bergantung pada ketersediaan jadwal tim kami.',
                    ],
                    [
                        'question' => 'Apakah DP bisa dikembalikan jika acara batal?',
                        'answer' => 'Down Payment (DP) bersifat non-refundable karena penjadwalan tim eksklusif. Untuk situasi Force Majeure, kami menawarkan opsi penjadwalan ulang berdasarkan kesepakatan bersama.',
                    ],
                ],
            ]
        );

        $this->command->line('  <info>✓</info> Help Center created');
        $this->command->info('--- Help Center Seeding Complete ---');
    }
}
