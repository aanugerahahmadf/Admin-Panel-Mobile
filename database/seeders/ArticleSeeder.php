<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ArticleSeeder extends Seeder
{
    public function run(): void
    {
        // MASTER CLEAR: Sapu jagat semua media artikel sebelum seeding mulai!
        Article::all()->each(function ($article) {
            $article->clearMediaCollection('images');
            $article->clearMediaCollection('videos');
        });

        $admin = User::first(); // Assuming super_admin exists from previous seeders

        $articles = [
            [
                'title' => __('Cara Mempersiapkan Kulit Sebelum Hari-H Pernikahan'),
                'excerpt' => __('Tips perawatan kulit esensial untuk calon pengantin agar tampil glowing.'),
                'content' => __('<p>Agar dekorasi bunga pernikahan dapat tampil dengan sempurna dan memberikan hasil yang maksimal, pemilihan jenis bunga adalah kuncinya. Pastikan Anda sudah menentukan palet warna minimal 2 minggu sebelum hari H.</p><ul><li>Pilih bunga segar sesuai musim.</li><li>Hindari bunga yang mudah layu di cuaca panas.</li><li>Konsultasikan desain dengan tim kami.</p>'),
                'category' => __('Tips Kecantikan'),
                'image_url' => 'https://images.unsplash.com/photo-1522335789203-aabd1fc54bc9?q=80&w=1000&auto=format&fit=crop',
                'video_url' => 'https://media.w3.org/2010/05/sintel/trailer.mp4',
            ],
            [
                'title' => __('Inspirasi Dekorasi Bunga Pernikahan Modern 2026'),
                'excerpt' => __('Tren dekorasi bunga pernikahan dengan sentuhan modern yang elegan.'),
                'content' => __('<p>Gaya dekorasi bunga pernikahan terus berkembang. Tahun 2026 ini, tren "Floral Glass Look" sedang banyak diminati, memadukan bunga segar pilihan dengan aksen transparan yang anggun.</p><p>Konsep ini memberikan kesan mewah dan lapang, membuat momen bahagia Anda semakin berkesan bagi para tamu undangan.</p>'),
                'category' => __('Tren Pernikahan'),
                'image_url' => 'https://images.unsplash.com/photo-1519741497674-611481863552?q=80&w=1000&auto=format&fit=crop',
                'video_url' => null,
            ],
            [
                'title' => __('Mengapa Harus Memilih Dekorasi Bunga Pernikahan sebagai Vendor Anda?'),
                'excerpt' => __('Mengenal lebih jauh kualitas dan dedikasi tim Dekorasi Bunga Pernikahan.'),
                'content' => __('<p>Dekorasi Bunga Pernikahan bukan sekadar penyedia bunga, melainkan mitra dalam mewujudkan pernikahan impian Anda. Dengan pengalaman lebih dari 10 tahun, kami memahami detail kerumitan acara Anda.</p><p>Tim kami telah menangani berbagai gaya mulai dari adat tradisional Nusantara hingga gaya <em>International Garden Look</em>.</p>'),
                'category' => __('Profil Vendor'),
                'image_url' => 'https://images.unsplash.com/photo-1511795409834-ef04bbd61622?q=80&w=1000&auto=format&fit=crop',
                'video_url' => 'https://media.w3.org/2010/05/sintel/trailer.mp4',
            ],
            [
                'title' => __('Checklist Persiapan Pernikahan 6 Bulan Sebelum Acara'),
                'excerpt' => __('Langkah-langkah strategis agar persiapan pernikahan berjalan lancar.'),
                'content' => __('<p>Mulai dari menentukan budget hingga memilih vendor utama, checklist ini akan membantu Anda mengorganisir jadwal tanpa merasa terbebani di menit-menit terakhir.</p><ol><li>Bulan 1: Budget dan konsep</li><li>Bulan 2: Tentukan venue</li><li>Bulan 3: MUA dan Fotografer</li></ol><p>Patuhi checklist ini ya!</p>'),
                'category' => __('Persiapan Pernikahan'),
                'image_url' => 'https://images.unsplash.com/photo-1510076857177-7470076d4098?q=80&w=1000&auto=format&fit=crop',
                'video_url' => null,
            ],
        ];

        foreach ($articles as $article) {
            // Retrieve or create the category based on the array's category string
            $category = Category::firstOrCreate(
                ['slug' => Str::slug($article['category'])],
                ['name' => $article['category']]
            );

            $articleModel = Article::updateOrCreate(
                ['slug' => Str::slug($article['title'])],
                [
                    'title' => $article['title'],
                    'excerpt' => $article['excerpt'],
                    'content' => $article['content'],
                    'category_id' => $category->id,
                    'author_id' => $admin?->id ?? 1,
                    'wedding_organizer_id' => 1,
                    'image_url' => $article['image_url'],
                    'video_url' => $article['video_url'],
                    'is_published' => true,
                    'published_at' => now(),
                ]
            );

            // POWER FLUSH: Bersihkan images & videos biar gak ada logo nyangkut!
            $articleModel->clearMediaCollection('images');
            $articleModel->clearMediaCollection('videos');

            // Seed Images
            if ($articleModel->getMedia('images')->isEmpty()) {
                try {
                    $articleModel->addMediaFromUrl($article['image_url'])
                        ->toMediaCollection('images');
                } catch (\Exception $e) {
                }
            }

            // Seed Videos (HANYA INI YANG BIKIN PREVIEW MUNCUL!)
            if ($articleModel->video_url && $articleModel->getMedia('videos')->isEmpty()) {
                try {
                    // Pakai addMediaFromUrl untuk download video asli
                    $articleModel->addMediaFromUrl($articleModel->video_url)
                        ->toMediaCollection('videos');
                } catch (\Exception $e) {
                    // Jika gagal download, tetap simpan video_url sebagai backup
                }
            }
        }
    }
}
