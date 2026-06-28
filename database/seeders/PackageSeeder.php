<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Package;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PackageSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('--- Seeding Packages ---');

        $cats = $this->ensureCategories();

        $packages = [
            [
                'name' => 'Luxurious Traditional Gebyok Package',
                'category' => 'traditional',
                'price' => 20000000,
                'discount' => null,
                'featured' => true,
                'stock' => 5,
                'color' => '#8B4513',
                'image' => 'package-11.png',
                'features' => ['Gebyok ukir premium', 'Bunga segar pilihan', 'Kain batik eksklusif', 'Lighting tradisional', 'Tim dekorasi profesional'],
                'description' => 'Paket dekorasi pernikahan tradisional mewah dengan gebyok ukir kayu jati pilihan, dihiasi rangkaian bunga segar dan kain batik eksklusif.',
            ],
            [
                'name' => 'Modern Crystal Stage Package',
                'category' => 'modern',
                'price' => 25000000,
                'discount' => 23000000,
                'featured' => true,
                'stock' => 5,
                'color' => '#4A90D9',
                'image' => 'package-2.png',
                'features' => ['Chandelier kristal', 'Bunga anggrek putih', 'Backdrop LED', 'Karpet merah premium', 'Meja penerima tamu'],
                'description' => 'Paket dekorasi modern elegan dengan chandelier kristal berkilau dan rangkaian anggrek putih. Backdrop LED interaktif menciptakan suasana mewah.',
            ],
            [
                'name' => 'Rustic Sunset Garden Package',
                'category' => 'rustic',
                'price' => 17000000,
                'discount' => null,
                'featured' => false,
                'stock' => 5,
                'color' => '#D2691E',
                'image' => 'package-3.png',
                'features' => ['Arch kayu alami', 'Pampas grass', 'Fairy lights', 'Bunga liar segar', 'Dekorasi bambu'],
                'description' => 'Paket dekorasi rustic hangat dengan arch kayu alami, pampas grass, dan fairy lights yang romantis. Nuansa kebun senja yang intim.',
            ],
            [
                'name' => 'Minimalist Pastel Arch Package',
                'category' => 'minimalist',
                'price' => 13000000,
                'discount' => 12000000,
                'featured' => false,
                'stock' => 5,
                'color' => '#F4C2C2',
                'image' => 'package-4.png',
                'features' => ['Arch geometris', 'Bunga blush rose', 'Eucalyptus garland', 'Backdrop polos premium', 'Dekorasi meja simpel'],
                'description' => 'Paket dekorasi minimalis bersih dengan arch geometris, blush rose, dan eucalyptus. Keindahan dalam kesederhanaan yang tetap terasa mewah.',
            ],
            [
                'name' => 'English Garden Romance Package',
                'category' => 'garden',
                'price' => 21000000,
                'discount' => null,
                'featured' => true,
                'stock' => 5,
                'color' => '#228B22',
                'image' => 'package-5.png',
                'features' => ['Floral arch besar', 'Bunga mawar garden', 'Ivy & greenery', 'Pergola dekorasi', 'Aisle bunga segar'],
                'description' => 'Paket taman bunga Inggris yang romantis dengan floral arch besar, mawar garden, dan pergola yang dihiasi ivy.',
            ],
            [
                'name' => 'Grand Royal Ballroom Package',
                'category' => 'royal',
                'price' => 45000000,
                'discount' => 42000000,
                'featured' => true,
                'stock' => 3,
                'color' => '#FFD700',
                'image' => 'package-6.png',
                'features' => ['Pelaminan emas mewah', 'Chandelier grand', 'Bunga premium import', 'Red carpet VIP', 'Dekorasi ceiling penuh', 'Tim 20 orang'],
                'description' => 'Paket kemewahan ballroom kerajaan dengan pelaminan emas, chandelier grand, dan bunga premium import.',
            ],
            [
                'name' => 'Javanese Royal Pendopo Package',
                'category' => 'traditional',
                'price' => 23000000,
                'discount' => 21000000,
                'featured' => false,
                'stock' => 5,
                'color' => '#6B3A2A',
                'image' => 'package-7.png',
                'features' => ['Pendopo joglo replika', 'Ornamen emas', 'Bunga melati segar', 'Backdrop batik tulis', 'Pelaminan ukir'],
                'description' => 'Nuansa pendopo joglo kerajaan Jawa yang autentik dengan ornamen emas and rangkaian melati segar.',
            ],
            [
                'name' => 'Contemporary White Luxe Package',
                'category' => 'modern',
                'price' => 27000000,
                'discount' => 25000000,
                'featured' => true,
                'stock' => 5,
                'color' => '#E8E8E8',
                'image' => 'package-8.png',
                'features' => ['All-white concept', 'Bunga peony & mawar', 'Neon sign custom', 'Flower wall backdrop', 'Aisle dekorasi'],
                'description' => 'Konsep serba putih yang bersih dan mewah dengan bunga peony dan mawar pilihan. Neon sign custom dan flower wall backdrop.',
            ],
            [
                'name' => 'Bohemian Wildflower Dream Package',
                'category' => 'rustic',
                'price' => 18500000,
                'discount' => 17000000,
                'featured' => false,
                'stock' => 5,
                'color' => '#C4A35A',
                'image' => 'package-9.png',
                'features' => ['Macrame backdrop', 'Bunga liar mix', 'Tipi tent dekorasi', 'Dreamcatcher ornamen', 'Karpet etnik'],
                'description' => 'Gaya bohemian bebas dengan macrame backdrop, bunga liar warna-warni, dan ornamen dreamcatcher.',
            ],
            [
                'name' => 'Versailles Gold Elegance Package',
                'category' => 'royal',
                'price' => 50000000,
                'discount' => null,
                'featured' => true,
                'stock' => 2,
                'color' => '#B8860B',
                'image' => 'package-10.png',
                'features' => ['Konsep istana Versailles', 'Ornamen emas 24k', 'Bunga mawar merah premium', 'Candelabra set', 'Ceiling draping mewah', 'Lighting show'],
                'description' => 'Terinspirasi kemewahan Istana Versailles dengan ornamen emas, mawar merah premium, dan ceiling draping yang dramatis.',
            ],
        ];

        foreach ($packages as $data) {
            $slug = Str::slug($data['name']);

            $package = Package::updateOrCreate(
                ['slug' => $slug],
                [
                    'category_id' => $cats[$data['category']]->id,
                    'name' => $data['name'],
                    'description' => $data['description'],
                    'price' => $data['price'],
                    'discount_price' => $data['discount'],
                    'is_featured' => $data['featured'],
                    'features' => $data['features'],
                    'color' => $data['color'],
                    'stock' => $data['stock'],
                ]
            );

            $imagePath = public_path('images/package/'.$data['image']);
            if (file_exists($imagePath)) {
                $package->clearMediaCollection('package_image');
                try {
                    $package->addMedia($imagePath)
                        ->preservingOriginal()
                        ->toMediaCollection('package_image');

                    $this->command->line("  <info>✓</info> {$data['name']} [{$data['image']}]");
                } catch (\Exception $e) {
                    $this->command->error("  ✗ Gagal memuat gambar untuk: {$data['name']}");
                }
            } else {
                $this->command->line("  <info>✓</info> {$data['name']} (Tanpa Gambar)");
            }
        }

        $this->command->info('--- Package Seeding Complete ('.count($packages).' packages) ---');
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
