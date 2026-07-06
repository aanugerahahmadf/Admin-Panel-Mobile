<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Traits\TranslatesContent;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    use TranslatesContent;

    public function run(): void
    {
        $this->command->info('--- Seeding Products ---');

        $cats = Category::where('type', 'product')->get()->keyBy('slug');

        $products = [
            [
                'name' => 'Gebyok Ukir Jati Premium',
                'name_translations' => ['id' => 'Gebyok Ukir Jati Premium', 'en' => 'Premium Carved Teak Gebyok'],
                'category' => 'traditional',
                'price' => 15000000,
                'discount_pct' => 10,
                'stock' => 10,
                'image' => 'product-1.png',
                'description' => 'Gebyok ukir kayu jati pilihan dengan motif batik klasik. Cocok sebagai backdrop pelaminan adat Jawa yang megah.',
                'description_translations' => ['id' => 'Gebyok ukir kayu jati pilihan dengan motif batik klasik. Cocok sebagai backdrop pelaminan adat Jawa yang megah.', 'en' => 'Selected teak wood carved gebyok with classic batik motifs. Perfect as a magnificent Javanese traditional wedding backdrop.'],
            ],
            [
                'name' => 'Chandelier Kristal Modern',
                'name_translations' => ['id' => 'Chandelier Kristal Modern', 'en' => 'Modern Crystal Chandelier'],
                'category' => 'modern',
                'price' => 20000000,
                'discount_pct' => 10,
                'stock' => 8,
                'image' => 'product-2.png',
                'description' => 'Chandelier kristal premium untuk dekorasi ballroom modern. Memantulkan cahaya indah di setiap sudut ruangan.',
                'description_translations' => ['id' => 'Chandelier kristal premium untuk dekorasi ballroom modern. Memantulkan cahaya indah di setiap sudut ruangan.', 'en' => 'Premium crystal chandelier for modern ballroom decoration. Reflects beautiful light in every corner of the room.'],
            ],
            [
                'name' => 'Arch Kayu Rustic Natural',
                'name_translations' => ['id' => 'Arch Kayu Rustic Natural', 'en' => 'Natural Rustic Wood Arch'],
                'category' => 'rustic',
                'price' => 8500000,
                'discount_pct' => 12,
                'stock' => 15,
                'image' => 'product-3.png',
                'description' => 'Arch kayu alami dengan finishing natural, dihiasi pampas grass dan bunga liar segar. Sempurna untuk pernikahan outdoor.',
                'description_translations' => ['id' => 'Arch kayu alami dengan finishing natural, dihiasi pampas grass dan bunga liar segar. Sempurna untuk pernikahan outdoor.', 'en' => 'Natural wood arch with natural finish, decorated with pampas grass and fresh wildflowers. Perfect for outdoor weddings.'],
            ],
            [
                'name' => 'Flower Wall Blush Rose',
                'name_translations' => ['id' => 'Flower Wall Blush Rose', 'en' => 'Blush Rose Flower Wall'],
                'category' => 'minimalist',
                'price' => 6500000,
                'discount_pct' => 11,
                'stock' => 20,
                'image' => 'product-4.png',
                'description' => 'Dinding bunga mawar blush pink yang memukau. Menjadi spot foto favorit tamu undangan.',
                'description_translations' => ['id' => 'Dinding bunga mawar blush pink yang memukau. Menjadi spot foto favorit tamu undangan.', 'en' => 'Stunning blush pink rose flower wall. Becomes guests favorite photo spot.'],
            ],
            [
                'name' => 'Pergola Taman Inggris',
                'name_translations' => ['id' => 'Pergola Taman Inggris', 'en' => 'English Garden Pergola'],
                'category' => 'garden',
                'price' => 12000000,
                'discount_pct' => 10,
                'stock' => 6,
                'image' => 'product-5.png',
                'description' => 'Pergola bergaya taman Inggris dengan ivy dan mawar garden. Menciptakan suasana romantis di outdoor venue.',
                'description_translations' => ['id' => 'Pergola bergaya taman Inggris dengan ivy dan mawar garden. Menciptakan suasana romantis di outdoor venue.', 'en' => 'English garden style pergola with ivy and garden roses. Creates a romantic atmosphere at outdoor venues.'],
            ],
            [
                'name' => 'Pelaminan Emas Royal',
                'name_translations' => ['id' => 'Pelaminan Emas Royal', 'en' => 'Royal Gold Wedding Throne'],
                'category' => 'royal',
                'price' => 35000000,
                'discount_pct' => 9,
                'stock' => 3,
                'image' => 'product-6.png',
                'description' => 'Pelaminan mewah berlapis emas dengan ornamen kerajaan. Untuk pernikahan yang benar-benar berkesan.',
                'description_translations' => ['id' => 'Pelaminan mewah berlapis emas dengan ornamen kerajaan. Untuk pernikahan yang benar-benar berkesan.', 'en' => 'Luxurious gold-plated wedding throne with royal ornaments. For a truly memorable wedding.'],
            ],
            [
                'name' => 'Macrame Bohemian Backdrop',
                'name_translations' => ['id' => 'Macrame Bohemian Backdrop', 'en' => 'Bohemian Macrame Backdrop'],
                'category' => 'rustic',
                'price' => 4500000,
                'discount_pct' => 16,
                'stock' => 12,
                'image' => 'product-7.png',
                'description' => 'Backdrop macrame handmade dengan sentuhan bohemian. Unik, artistik, dan penuh karakter.',
                'description_translations' => ['id' => 'Backdrop macrame handmade dengan sentuhan bohemian. Unik, artistik, dan penuh karakter.', 'en' => 'Handmade macrame backdrop with bohemian touch. Unique, artistic, and full of character.'],
            ],
            [
                'name' => 'Neon Sign Custom Wedding',
                'name_translations' => ['id' => 'Neon Sign Custom Wedding', 'en' => 'Custom Wedding Neon Sign'],
                'category' => 'modern',
                'price' => 3500000,
                'discount_pct' => 14,
                'stock' => 25,
                'image' => 'product-8.png',
                'description' => 'Neon sign custom dengan nama pasangan atau quote favorit. Menjadi dekorasi sekaligus kenang-kenangan.',
                'description_translations' => ['id' => 'Neon sign custom dengan nama pasangan atau quote favorit. Menjadi dekorasi sekaligus kenang-kenangan.', 'en' => 'Custom neon sign with couple names or favorite quotes. Serves as both decoration and keepsake.'],
            ],
            [
                'name' => 'Candelabra Set Mewah',
                'name_translations' => ['id' => 'Candelabra Set Mewah', 'en' => 'Luxury Candelabra Set'],
                'category' => 'royal',
                'price' => 9000000,
                'discount_pct' => 11,
                'stock' => 8,
                'image' => 'product-9.png',
                'description' => 'Set candelabra emas mewah untuk dekorasi meja dan aisle. Memberikan kesan elegan dan dramatis.',
                'description_translations' => ['id' => 'Set candelabra emas mewah untuk dekorasi meja dan aisle. Memberikan kesan elegan dan dramatis.', 'en' => 'Luxurious gold candelabra set for table and aisle decoration. Adds an elegant and dramatic impression.'],
            ],
            [
                'name' => 'Greenery Wall Skandinavia',
                'name_translations' => ['id' => 'Greenery Wall Skandinavia', 'en' => 'Scandinavian Greenery Wall'],
                'category' => 'minimalist',
                'price' => 5500000,
                'discount_pct' => 9,
                'stock' => 18,
                'image' => 'product-10.png',
                'description' => 'Dinding hijau segar bergaya Skandinavia dengan tanaman pilihan. Bersih, natural, dan timeless.',
                'description_translations' => ['id' => 'Dinding hijau segar bergaya Skandinavia dengan tanaman pilihan. Bersih, natural, dan timeless.', 'en' => 'Fresh green wall in Scandinavian style with selected plants. Clean, natural, and timeless.'],
            ],
        ];

        foreach ($products as $data) {
            $slug = Str::slug($data['name']);
            $discountPrice = (int) round($data['price'] * (100 - $data['discount_pct']) / 100);

            $nameTranslations = $this->translateToAllLocales($data['name_translations']['id'], $data['name_translations']['en']);
            $descTranslations = $this->translateToAllLocales($data['description_translations']['id'], $data['description_translations']['en']);

            $product = Product::updateOrCreate(
                ['slug' => $slug],
                [
                    'category_id' => $cats[$data['category']]->id,
                    'name' => $data['name'],
                    'description' => $data['description'],
                    'name_translations' => $nameTranslations,
                    'description_translations' => $descTranslations,
                    'price' => $data['price'],
                    'discount_price' => $discountPrice,
                    'stock' => $data['stock'],
                    'is_active' => true,
                ]
            );

            $imagePath = public_path('images/product/'.$data['image']);
            if (file_exists($imagePath)) {
                $product->clearMediaCollection('product_image');
                try {
                    $product->addMedia($imagePath)
                        ->preservingOriginal()
                        ->toMediaCollection('product_image');

                    $this->command->line("  <info>✓</info> {$data['name']} [{$data['image']}]");
                } catch (\Exception $e) {
                    $this->command->error("  ✗ Gagal memuat gambar untuk: {$data['name']}");
                }
            } else {
                $this->command->line("  <info>✓</info> {$data['name']} (Tanpa Gambar)");
            }
        }

        $this->command->info('--- Product Seeding Complete ('.count($products).' products) ---');
    }
}
