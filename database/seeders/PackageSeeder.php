<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Package;
use App\Traits\TranslatesContent;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PackageSeeder extends Seeder
{
    use TranslatesContent;

    public function run(): void
    {
        $this->command->info('--- Seeding Packages ---');

        $cats = Category::where('type', 'package')->get()->keyBy('slug');

        $packages = [
            [
                'name' => 'Luxurious Traditional Gebyok Package',
                'category' => 'traditional',
                'price' => 20000000,
                'discount_pct' => 10,
                'featured' => true,
                'stock' => 5,
                'color' => '#8B4513',
                'image' => 'package-11.png',
                'features' => ['Gebyok ukir premium', 'Bunga segar pilihan', 'Kain batik eksklusif', 'Lighting tradisional', 'Tim dekorasi profesional'],
                'description' => 'Paket dekorasi pernikahan tradisional mewah dengan gebyok ukir kayu jati pilihan, dihiasi rangkaian bunga segar dan kain batik eksklusif.',
                'name_id' => 'Paket Gebyok Tradisional Mewah',
                'name_en' => 'Luxurious Traditional Gebyok Package',
                'desc_id' => 'Paket dekorasi pernikahan tradisional mewah dengan gebyok ukir kayu jati pilihan, dihiasi rangkaian bunga segar dan kain batik eksklusif.',
                'desc_en' => 'Luxurious traditional wedding decoration package with premium carved teak wood gebyok, adorned with fresh flower arrangements and exclusive batik fabrics.',
            ],
            [
                'name' => 'Modern Crystal Stage Package',
                'category' => 'modern',
                'price' => 25000000,
                'discount_pct' => 8,
                'featured' => true,
                'stock' => 5,
                'color' => '#4A90D9',
                'image' => 'package-2.png',
                'features' => ['Chandelier kristal', 'Bunga anggrek putih', 'Backdrop LED', 'Karpet merah premium', 'Meja penerima tamu'],
                'description' => 'Paket dekorasi modern elegan dengan chandelier kristal berkilau dan rangkaian anggrek putih. Backdrop LED interaktif menciptakan suasana mewah.',
                'name_id' => 'Paket Panggung Kristal Modern',
                'name_en' => 'Modern Crystal Stage Package',
                'desc_id' => 'Paket dekorasi modern elegan dengan chandelier kristal berkilau dan rangkaian anggrek putih. Backdrop LED interaktif menciptakan suasana mewah.',
                'desc_en' => 'Elegant modern decoration package with sparkling crystal chandeliers and white orchid arrangements. Interactive LED backdrop creates a luxurious atmosphere.',
            ],
            [
                'name' => 'Rustic Sunset Garden Package',
                'category' => 'rustic',
                'price' => 17000000,
                'discount_pct' => 9,
                'featured' => false,
                'stock' => 5,
                'color' => '#D2691E',
                'image' => 'package-3.png',
                'features' => ['Arch kayu alami', 'Pampas grass', 'Fairy lights', 'Bunga liar segar', 'Dekorasi bambu'],
                'description' => 'Paket dekorasi rustic hangat dengan arch kayu alami, pampas grass, dan fairy lights yang romantis. Nuansa kebun senja yang intim.',
                'name_id' => 'Paket Taman Senja Rustic',
                'name_en' => 'Rustic Sunset Garden Package',
                'desc_id' => 'Paket dekorasi rustic hangat dengan arch kayu alami, pampas grass, dan fairy lights yang romantis. Nuansa kebun senja yang intim.',
                'desc_en' => 'Warm rustic decoration package with natural wooden arch, pampas grass, and romantic fairy lights. Intimate sunset garden ambiance.',
            ],
            [
                'name' => 'Minimalist Pastel Arch Package',
                'category' => 'minimalist',
                'price' => 13000000,
                'discount_pct' => 8,
                'featured' => false,
                'stock' => 5,
                'color' => '#F4C2C2',
                'image' => 'package-4.png',
                'features' => ['Arch geometris', 'Bunga blush rose', 'Eucalyptus garland', 'Backdrop polos premium', 'Dekorasi meja simpel'],
                'description' => 'Paket dekorasi minimalis bersih dengan arch geometris, blush rose, dan eucalyptus. Keindahan dalam kesederhanaan yang tetap terasa mewah.',
                'name_id' => 'Paket Arch Pastel Minimalis',
                'name_en' => 'Minimalist Pastel Arch Package',
                'desc_id' => 'Paket dekorasi minimalis bersih dengan arch geometris, blush rose, dan eucalyptus. Keindahan dalam kesederhanaan yang tetap terasa mewah.',
                'desc_en' => 'Clean minimalist decoration package with geometric arch, blush roses, and eucalyptus. Beauty in simplicity that still feels luxurious.',
            ],
            [
                'name' => 'English Garden Romance Package',
                'category' => 'garden',
                'price' => 21000000,
                'discount_pct' => 10,
                'featured' => true,
                'stock' => 5,
                'color' => '#228B22',
                'image' => 'package-5.png',
                'features' => ['Floral arch besar', 'Bunga mawar garden', 'Ivy & greenery', 'Pergola dekorasi', 'Aisle bunga segar'],
                'description' => 'Paket taman bunga Inggris yang romantis dengan floral arch besar, mawar garden, dan pergola yang dihiasi ivy.',
                'name_id' => 'Paket Romantis Taman Inggris',
                'name_en' => 'English Garden Romance Package',
                'desc_id' => 'Paket taman bunga Inggris yang romantis dengan floral arch besar, mawar garden, dan pergola yang dihiasi ivy.',
                'desc_en' => 'Romantic English garden package with a large floral arch, garden roses, and an ivy-adorned pergola.',
            ],
            [
                'name' => 'Grand Royal Ballroom Package',
                'category' => 'royal',
                'price' => 45000000,
                'discount_pct' => 7,
                'featured' => true,
                'stock' => 3,
                'color' => '#FFD700',
                'image' => 'package-6.png',
                'features' => ['Pelaminan emas mewah', 'Chandelier grand', 'Bunga premium import', 'Red carpet VIP', 'Dekorasi ceiling penuh', 'Tim 20 orang'],
                'description' => 'Paket kemewahan ballroom kerajaan dengan pelaminan emas, chandelier grand, dan bunga premium import.',
                'name_id' => 'Paket Ballroom Kerajaan Grand',
                'name_en' => 'Grand Royal Ballroom Package',
                'desc_id' => 'Paket kemewahan ballroom kerajaan dengan pelaminan emas, chandelier grand, dan bunga premium import.',
                'desc_en' => 'Royal ballroom luxury package with golden throne, grand chandeliers, and imported premium flowers.',
            ],
            [
                'name' => 'Javanese Royal Pendopo Package',
                'category' => 'traditional',
                'price' => 23000000,
                'discount_pct' => 9,
                'featured' => false,
                'stock' => 5,
                'color' => '#6B3A2A',
                'image' => 'package-7.png',
                'features' => ['Pendopo joglo replika', 'Ornamen emas', 'Bunga melati segar', 'Backdrop batik tulis', 'Pelaminan ukir'],
                'description' => 'Nuansa pendopo joglo kerajaan Jawa yang autentik dengan ornamen emas and rangkaian melati segar.',
                'name_id' => 'Paket Pendopo Kerajaan Jawa',
                'name_en' => 'Javanese Royal Pendopo Package',
                'desc_id' => 'Nuansa pendopo joglo kerajaan Jawa yang autentik dengan ornamen emas and rangkaian melati segar.',
                'desc_en' => 'Authentic Javanese royal pendopo joglo ambiance with golden ornaments and fresh jasmine arrangements.',
            ],
            [
                'name' => 'Contemporary White Luxe Package',
                'category' => 'modern',
                'price' => 27000000,
                'discount_pct' => 7,
                'featured' => true,
                'stock' => 5,
                'color' => '#E8E8E8',
                'image' => 'package-8.png',
                'features' => ['All-white concept', 'Bunga peony & mawar', 'Neon sign custom', 'Flower wall backdrop', 'Aisle dekorasi'],
                'description' => 'Konsep serba putih yang bersih dan mewah dengan bunga peony dan mawar pilihan. Neon sign custom dan flower wall backdrop.',
                'name_id' => 'Paket Mewah Putih Kontemporer',
                'name_en' => 'Contemporary White Luxe Package',
                'desc_id' => 'Konsep serba putih yang bersih dan mewah dengan bunga peony dan mawar pilihan. Neon sign custom dan flower wall backdrop.',
                'desc_en' => 'Clean and luxurious all-white concept with peonies and premium roses. Custom neon sign and flower wall backdrop.',
            ],
            [
                'name' => 'Bohemian Wildflower Dream Package',
                'category' => 'rustic',
                'price' => 18500000,
                'discount_pct' => 8,
                'featured' => false,
                'stock' => 5,
                'color' => '#C4A35A',
                'image' => 'package-9.png',
                'features' => ['Macrame backdrop', 'Bunga liar mix', 'Tipi tent dekorasi', 'Dreamcatcher ornamen', 'Karpet etnik'],
                'description' => 'Gaya bohemian bebas dengan macrame backdrop, bunga liar warna-warni, dan ornamen dreamcatcher.',
                'name_id' => 'Paket Impian Bunga Liar Bohemian',
                'name_en' => 'Bohemian Wildflower Dream Package',
                'desc_id' => 'Gaya bohemian bebas dengan macrame backdrop, bunga liar warna-warni, dan ornamen dreamcatcher.',
                'desc_en' => 'Free-spirited bohemian style with macrame backdrop, colorful wildflowers, and dreamcatcher ornaments.',
            ],
            [
                'name' => 'Versailles Gold Elegance Package',
                'category' => 'royal',
                'price' => 50000000,
                'discount_pct' => 8,
                'featured' => true,
                'stock' => 2,
                'color' => '#B8860B',
                'image' => 'package-10.png',
                'features' => ['Konsep istana Versailles', 'Ornamen emas 24k', 'Bunga mawar merah premium', 'Candelabra set', 'Ceiling draping mewah', 'Lighting show'],
                'description' => 'Terinspirasi kemewahan Istana Versailles dengan ornamen emas, mawar merah premium, dan ceiling draping yang dramatis.',
                'name_id' => 'Paket Keanggunan Emas Versailles',
                'name_en' => 'Versailles Gold Elegance Package',
                'desc_id' => 'Terinspirasi kemewahan Istana Versailles dengan ornamen emas, mawar merah premium, dan ceiling draping yang dramatis.',
                'desc_en' => 'Inspired by the luxury of Versailles Palace with gold ornaments, premium red roses, and dramatic ceiling draping.',
            ],
        ];

        foreach ($packages as $data) {
            $slug = Str::slug($data['name']);
            $discountPrice = (int) round($data['price'] * (100 - $data['discount_pct']) / 100);

            $nameTranslations = $this->translateToAllLocales($data['name_id'], $data['name_en']);
            $descTranslations = $this->translateToAllLocales($data['desc_id'], $data['desc_en']);

            $package = Package::updateOrCreate(
                ['slug' => $slug],
                [
                    'category_id' => $cats[$data['category']]->id,
                    'name' => $data['name'],
                    'description' => $data['description'],
                    'name_translations' => $nameTranslations,
                    'description_translations' => $descTranslations,
                    'price' => $data['price'],
                    'discount_price' => $discountPrice,
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
}
