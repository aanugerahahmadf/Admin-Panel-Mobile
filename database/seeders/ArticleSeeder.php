<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use File;

class ArticleSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('--- Seeding Articles ---');
        
        // MASTER CLEAR: Sapu jagat semua media artikel sebelum seeding mulai!
        Article::all()->each(function ($article) {
            $article->clearMediaCollection('images');
            $article->clearMediaCollection('videos');
        });

        $admin = User::first(); // Assuming super_admin exists from previous seeders

        $articles = [
            [
                'title' => __('Tren Dekorasi Bunga Pernikahan Mewah 2026'),
                'excerpt' => __('Inspirasi penataan bunga segar yang elegan untuk momen spesial Anda.'),
                'content' => __("Memilih dekorasi bunga yang tepat adalah kunci utama dalam menciptakan atmosfer pernikahan yang tak terlupakan. Tahun ini, penggunaan kombinasi bunga Mawar premium, Hydrangea, dan sentuhan dedaunan hijau alami menjadi pilihan favorit para pengantin.\n\nPenataan bunga di pelaminan yang megah, aksen bunga pada sepanjang lorong pengantin, serta centerpiece meja tamu yang artistik adalah fokus utama kami.\n\nTim Dekorasi Bunga Pernikahan siap mewujudkan konsep impian Anda dengan kualitas bunga terbaik dan desain yang eksklusif."),
                'category' => __('Dekorasi Bunga'),
                'image_name' => 'article-1.png',
                'video_name' => 'article-video-1.mp4',
            ],
            [
                'title' => __('Seni Penataan Centerpiece Meja Tamu yang Artistik'),
                'excerpt' => __('Bagaimana menciptakan keindahan di setiap meja tamu dengan sentuhan bunga floral.'),
                'content' => __("Centerpiece meja bukan sekadar hiasan, melainkan elemen yang membangun kehangatan suasana bagi para tamu. Dengan perpaduan vas kristal tinggi dan bunga-bunga eksotis seperti Anggrek dan Peony, meja tamu akan tampak sangat mewah dan berkelas.\n\nSetiap detail kami perhatikan untuk memberikan pengalaman visual yang memanjakan mata setiap tamu undangan Anda."),
                'category' => __('Desain Interior'),
                'image_name' => 'article-2.png',
                'video_name' => 'article-video-2.mp4',
            ],
            [
                'title' => __('Keajaiban Floral Arch di Altar Pernikahan'),
                'excerpt' => __('Menciptakan gerbang cinta yang megah dengan balutan bunga-bunga pilihan.'),
                'content' => __("Floral Arch atau gerbang bunga adalah pusat perhatian di setiap upacara pernikahan. Kami merancang arch yang meluap dengan keindahan bunga Lily dan Mawar Champagne untuk menciptakan latar belakang yang romantis dan puitis.\n\nSempurnakan momen janji suci Anda dengan estetika floral yang luar biasa dari tim ahli kami."),
                'category' => __('Pelaminan'),
                'image_name' => 'article-3.png',
                'video_name' => 'article-video-3.mp4',
            ],
            [
                'title' => __('Makna di Balik Buket Bunga Pengantin Anda'),
                'excerpt' => __('Mengenal filosofi dan tips memilih buket bunga pengantin yang sempurna.'),
                'content' => __("Buket bunga pengantin adalah simbol kebahagiaan dan awal yang baru. Pemilihan bunga seperti Ranunculus dan Eucalyptus memberikan kesan modern namun tetap elegan. Pastikan buket Anda mencerminkan kepribadian dan gaya pernikahan Anda.\n\nKami menyediakan layanan konsultasi khusus untuk memastikan buket Anda adalah karya seni yang unik dan personal."),
                'category' => __('Buket Pengantin'),
                'image_name' => 'article-4.png',
                'video_name' => 'article-video-4.mp4',
            ],
        ];

        foreach ($articles as $article) {
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
                    'image_url' => asset('images/article/' . $article['image_name']),
                    'video_url' => $article['video_name'] ? asset('videos/article/' . $article['video_name']) : null,
                    'is_published' => true,
                    'published_at' => now(),
                ]
            );

            // Seed Image
            $imagePath = public_path('images/article/' . $article['image_name']);
            if (File::exists($imagePath)) {
                try {
                    $articleModel->addMedia($imagePath)
                        ->preservingOriginal()
                        ->toMediaCollection('images');
                } catch (\Exception $e) {}
            }

            // Seed Video
            if ($article['video_name']) {
                $videoPath = public_path('videos/article/' . $article['video_name']);
                if (File::exists($videoPath)) {
                    try {
                        $articleModel->addMedia($videoPath)
                            ->preservingOriginal()
                            ->toMediaCollection('videos');
                        
                        $this->command->line("  <info>✓</info> {$article['title']} [{$article['image_name']}] + [{$article['video_name']}]");
                        continue;
                    } catch (\Exception $e) {}
                }
            }

            $this->command->line("  <info>✓</info> {$article['title']} [{$article['image_name']}]");
        }

        $this->command->info('--- Article Seeding Complete (' . count($articles) . ' articles) ---');
    }
}
