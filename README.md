# 💍 Weeding Organizer - AI-Powered Wedding Management Platform

<p align="center">
  <img src="public/favicon.ico" width="400" high="300" alt="Weeding Organizer Logo">
</p>

<p align="center">
  <a href="https://laravel.com"><img src="https://img.shields.io/badge/Laravel-12-FF2D20?style=for-the-badge&logo=laravel" alt="Laravel"></a>
  <a href="https://nativephp.com"><img src="https://img.shields.io/badge/NativePHP-Mobile-4F46E5?style=for-the-badge&logo=php" alt="NativePHP"></a>
  <a href="https://filamentphp.com"><img src="https://img.shields.io/badge/Filament-3.x-FDBE11?style=for-the-badge&logo=filament" alt="Filament"></a>
  <a href="https://pestphp.com"><img src="https://img.shields.io/badge/Pest-Test-01BDC7?style=for-the-badge&logo=pest" alt="Pest"></a>
  <a href="https://www.php.net"><img src="https://img.shields.io/badge/PHP-8.5-777BB4?style=for-the-badge&logo=php" alt="PHP 8.5"></a>
</p>

---

## 🚀 Visi & Misi

**Weeding Organizer** adalah platform digital terintegrasi yang dirancang khusus untuk mempermudah calon pengantin merencanakan hari bahagia mereka. Dengan dukungan teknologi **AI (CBIR)** untuk pencarian gaya visual dan aplikasi mobile asli yang responsif, kami menghadirkan pengalaman Wedding Planning yang modern, aman, dan efisien.

---

## ✨ Fitur Lengkap Untuk User Pengantin

Aplikasi mobile berbasis **NativePHP** ini hadir sebagai asisten pribadi yang cerdas untuk memandu setiap tahap perencanaan pernikahan:

- **🤵 Personal Wedding Planner**: Kelola **Wedding Date** dan detail acara pernikahan Anda secara personal.
- **💰 Smart Budgeting Control**: Atur dan pantau **Budget Pernikahan** agar tetap sesuai dengan perencanaan keuangan.
- **🔍 AI Style Discovery (CBIR)**: Temukan gaya dekorasi, makeup, atau venue impian hanya dengan mengunggah foto referensi melalui teknologi AI.
- **📍 Location-Based Service**: Temukan detail lokasi acara dan integrasi alamat yang memudahkan koordinasi lapangan.
- **💳 Integrated Wallet & Payments**: Sistem **Top-up Saldo** untuk kemudahan pembayaran DP atau pelunasan layanan secara instan dan aman.
- **💬 Direct Real-time Chat**: Konsultasi langsung dengan tim kami melalui fitur pesan instan di dalam aplikasi.
- **🛍️ Katalog Layanan Lengkap**: Pilih berbagai paket (Makeup, Venue, Catering, Dekorasi) dengan sistem **Wishlist & Voucher** promo eksklusif.
- **⭐ Trusted Reviews**: Lihat testimoni dan berikan feedback untuk menjamin kualitas layanan kami.

---

## 🛠️ Fitur Admin Panel Management

Menggunakan **Filament v3**, memberikan kontrol mutlak bagi tim internal untuk mengelola operasional:

- **📊 Business Analytics**: Pantau total pesanan, grafik pendapatan terbaru, dan statistik performa bulanan secara intuitif.
- **📦 Service Package Manager**: Kelola seluruh paket layanan (galeri foto, spesifikasi, dan harga) dengan mudah.
- **🧾 Lifecycle Order Processing**: Kelola seluruh tahap pesanan mulai dari booking awal hingga hari pelaksanaan acara.
- **🏦 Ledger & Finance Control**: Verifikasi transaksi **Top-up** saldo pengguna dan kelola laporan keuangan secara internal.
- **👥 Access Control**: Pengaturan hak akses tim khusus untuk manajemen data dan operasional aplikasi.
- **📰 CRM & Content Manager**: Publikasikan tips pernikahan melalui artikel dan kelola banner promo untuk memanjakan pengguna.

---

## 🏗️ Elite Tech Stack

- **Framework**: [Laravel] https://laravel.com
- **Mobile Runtime**: [NativePHP - Android & iOS] https://github.com/nativephp/mobile
- **Dashboard Interface**: [Filament v3] https://filamentphp.com
- **AI Core Engine**: Flask / Python with Content-Based Image Retrieval (CBIR) Algorithm https://www.python.org
- **Messaging Engine**: Laravel Reverb (Real-time Communications) https://reverb.laravel.com
- **Testing Standard**: [Pest PHP] https://pestphp.com

---

## 📦 Instalasi & Setup Cepat

```bash
# Clone & Install
git clone https://github.com/aanugerahahmadf/Admin-Panel-Mobile.git
cd Admin-Panel-Mobile
composer install && npm install && npm run build

# Setup
cp .env.example .env && php artisan key:generate
php artisan migrate --seed
```

### Jalankan Mode Mobile (NativePHP)
```bash
# Ready your app to go native
php artisan native:install
 
# Run your app on a mobile device
php artisan native:run
```

---

## 🖥️ Multi-Platform Support

Aplikasi ini berjalan dalam **tiga mode platform** yang masing-masing dipicu oleh satu perintah Artisan. Platform mode ditentukan saat bootstrap — setiap mode memuat environment file tersendiri, bundle aset Vite tersendiri, dan route set tersendiri.

| Mode | Perintah | Target | RuntimePlatform Cases |
|------|----------|--------|-----------------------|
| **Web** | `php artisan serve` | Browser (Windows, macOS, Android, iOS) | `WebsiteWindows`, `WebsiteMacOS`, `WebsiteAndroid`, `WebsiteIos` |
| **Mobile Native** | `php artisan native:run` | Android & iOS via NativePHP Mobile | `MobileAppAndroid`, `MobileAppIos` |
| **Desktop App** | `php artisan native:serve` | Windows & macOS via NativePHP Electron | `DesktopAppWindows`, `DesktopAppMacOS` |

### Quick Start Per Platform

**Web** — tidak memerlukan dependensi tambahan:

```bash
# 1. (Opsional) buat file environment khusus web
cp .env.web.example .env.web

# 2. Build aset untuk web
npx vite build -- --mode web

# 3. Jalankan server
php artisan serve
# → http://localhost:8000
```

**Mobile (Android / iOS)** — memerlukan paket `nativephp/mobile`:

```bash
# 1. Install dependensi mobile
composer require nativephp/mobile
php artisan native:install

# 2. (Opsional) buat file environment khusus mobile
cp .env.mobile.example .env.mobile

# 3. Build aset untuk mobile
npx vite build -- --mode mobile

# 4. Jalankan di perangkat / emulator
php artisan native:run
```

**Desktop (Windows / macOS)** — memerlukan paket `nativephp/electron` dan `nativephp/laravel`:

```bash
# 1. Install dependensi desktop
composer require nativephp/electron nativephp/laravel
php artisan native:install

# 2. (Opsional) buat file environment khusus desktop
cp .env.desktop.example .env.desktop

# 3. Build aset untuk desktop
npx vite build -- --mode desktop

# 4. Jalankan sebagai aplikasi Electron
php artisan native:serve
```

> Tidak yakin mana yang harus dipakai? Lihat **[docs/command-guide.md](docs/command-guide.md)** untuk decision tree lengkap dan panduan troubleshooting.

### Platform Feature Overview

Setiap platform mode mengaktifkan fitur yang berbeda. Tabel berikut merangkum ketersediaan fitur utama:

| Fitur | Web | Mobile | Desktop |
|-------|:---:|:------:|:-------:|
| Native camera (CBIR) | ❌ | ✅ | ✅ |
| WebRTC camera | ✅ | ❌ | ❌ |
| File system access | ❌ | ✅ | ✅ |
| Push notifications | ❌ | ✅ | ❌ |
| Desktop notifications | ❌ | ❌ | ✅ |
| Auto-updates | ❌ | ❌ | ✅ |
| App badge | ❌ | ✅ | ❌ |

Periksa ketersediaan fitur di kode PHP menggunakan helper yang sudah disediakan:

```php
// Cek fitur untuk platform aktif saat ini
if (platform_feature('camera')) {
    // gunakan NativePHP Camera API
}

// Cek mode platform
if (is_mobile_mode()) { /* ... */ }
if (is_desktop_mode()) { /* ... */ }
if (is_web_mode()) { /* ... */ }
```

Untuk daftar lengkap fitur beserta cara menggunakannya di kode, lihat **[docs/platform-features.md](docs/platform-features.md)**.

### Development Workflow

**Menjalankan satu platform:**

```bash
# Cek status platform yang aktif
php artisan platform:status

# Bersihkan cache saat ganti mode
php artisan platform:clear
```

**Menjalankan beberapa platform sekaligus** — gunakan terminal terpisah dengan port berbeda:

```bash
# Terminal 1 — Web di port 8000
php artisan serve --port=8000

# Terminal 2 — Mobile (port dikontrol NativePHP)
php artisan native:run

# Terminal 3 — Desktop (port dikontrol NativePHP Electron)
php artisan native:serve
```

Setiap mode memuat file environment-nya sendiri secara otomatis:

| Mode | File Environment | Build Directory |
|------|-----------------|-----------------|
| Web | `.env.web` | `public/build/web` |
| Mobile | `.env.mobile` | `public/build/mobile` |
| Desktop | `.env.desktop` | `public/build/desktop` |

File environment platform bersifat **opsional** — jika tidak ada, aplikasi menggunakan nilai dari `.env` utama. Nilai di file platform selalu menimpa nilai di `.env` utama bila ada konflik.

### Dokumentasi Lengkap

| Topik | Dokumen |
|-------|---------|
| Arsitektur & komponen | [docs/platform-support.md](docs/platform-support.md) |
| Panduan perintah & decision tree | [docs/command-guide.md](docs/command-guide.md) |
| Konfigurasi environment | [docs/environment-configuration.md](docs/environment-configuration.md) |
| Kompilasi aset Vite | [docs/asset-compilation.md](docs/asset-compilation.md) |
| Feature matrix lengkap | [docs/platform-features.md](docs/platform-features.md) |

---

## 🔑 Akun Akses Default

Gunakan kredensial berikut untuk masuk ke dashboard admin:
- **Email**: `devimakeup.wo@gmail.com`
- **Password**: `@Admin123`

---

## 🧪 Automated Testing

Menjamin keandalan fitur finansial dan pemrosesan data secara otomatis:
```bash
php artisan test
```

---

<p align="center">
  <b>Weeding Organizer</b> - Mewujudkan Pernikahan Impian Anda Menjadi Nyata.
</p>

<p align="center">
  Dibuat dengan oleh <b>Anugerah Ahmad Fachrurochim</b>
</p>
