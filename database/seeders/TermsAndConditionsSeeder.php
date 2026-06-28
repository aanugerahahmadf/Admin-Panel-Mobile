<?php

namespace Database\Seeders;

use App\Models\PrivacyPolicy;
use App\Models\TermsOfService;
use Illuminate\Database\Seeder;

class TermsAndConditionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('--- Seeding Terms & Privacy ---');

        // 1. Terms of Service
        TermsOfService::updateOrCreate(
            ['id' => 1],
            [
                'title' => __('Syarat & Ketentuan'),
                'content' => [
                    ['heading' => __('PENDAHULUAN'), 'body' => __('Selamat datang di platform Dekorasi Bunga Pernikahan. Sebelum menggunakan Situs ini atau membuat Akun, harap baca Syarat Layanan berikut dengan cermat untuk memahami hak dan kewajiban hukum Anda sehubungan dengan manajemen dekorasi kami.'), 'is_italic' => false],
                    ['heading' => __('AKUN DAN KEAMANAN'), 'body' => __('Dekorasi Bunga Pernikahan berhak menolak akses ke Situs atau Layanan demi melindungi integritas jadwal layanan kami. Anda bertanggung jawab menjaga kerahasiaan kata sandi dan aktivitas akun. Setiap tindakan dalam akun dianggap sebagai persetujuan Anda.'), 'is_italic' => true],
                    ['heading' => __('LAYANAN DAN TRANSAKSI'), 'body' => __('Pemesanan product atau paket dianggap permanen setelah validasi Down Payment. Dashboard berfungsi sebagai bukti digital transaksional yang sah. Amandemen rincian pesanan hanya diizinkan melalui konfirmasi sistem selambat-lambatnya 30 hari sebelum hari acara.'), 'is_italic' => false],
                    ['heading' => __('PEMBATALAN & REFUND'), 'body' => __('DP bersifat non-refundable karena penjadwalan tim eksklusif. Untuk Force Majeure (Bencana alam/pandemi), opsi penjadwalan ulang akan ditawarkan berdasarkan ketersediaan kalender internal kami dengan menjunjung tinggi asas kekeluargaan.'), 'is_italic' => true],
                    ['heading' => __('HAK CIPTA & PORTOFOLIO'), 'body' => __('Dokumentasi dekorasi bunga adalah hak intelektual Dekorasi Bunga Pernikahan dan dapat digunakan sebagai portofolio resmi. Penggunaan aset digital kami secara komersial tanpa izin tertulis dilarang keras secara hukum.'), 'is_italic' => false],
                ],
            ]
        );
        $this->command->line('  <info>✓</info> Syarat & Ketentuan created');

        // 2. Privacy Policy
        PrivacyPolicy::updateOrCreate(
            ['id' => 1],
            [
                'title' => __('Kebijakan Privasi'),
                'content' => [
                    ['heading' => __('KOMITMEN PRIVASI'), 'body' => __('Dekorasi Bunga Pernikahan menangani tanggung jawab perlindungan data pribadi sesuai dengan UU Pelindungan Data Pribadi (UU PDP) dengan sangat serius. Kami berkomitmen penuh untuk melindungi kerahasiaan seluruh data dekorasi Anda.'), 'is_italic' => false],
                    ['heading' => __('PENGUMPULAN DATA'), 'body' => __('Kami mengumpulkan data pribadi riil seperti nama lengkap, alamat email, lokasi acara, dan riwayat transaksi. Data otentikasi cepat melalui Google Login hanya digunakan untuk pembuatan identitas digital unik pada portal dekorasi kami.'), 'is_italic' => true],
                    ['heading' => __('PENGGUNAAN INFORMASI'), 'body' => __('Kami menggunakan informasi Anda semata-mata untuk memproses pesanan dekorasi bunga, koordinasi internal, notifikasi jadwal, dan audit perlindungan hak hukum. Seluruh data koordinasi internal tetap berada di bawah pengawasan audit internal kami.'), 'is_italic' => false],
                    ['heading' => __('KEAMANAN SISTEM'), 'body' => __('Platform kami menggunakan enkripsi SSL tingkat tinggi untuk seluruh transmisi data. Keamanan sesi login bersifat temporer guna menjamin perlindungan privasi real-time saat Anda mengakses dashboard Dekorasi Bunga Pernikahan.'), 'is_italic' => false],
                ],
            ]
        );
        $this->command->line('  <info>✓</info> Kebijakan Privasi created');

        $this->command->info('--- Terms & Privacy Seeding Complete ---');
    }
}
