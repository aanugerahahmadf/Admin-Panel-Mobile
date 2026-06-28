<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('--- Seeding Products ---');

        $cats = $this->ensureCategories();

        $products = [
            [
                'name' => 'Gebyok Ukir Jati Premium',
                'category' => 'traditional',
                'price' => 15000000,
                'discount' => null,
                'stock' => 10,
                'image' => 'product-1.png',
                'description' => 'Gebyok ukir kayu jati pilihan dengan motif batik klasik. Cocok sebagai backdrop pelaminan adat Jawa yang megah.',
            ],
            [
                'name' => 'Chandelier Kristal Modern',
                'category' => 'modern',
                'price' => 20000000,
                'discount' => 18000000,
                'stock' => 8,
                'image' => 'product-2.png',
                'description' => 'Chandelier kristal premium untuk dekorasi ballroom modern. Memantulkan cahaya indah di setiap sudut ruangan.',
            ],
            [
                'name' => 'Arch Kayu Rustic Natural',
                'category' => 'rustic',
                'price' => 8500000,
                'discount' => null,
                'stock' => 15,
                'image' => 'product-3.png',
                'description' => 'Arch kayu alami dengan finishing natural, dihiasi pampas grass dan bunga liar segar. Sempurna untuk pernikahan outdoor.',
            ],
            [
                'name' => 'Flower Wall Blush Rose',
                'category' => 'minimalist',
                'price' => 6500000,
                'discount' => 5800000,
                'stock' => 20,
                'image' => 'product-4.png',
                'description' => 'Dinding bunga mawar blush pink yang memukau. Menjadi spot foto favorit tamu undangan.',
            ],
            [
                'name' => 'Pergola Taman Inggris',
                'category' => 'garden',
                'price' => 12000000,
                'discount' => null,
                'stock' => 6,
                'image' => 'product-5.png',
                'description' => 'Pergola bergaya taman Inggris dengan ivy dan mawar garden. Menciptakan suasana romantis di outdoor venue.',
            ],
            [
                'name' => 'Pelaminan Emas Royal',
                'category' => 'royal',
                'price' => 35000000,
                'discount' => 32000000,
                'stock' => 3,
                'image' => 'product-6.png',
                'description' => 'Pelaminan mewah berlapis emas dengan ornamen kerajaan. Untuk pernikahan yang benar-benar berkesan.',
            ],
            [
                'name' => 'Macrame Bohemian Backdrop',
                'category' => 'rustic',
                'price' => 4500000,
                'discount' => null,
                'stock' => 12,
                'image' => 'product-7.png',
                'description' => 'Backdrop macrame handmade dengan sentuhan bohemian. Unik, artistik, dan penuh karakter.',
            ],
            [
                'name' => 'Neon Sign Custom Wedding',
                'category' => 'modern',
                'price' => 3500000,
                'discount' => 3000000,
                'stock' => 25,
                'image' => 'product-8.png',
                'description' => 'Neon sign custom dengan nama pasangan atau quote favorit. Menjadi dekorasi sekaligus kenang-kenangan.',
            ],
            [
                'name' => 'Candelabra Set Mewah',
                'category' => 'royal',
                'price' => 9000000,
                'discount' => null,
                'stock' => 8,
                'image' => 'product-9.png',
                'description' => 'Set candelabra emas mewah untuk dekorasi meja dan aisle. Memberikan kesan elegan dan dramatis.',
            ],
            [
                'name' => 'Greenery Wall Skandinavia',
                'category' => 'minimalist',
                'price' => 5500000,
                'discount' => 5000000,
                'stock' => 18,
                'image' => 'product-10.png',
                'description' => 'Dinding hijau segar bergaya Skandinavia dengan tanaman pilihan. Bersih, natural, dan timeless.',
            ],
        ];

        foreach ($products as $data) {
            $slug = Str::slug($data['name']);
            $product = Product::updateOrCreate(
                ['slug' => $slug],
                [
                    'category_id' => $cats[$data['category']]->id,
                    'name' => $data['name'],
                    'description' => $data['description'],
                    'price' => $data['price'],
                    'discount_price' => $data['discount'],
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

    private function ensureCategories(): array
    {
        $list = [
            ['slug' => 'traditional', 'name' => 'Traditional'],
            ['slug' => 'modern',      'name' => 'Modern'],
            ['slug' => 'rustic',      'name' => 'Rustic'],
            ['slug' => 'minimalist',  'name' => 'Minimalist'],
            ['slug' => 'garden',      'name' => 'Garden'],
            ['slug' => 'royal',       'name' => 'Royal'],
        ];

        $result = [];
        foreach ($list as $c) {
            $result[$c['slug']] = Category::firstOrCreate(['slug' => $c['slug']], ['name' => $c['name']]);
        }

        return $result;
    }
}
