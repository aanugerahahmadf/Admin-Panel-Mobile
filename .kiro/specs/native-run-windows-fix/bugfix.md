# Bugfix Requirements Document

## Introduction

Perintah `php artisan native:run` gagal saat dijalankan di Windows 10/11 dengan NativePHP Mobile 3.2.2 dan Laravel 12.x. Kegagalan terjadi secara berurutan dalam empat titik: (1) penyalinan source ke temp dir menggunakan robocopy/xcopy tidak menyalin semua file dengan benar sehingga `composer.json` tidak tersedia di temp dir, (2) Composer menggunakan 7-Zip untuk mengekstrak package ke path temp yang sama sehingga file ZIP sementara tidak ditemukan akibat Windows MAX_PATH, (3) Gradle build gagal karena `gradlew.bat` tidak dapat dieksekusi melalui `passthru()` tanpa full path, dan (4) autoloader tidak lengkap akibat efek domino dari kegagalan Composer. Semua bug ini berdampak pada developer yang membangun aplikasi Android di Windows dan menyebabkan build tidak dapat diselesaikan sama sekali.

**Status implementasi saat ini (berdasarkan review kode):**
- Bug 1.6 (gradlew.bat full path) → **SUDAH DIPERBAIKI** di `app/Traits/NativePHP/RunsAndroid.php` menggunakan `Process::run("\"$gradleWrapper\" $gradleTask")` dengan full absolute path.
- Bug 1.1/1.2 (robocopy/xcopy fallback) → **SEBAGIAN DIPERBAIKI** di `app/Traits/NativePHP/PreparesBuild::platformOptimizedCopy()` — robocopy sudah menggunakan backslash yang benar, namun xcopy fallback masih menggunakan pola `\*` yang tidak menyalin file di root level.
- Bug 1.4 (7-Zip path length untuk bundle ZIP) → **SEBAGIAN DIPERBAIKI** — `createZipBundle` lokal menambahkan flag `-ssw` dan menoleransi exit code 1 sebagai non-fatal. Namun masalah path panjang saat Composer mengekstrak package (bukan saat membuat bundle) belum ditangani.
- Bug 1.3/1.5 (composer.json missing, autoloader tidak lengkap) → **BELUM DIPERBAIKI** — masih bergantung pada perbaikan xcopy fallback.

Solusi harus diterapkan tanpa memodifikasi vendor files secara langsung, yaitu melalui override trait lokal (`App\Traits\NativePHP\*`), konfigurasi `.env`, `config/nativephp.php`, atau `composer-patches` jika patch vendor diperlukan.

---

## Bug Analysis

### Current Behavior (Defect)

1.1 WHEN `robocopy` dijalankan dengan flag `/MIR` untuk menyalin source Laravel ke temp dir di Windows THEN sistem gagal dengan exit code >= 8 (fatal error) karena path terlalu panjang, permission issue, atau konflik dengan antivirus

1.2 WHEN robocopy gagal dengan exit code >= 8 dan fallback ke `xcopy` dijalankan THEN sistem tidak menyalin `composer.json` dan file-file root lainnya ke temp dir karena `xcopy` dengan pola `\*` hanya menyalin isi direktori tanpa file di root level (perilaku xcopy yang diketahui: `xcopy "src\*" "dst\"` melewati file di root `src`)

1.3 WHEN `composer install` dijalankan di temp dir yang tidak memiliki `composer.json` (akibat xcopy fallback yang tidak lengkap) THEN sistem gagal dengan error "Composer could not find a composer.json file in D:\Temp\NativeBuild"

1.4 WHEN Composer mengekstrak package ZIP menggunakan 7-Zip ke path `vendor\composer\tmp-*.zip` di dalam temp dir THEN sistem gagal menemukan file ZIP sementara karena total path melebihi Windows MAX_PATH 260 karakter (contoh: `D:\Temp\NativeBuild\vendor\composer\tmp-abc123\vendor\package\src\...`)

1.5 WHEN banyak package Composer gagal diinstall akibat kegagalan 7-Zip atau composer.json tidak ditemukan THEN sistem menghasilkan autoloader yang tidak lengkap sehingga `bootstrap/app.php` crash dengan "Class Illuminate\Foundation\Application not found"

1.6 ~~WHEN `runTheAndroidBuild()` menjalankan Gradle build di Windows THEN sistem mengeksekusi `passthru("cd /d \"$androidPath\" && gradlew.bat $gradleTask")` yang gagal karena CMD tidak menemukan `gradlew.bat` di current directory~~ — **SUDAH DIPERBAIKI**: `app/Traits/NativePHP/RunsAndroid.php` sekarang menggunakan `Process::run("\"$gradleWrapper\" $gradleTask")` dengan full absolute path ke `gradlew.bat`

### Expected Behavior (Correct)

2.1 WHEN robocopy gagal dengan exit code >= 8 THEN sistem SHALL menggunakan PHP native file copy (`File::copyDirectory` atau `RecursiveDirectoryIterator`) sebagai fallback yang menyalin semua file termasuk file di root level secara reliable, bukan xcopy dengan pola `\*`

2.2 WHEN fallback copy dijalankan THEN sistem SHALL memverifikasi bahwa `composer.json` ada di temp dir sebelum melanjutkan ke langkah `composer install`, dan menampilkan error yang jelas jika file tidak ditemukan

2.3 WHEN `composer install` dijalankan di temp dir THEN sistem SHALL berhasil menemukan `composer.json` dan menginstall semua dependencies tanpa error

2.4 WHEN Composer mengekstrak package menggunakan 7-Zip di Windows THEN sistem SHALL menggunakan temp dir dengan path yang lebih pendek (misalnya `%TEMP%\np-{timestamp}` atau `C:\Temp\np`) untuk menghindari Windows MAX_PATH limitation, atau mengaktifkan Long Path Support via `HKLM\SYSTEM\CurrentControlSet\Control\FileSystem\LongPathsEnabled`

2.5 WHEN semua package Composer berhasil diinstall THEN sistem SHALL menghasilkan autoloader yang lengkap sehingga `bootstrap/app.php` dapat di-bootstrap tanpa error

2.6 WHEN `runTheAndroidBuild()` menjalankan Gradle build di Windows THEN sistem SHALL mengeksekusi `gradlew.bat` menggunakan full absolute path (`"$androidPath\gradlew.bat" $gradleTask`) melalui `Process::run()` agar tidak bergantung pada current working directory — **SUDAH DIIMPLEMENTASIKAN** di `app/Traits/NativePHP/RunsAndroid.php`

### Unchanged Behavior (Regression Prevention)

3.1 WHEN `php artisan native:run` dijalankan di Linux atau macOS THEN sistem SHALL CONTINUE TO menggunakan `rsync` untuk menyalin source dan `./gradlew` untuk Gradle build tanpa perubahan

3.2 WHEN robocopy berhasil (exit code 0-7) di Windows THEN sistem SHALL CONTINUE TO menggunakan hasil robocopy tanpa menjalankan fallback copy

3.3 WHEN `composer install` berhasil di temp dir THEN sistem SHALL CONTINUE TO menjalankan `composer dump-autoload --optimize --classmap-authoritative` setelahnya

3.4 WHEN Gradle build berhasil di Windows THEN sistem SHALL CONTINUE TO menginstall APK ke device menggunakan `adb install -r` dengan `timeout(0)` dan meluncurkan app dengan `adb shell am start`

3.5 WHEN `createZipBundle()` dijalankan di Windows THEN sistem SHALL CONTINUE TO menggunakan 7-Zip dengan flag `-ssw` untuk membuat `laravel_bundle.zip` dari temp dir yang sudah berisi hasil composer install

3.6 WHEN `NATIVEPHP_BUILD_TEMP_DIR` di-set di `.env` (saat ini: `D:\Temp\NativeBuild`) THEN sistem SHALL CONTINUE TO menggunakan nilai tersebut sebagai base path untuk temp dir build

3.7 WHEN build type adalah `release` atau `bundle` THEN sistem SHALL CONTINUE TO menjalankan Gradle task yang sesuai (`assembleRelease` atau `bundleRelease`) dan menghasilkan output file di path yang benar

3.8 WHEN `AndroidSdkEnvironment::apply()` dipanggil di awal `NativeRunCommand::handle()` THEN sistem SHALL CONTINUE TO meng-inject `ANDROID_SDK_ROOT`, `JAVA_HOME`, `GRADLE_HOME`, dan `PATH` yang benar dari konfigurasi `.env` sebelum proses build dimulai

3.9 WHEN `NativeRunCommand` menggunakan trait conflict resolution (`insteadof`) THEN sistem SHALL CONTINUE TO menggunakan implementasi lokal (`App\Traits\NativePHP\*`) untuk `prepareLaravelBundle`, `createZipBundle`, `platformOptimizedCopy`, `runTheAndroidBuild`, dan `installAndroidIcon` menggantikan implementasi vendor

---

## Bug Condition Pseudocode

### Bug Condition Function

```pascal
FUNCTION isBugCondition(X)
  INPUT: X of type BuildContext
  OUTPUT: boolean

  // Bug terjadi ketika semua kondisi berikut terpenuhi:
  RETURN (
    X.os_family = "Windows"
    AND (
      // Bug 1 & 2: xcopy fallback tidak menyalin root files (masih aktif)
      (X.robocopy_exit_code >= 8 AND X.fallback_uses_xcopy_star_pattern = true)
      // Bug 3: composer.json tidak ada di temp dir (konsekuensi Bug 2)
      OR X.composer_json_missing_in_temp_dir = true
      // Bug 4: Composer 7-Zip path terlalu panjang (masih aktif)
      OR X.composer_tmp_path_length > 260
      // Bug 6: gradlew.bat tanpa full path (SUDAH DIPERBAIKI)
      // OR X.gradlew_invoked_without_full_path = true
    )
  )
END FUNCTION
```

### Property: Fix Checking

```pascal
// Property: Fix Checking — Windows Build Pipeline
FOR ALL X WHERE isBugCondition(X) DO
  result ← nativeRun'(X)
  ASSERT result.copy_succeeded = true
    AND result.composer_json_exists_in_temp = true
    AND result.composer_install_succeeded = true
    AND result.gradle_build_succeeded = true
    AND result.apk_installed = true
END FOR
```

### Property: Preservation Checking

```pascal
// Property: Preservation Checking
FOR ALL X WHERE NOT isBugCondition(X) DO
  ASSERT nativeRun(X) = nativeRun'(X)
  // Perilaku di Linux/macOS tidak berubah
  // Perilaku di Windows ketika robocopy berhasil tidak berubah
  // Perilaku AndroidSdkEnvironment::apply() tidak berubah
END FOR
```
