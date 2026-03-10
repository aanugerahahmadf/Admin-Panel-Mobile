<h1 align="center">
  💄 Devi Make Up - Wedding Organizer Admin Panel Mobile
</h1>

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-12.x-red?style=for-the-badge&logo=laravel" />
  <img src="https://img.shields.io/badge/Filament-3.x-orange?style=for-the-badge" />
  <img src="https://img.shields.io/badge/NativePHP-Mobile-blue?style=for-the-badge" />
  <img src="https://img.shields.io/badge/PHP-8.5-purple?style=for-the-badge&logo=php" />
  <img src="https://img.shields.io/badge/Platform-Android%20%7C%20Web-green?style=for-the-badge" />
</p>

<p align="center">
  Panel Admin berbasis <strong>Filament v3</strong> terintegrasi penuh dengan <strong>NativePHP Mobile</strong> untuk deployment sebagai aplikasi Android native.
  Dibangun untuk manajemen bisnis Wedding Organizer "Devi Make Up" secara menyeluruh.
</p>

---

## ✨ Fitur Utama

### 🏢 Manajemen Bisnis
| Fitur | Deskripsi |
|---|---|
| 📦 **Paket Rias** | CRUD lengkap paket layanan dengan harga, tema, kapasitas & kategori |
| 🌸 **Profil Studio** | Kelola info WO, galeri, lokasi GPS, dan rating |
| 🛒 **Pesanan & Pembayaran** | Pantau seluruh transaksi & status pembayaran real-time |
| 📝 **Artikel Blog** | Kelola konten blog dengan editor rich-text |
| 🎟️ **Voucher & Topup** | Manajemen voucher diskon dan topup saldo pengguna |
| ⭐ **Ulasan** | Pantau review pelanggan dari semua paket |
| 👥 **Manajemen User** | Kelola akun pengguna dan hak akses |
| 💰 **Penarikan Dana** | Kelola permintaan withdrawal |

### 📸 Upload Media (Gambar & Video)
| Model | Koleksi | Maks Size | Jumlah |
|---|---|---|---|
| **Paket Rias** | `package` (foto), `videos` | 100GB/video | Foto: 1, Video: 5 |
| **Profil Studio** | `logo`, `gallery`, `videos` | 100GB/video | Gallery: banyak, Video: 3 |
| **Artikel Blog** | `article-images`, `videos` | 100GB/video | Foto: 1, Video: 1 |

### 📱 Integrasi NativePHP Mobile
- **Android Native App** — Dijalankan langsung sebagai APK di Android
- **Database Proxy** — Koneksi MySQL via HTTP proxy untuk mobile
- **Smart Host Detection** — Auto-detect IP endpoint (emulator/device/LAN)
- **System API** — `System::openAppSettings()` untuk buka pengaturan perangkat
- **Environment Shim** — Fallback `nativephp_call` untuk development di desktop

### 🔔 Notifikasi
- **Database Notifications** — Notifikasi persisten tersimpan di database
- **Swipeable Notifications** — Geser notifikasi kiri/kanan untuk dismiss (Touch & Mouse)
- **Polling efisien** — `databaseNotificationsPolling('120s')` untuk hemat resource mobile

### 🔐 Keamanan & Akses
- **SuperAdmin Middleware** — Pembatasan akses khusus role `super_admin`
- **OTP Authentication** — Reset password & verifikasi email via OTP
- **CSRF Protection** — `VerifyCsrfToken` middleware aktif
- **Persistent Middleware** — `isPersistent: true` untuk stabilitas SPA

---

## 🛠️ Tech Stack

| Layer | Teknologi |
|---|---|
| **Backend** | Laravel 12.x, PHP 8.5 |
| **Admin Panel** | Filament v3.x |
| **Mobile** | NativePHP Mobile v3.x |
| **Database** | MySQL (via Laragon) |
| **Auth** | Laravel Sanctum + Spatie Permission |
| **Media** | Spatie MediaLibrary v11 |
| **Realtime** | Laravel Reverb (WebSocket) |
| **Storage** | Local disk (public) |
| **Frontend** | Livewire 3, Alpine.js, Vite |
| **Email** | SMTP Gmail |
| **AI/CBIR** | External API (Python Flask) |

---

## 📋 Prasyarat

Sebelum memulai, pastikan lo punya:

- [x] **PHP 8.2+** (disarankan 8.5)
- [x] **Composer 2.x**
- [x] **Node.js 20+** & npm
- [x] **MySQL 8.0+** (via Laragon/XAMPP/Docker)
- [x] **Laragon** (Windows) atau MySQL native
- [x] **Android Studio** + emulator (untuk build mobile)
- [x] **Java JDK 17+** (untuk Gradle build)

---

## 🚀 Instalasi

### 1. Clone Repository

```bash
git clone https://github.com/aanugerahahmadf/Admin-Panel-Mobile.git
cd Admin-Panel-Mobile
```

### 2. Install Dependencies

```bash
# PHP dependencies
composer install

# Node dependencies
npm install
```

### 3. Setup Environment

```bash
# Copy env template
cp .env.example .env

# Generate app key
php artisan key:generate
```

Edit file `.env` dengan konfigurasi lo:

```env
APP_NAME="Wedding Organizer"
APP_URL=http://127.0.0.1:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=admin_panel_cbir
DB_USERNAME=root
DB_PASSWORD=

# NativePHP Config
NATIVEPHP_APP_ID=com.yourcompany.yourapp
NATIVEPHP_APP_VERSION=1.0.0
NATIVEPHP_APP_VERSION_CODE=1
NATIVEPHP_RUNNING=false

# Server port (untuk mobile proxy)
NATIVE_SERVER_PORT=8000

# Email
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_USERNAME=your@gmail.com
MAIL_PASSWORD=your_app_password
```

### 4. Setup Database

```bash
# Buat database di MySQL:
# CREATE DATABASE admin_panel_cbir;

# Jalankan migrasi & seeder
php artisan migrate --seed
```

### 5. Setup Storage

```bash
# Link public storage
php artisan storage:link
```

### 6. Build Frontend Assets

```bash
npm run build
# atau, untuk development:
npm run dev
```

### 7. Jalankan Server

```bash
php artisan serve --host=0.0.0.0 --port=8000
```

Akses admin panel di: **http://127.0.0.1:8000/admin**

---

## 📱 Build & Jalankan di Android

### Prasyarat Mobile
- Android Studio terinstal
- Android SDK & emulator dikonfigurasi
- `ANDROID_HOME` environment variable sudah di-set

### 1. Install NativePHP

```bash
php artisan native:install
```

### 2. Jalankan di Emulator

```bash
php artisan native:run android
```

> Build log tersedia di: `nativephp/android-build.log`

### 3. Build APK Release

```bash
php artisan native:run android --release
```

---

## 🗂️ Struktur Proyek

```
app/
├── Filament/
│   ├── Pages/
│   │   └── Auth/                    # Login, OTP Reset, OTP Verifikasi
│   ├── Resources/                   # CRUD Resources
│   │   ├── ArticleResource.php
│   │   ├── BannerResource.php
│   │   ├── OrderResource.php
│   │   ├── PackageResource.php      # + Video Upload
│   │   ├── PaymentResource.php
│   │   ├── WeddingOrganizerResource.php  # + Video Upload
│   │   └── ...
│   └── Widgets/
│       ├── StatsOverview.php        # Statistik utama
│       ├── RevenueChart.php         # Grafik pendapatan
│       ├── OrdersChart.php          # Grafik pesanan
│       └── RecentOrders.php         # Pesanan terbaru
├── Http/
│   └── Middleware/
│       └── SuperAdmin.php           # Role-based access control
├── Livewire/
│   ├── PersonalInfoComponent.php    # Profile: Info Pribadi
│   ├── UsernameComponent.php        # Profile: Username
│   └── MobileSettingsComponent.php  # Profile: Setting Mobile
├── Models/
│   ├── Package.php                  # HasMedia (image + video)
│   ├── WeddingOrganizer.php         # HasMedia (logo + gallery + video)
│   ├── Article.php                  # HasMedia (image + video)
│   └── ...
└── Providers/
    ├── AppServiceProvider.php       # nativephp_call shim
    ├── NativeServiceProvider.php    # Mobile config & DB proxy
    └── Filament/
        └── AdminPanelProvider.php   # Panel config & render hooks

public/
├── js/sanzgrapher/swippable-notification/
│   └── init-swippable.js           # Custom swipeable notifications JS
└── css/sanzgrapher/swippable-notification/
    └── swippable-notification-styles.css  # Swipeable notifications CSS

resources/views/
└── welcome.blade.php               # Halaman landing mobile app
```

---

## ⚙️ Konfigurasi Penting

### NativePHP Mobile Database Proxy

Untuk koneksi database di Android, aplikasi menggunakan HTTP proxy:

```env
NATIVE_DB_PROXY=true
NATIVE_DB_PROXY_SECRET=nativephp-db-proxy-secret-2024
NATIVE_SERVER_PORT=8000
```

### Swipeable Notifications

Notifikasi dapat di-dismiss dengan swipe (tanpa plugin eksternal):
- **Threshold:** 80px untuk trigger dismiss
- **Support:** Touch (mobile) + Mouse drag (desktop)
- **Target:** Toast notifications + Database notifications

### Video Upload Limits

Filament upload limit sudah di-set ke **100GB**. Pastikan PHP juga support:

```ini
; php.ini (Laragon: D:\laragon\bin\php\phpX.X.X\php.ini)
upload_max_filesize = 102400M
post_max_size = 102400M
max_execution_time = 0
max_input_time = -1
```

---

## 🔑 Default Credentials

Setelah seeder dijalankan:

| Role | Email | Password |
|---|---|---|
| Super Admin | `admin@devimakeup.com` | `password` |

> ⚠️ **Segera ubah password setelah login pertama!**

---

## 📡 API Endpoints

| Method | Endpoint | Deskripsi |
|---|---|---|
| `POST` | `/api/db-proxy` | Database proxy untuk mobile |
| `GET` | `/api/settings` | Konfigurasi app mobile |
| `GET` | `/api/packages` | Daftar paket |
| `GET` | `/api/wedding-organizers` | Data studio |
| `GET` | `/api/articles` | Daftar artikel |

---

## 🧪 Testing

```bash
# Jalankan semua test
php artisan test

# Test spesifik
php artisan test --filter=ExampleTest
```

---

## 🤝 Kontribusi

1. Fork repositori ini
2. Buat branch fitur baru: `git checkout -b feat/nama-fitur`
3. Commit perubahan: `git commit -m 'feat: tambah fitur X'`
4. Push ke branch: `git push origin feat/nama-fitur`
5. Buat Pull Request

---

## 📄 Lisensi

Proyek ini dilisensikan di bawah [MIT License](LICENSE).

---

## 👨‍💻 Developer

<p align="center">
  <strong>Ahmad Anugerah</strong><br/>
  <a href="mailto:aanugerahahmad27@gmail.com">aanugerahahmad27@gmail.com</a><br/>
  <a href="https://github.com/aanugerahahmadf">github.com/aanugerahahmadf</a>
</p>

---

<p align="center">
  Made with ❤️ for <strong>Devi Make Up Wedding Organizer</strong>
</p>
