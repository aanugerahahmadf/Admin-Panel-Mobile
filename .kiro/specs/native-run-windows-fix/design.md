# Native Run Windows Fix — Bugfix Design

## Overview

`php artisan native:run` gagal di Windows 10/11 karena empat bug berurutan dalam pipeline build Android. Bug utama berada di `app/Traits/NativePHP/PreparesBuild.php`:

1. **xcopy fallback tidak menyalin root-level files** (Bug 1.1/1.2) — ketika robocopy gagal (exit code ≥ 8), fallback `xcopy "src\*" "dst\"` melewati file di root direktori seperti `composer.json`, `artisan`, dan `.env`.
2. **`composer install` gagal karena `composer.json` tidak ada** (Bug 1.3) — efek domino dari Bug 1.2.
3. **Windows MAX_PATH (260 karakter) terlampaui** (Bug 1.4) — temp dir yang dalam seperti `D:\Temp\NativeBuild\vendor\composer\tmp-*\...` melebihi batas path Windows sehingga Composer gagal mengekstrak package via 7-Zip.
4. **Autoloader tidak lengkap** (Bug 1.5) — efek domino dari Bug 1.2 dan 1.4.

Bug 1.6 (gradlew.bat tanpa full path) sudah diperbaiki di `RunsAndroid.php` dan tidak disentuh.

Strategi perbaikan: (1) ganti xcopy fallback dengan PHP-native `File::copyDirectory()` yang menyalin semua file termasuk root level, (2) tambahkan verifikasi `composer.json` setelah copy, dan (3) persingkat temp dir path menggunakan `sys_get_temp_dir()` sebagai default.

---

## Glossary

- **Bug_Condition (C)**: Kondisi yang memicu bug — ketika build berjalan di Windows dan salah satu dari: robocopy gagal dengan exit code ≥ 8 (sehingga xcopy fallback digunakan), atau path temp dir melebihi 260 karakter.
- **Property (P)**: Perilaku yang diharapkan ketika bug condition terpenuhi — semua file root-level (termasuk `composer.json`) tersalin ke temp dir, dan total path temp dir tidak melebihi MAX_PATH.
- **Preservation**: Perilaku yang tidak boleh berubah — rsync di Linux/macOS, robocopy success path di Windows, `createZipBundle()`, `RunsAndroid.php`, dan semua behavior non-Windows.
- **`platformOptimizedCopy()`**: Method di `app/Traits/NativePHP/PreparesBuild.php` yang menyalin source Laravel ke temp dir menggunakan robocopy (Windows) atau rsync (Unix).
- **`prepareLaravelBundle()`**: Method di `app/Traits/NativePHP/PreparesBuild.php` yang mengatur seluruh pipeline: copy source → composer install → dump-autoload → zip bundle.
- **`isBugCondition(X)`**: Fungsi pseudocode yang mengidentifikasi input BuildContext yang memicu bug.
- **MAX_PATH**: Batas panjang path Windows sebesar 260 karakter (tanpa Long Path Support diaktifkan).
- **xcopy star pattern**: Pola `xcopy "src\*" "dst\"` yang secara desain melewati file di root direktori `src`.
- **`sys_get_temp_dir()`**: Fungsi PHP yang mengembalikan direktori temp sistem (biasanya `C:\Users\User\AppData\Local\Temp` di Windows).

---

## Bug Details

### Bug Condition

Bug termanifestasi dalam dua skenario independen yang keduanya terjadi di Windows:

**Skenario A (Bug 1.1/1.2/1.3/1.5):** Ketika robocopy gagal dengan exit code ≥ 8, fallback ke `xcopy "src\*" "dst\"` dijalankan. Pola `\*` pada xcopy adalah perilaku yang terdokumentasi: xcopy hanya menyalin *isi* direktori, bukan file yang berada langsung di root direktori sumber. Akibatnya `composer.json`, `artisan`, `.env`, dan file root lainnya tidak tersalin.

**Skenario B (Bug 1.4):** Ketika `NATIVEPHP_BUILD_TEMP_DIR` diset ke path yang dalam (misalnya `D:\Temp\NativeBuild`), Composer mengekstrak package ke `vendor\composer\tmp-{hash}\` di dalam temp dir tersebut. Total path bisa mencapai 280+ karakter, melebihi MAX_PATH Windows.

**Formal Specification:**

```
FUNCTION isBugCondition(X)
  INPUT: X of type BuildContext
  OUTPUT: boolean

  RETURN (
    X.os_family = "Windows"
    AND (
      // Skenario A: xcopy fallback tidak menyalin root files
      (X.robocopy_exit_code >= 8
       AND X.fallback_method = "xcopy_star_pattern"
       AND NOT X.composer_json_exists_in_temp_dir)

      // Skenario B: path temp dir terlalu panjang untuk MAX_PATH
      OR (length(X.temp_dir_path + "\vendor\composer\tmp-xxxxxxxx\") > 260)
    )
  )
END FUNCTION
```

### Examples

**Skenario A — xcopy fallback melewati composer.json:**
- Input: Windows build, robocopy gagal exit code 8 (access denied oleh antivirus), xcopy fallback dijalankan
- Expected: `D:\Temp\NativeBuild\composer.json` ada
- Actual: `D:\Temp\NativeBuild\composer.json` tidak ada; hanya subdirektori yang tersalin

**Skenario A — composer install gagal:**
- Input: temp dir setelah xcopy fallback tanpa `composer.json`
- Expected: `composer install` berhasil
- Actual: `Composer could not find a composer.json file in D:\Temp\NativeBuild`

**Skenario B — MAX_PATH terlampaui:**
- Input: `NATIVEPHP_BUILD_TEMP_DIR=D:\Temp\NativeBuild`, Composer mengekstrak `vendor/laravel/framework`
- Path: `D:\Temp\NativeBuild\vendor\composer\tmp-abc12345\laravel\framework\src\Illuminate\Foundation\` = 285 karakter
- Expected: ekstraksi berhasil
- Actual: 7-Zip gagal menemukan file ZIP sementara karena path terlalu panjang

**Edge case — robocopy berhasil (exit code 0-7):**
- Input: Windows build, robocopy berhasil exit code 1 (file disalin)
- Expected: fallback tidak dijalankan, behavior tidak berubah
- Actual (setelah fix): sama — fallback tidak dijalankan ✓

---

## Expected Behavior

### Preservation Requirements

**Unchanged Behaviors:**
- Ketika `php artisan native:run` dijalankan di Linux atau macOS, `rsync` tetap digunakan untuk menyalin source tanpa perubahan apapun
- Ketika robocopy berhasil (exit code 0–7) di Windows, hasil robocopy digunakan langsung tanpa menjalankan fallback
- Method `createZipBundle()` tidak diubah — sudah berfungsi dengan benar termasuk flag `-ssw` dan toleransi exit code 1
- Method `runTheAndroidBuild()` di `RunsAndroid.php` tidak diubah — sudah menggunakan full absolute path untuk `gradlew.bat`
- Ketika `NATIVEPHP_BUILD_TEMP_DIR` di-set di `.env`, nilai tersebut tetap digunakan sebagai base path
- Setelah `composer install` berhasil, `composer dump-autoload --optimize --classmap-authoritative` tetap dijalankan
- Build type `release` dan `bundle` tetap menjalankan Gradle task yang sesuai

**Scope:**
Semua input yang TIDAK memenuhi `isBugCondition(X)` harus menghasilkan perilaku yang identik dengan kode sebelum fix. Ini mencakup:
- Semua build di Linux/macOS
- Windows build di mana robocopy berhasil (exit code 0–7)
- Semua interaksi dengan `createZipBundle()`, `RunsAndroid.php`, dan `AndroidSdkEnvironment`

---

## Hypothesized Root Cause

Berdasarkan analisis kode di `app/Traits/NativePHP/PreparesBuild.php`:

1. **xcopy Star Pattern Bug (Bug 1.2 — Root Cause Utama)**: Baris `xcopy "{$source}\*" "{$destination}\" /E /I /Y /Q` menggunakan wildcard `\*` yang secara desain xcopy hanya menyalin isi subdirektori, bukan file yang berada langsung di root `$source`. Ini adalah perilaku xcopy yang terdokumentasi dan bukan bug xcopy itu sendiri — melainkan penggunaan pola yang salah.

2. **Tidak Ada Verifikasi Post-Copy (Bug 1.3 — Contributing Cause)**: Setelah `platformOptimizedCopy()` selesai, `prepareLaravelBundle()` langsung memanggil `composer install` tanpa memverifikasi bahwa `composer.json` ada di `$tempDir`. Tidak ada guard yang mendeteksi kegagalan copy sebelum melanjutkan.

3. **Temp Dir Path Terlalu Dalam (Bug 1.4 — Independent Root Cause)**: `NATIVEPHP_BUILD_TEMP_DIR=D:\Temp\NativeBuild` menghasilkan path dasar 20 karakter. Ditambah `\vendor\composer\tmp-xxxxxxxx\` (30 karakter) dan nama package yang panjang, total bisa melebihi 260 karakter. Tidak ada validasi panjang path saat menentukan temp dir.

4. **Fallback Tidak Menggunakan PHP-Native Copy**: Ketika robocopy gagal, fallback seharusnya menggunakan `File::copyDirectory()` atau `RecursiveDirectoryIterator` yang tidak memiliki batasan xcopy. PHP-native copy menyalin semua file termasuk root level secara reliable di semua kondisi Windows.

---

## Correctness Properties

Property 1: Bug Condition — PHP-Native Fallback Menyalin Semua Root Files

_For any_ BuildContext di Windows di mana robocopy gagal (exit code ≥ 8), fungsi `platformOptimizedCopy()` yang sudah diperbaiki SHALL menggunakan PHP-native copy (`File::copyDirectory()`) sebagai fallback yang menyalin semua file termasuk file di root direktori sumber (seperti `composer.json`, `artisan`, `.env`), sehingga `composer.json` PASTI ada di direktori tujuan setelah copy selesai.

**Validates: Requirements 2.1, 2.2**

Property 2: Bug Condition — Temp Dir Path Tidak Melebihi MAX_PATH

_For any_ BuildContext di Windows, panjang path temp dir yang dihasilkan oleh `prepareLaravelBundle()` SHALL tidak melebihi 200 karakter (memberikan margin 60 karakter untuk path vendor Composer yang dalam), sehingga total path termasuk `\vendor\composer\tmp-xxxxxxxx\package\src\` tetap di bawah MAX_PATH 260 karakter.

**Validates: Requirements 2.4**

Property 3: Preservation — Linux/macOS Behavior Tidak Berubah

_For any_ BuildContext di mana `PHP_OS_FAMILY !== 'Windows'`, fungsi `platformOptimizedCopy()` yang sudah diperbaiki SHALL menghasilkan perilaku yang identik dengan fungsi original — menggunakan rsync dengan exclude flags yang sama dan menghasilkan output yang sama.

**Validates: Requirements 3.1**

Property 4: Preservation — Robocopy Success Path Tidak Berubah

_For any_ BuildContext di Windows di mana robocopy berhasil (exit code 0–7), fungsi `platformOptimizedCopy()` yang sudah diperbaiki SHALL tidak menjalankan fallback copy dan menghasilkan perilaku yang identik dengan fungsi original.

**Validates: Requirements 3.2**

---

## Fix Implementation

### Changes Required

Semua perubahan dilakukan di satu file: `app/Traits/NativePHP/PreparesBuild.php`

---

**File**: `app/Traits/NativePHP/PreparesBuild.php`

**Method 1**: `platformOptimizedCopy()`

**Specific Changes**:

1. **Ganti xcopy fallback dengan PHP-native copy**: Hapus baris `xcopy "{$source}\*" "{$destination}\" /E /I /Y /Q` di blok fallback (ketika robocopy gagal). Ganti dengan `File::copyDirectory($source, $destination)` yang menyalin semua file termasuk root level.

2. **Ganti xcopy di else branch (no excludedDirs)**: Baris `xcopy "{$source}\*" "{$destination}\" /E /I /Y /Q` di branch `else` (ketika `$excludedDirs` kosong) juga menggunakan pola yang sama. Ganti dengan `File::copyDirectory($source, $destination)` untuk konsistensi.

3. **Tambahkan logging yang informatif**: Setelah PHP-native copy berhasil, log pesan yang jelas untuk membedakan dari robocopy success path.

**Pseudocode setelah fix:**
```
FUNCTION platformOptimizedCopy(source, destination, excludedDirs)
  IF os = Windows THEN
    IF excludedDirs NOT empty THEN
      cmd = robocopy dengan /MIR dan /XD flags
      exec(cmd)
      IF robocopy_exit_code >= 8 THEN
        log("WARNING: robocopy failed, using PHP-native fallback")
        File::copyDirectory(source, destination)  // ← FIX: ganti xcopy
        log("PHP-native copy completed")
      END IF
    ELSE
      File::copyDirectory(source, destination)    // ← FIX: ganti xcopy
    END IF
  ELSE
    rsync (tidak berubah)
  END IF
END FUNCTION
```

---

**Method 2**: `prepareLaravelBundle()`

**Specific Changes**:

4. **Persingkat temp dir path default**: Ubah logika penentuan `$tempDir` di Windows. Gunakan `sys_get_temp_dir()` sebagai default ketika `NATIVEPHP_BUILD_TEMP_DIR` tidak di-set, dengan suffix pendek `np-{timestamp}`. Ini menghasilkan path seperti `C:\Users\User\AppData\Local\Temp\np-20260602` (≈55 karakter) dibanding `D:\Temp\NativeBuild` (18 karakter tapi bisa dikonfigurasi lebih dalam).

   Dokumentasikan di komentar bahwa jika `NATIVEPHP_BUILD_TEMP_DIR` di-set, nilainya harus pendek (misalnya `D:\np` atau `C:\np`) untuk menghindari MAX_PATH.

5. **Tambahkan guard verifikasi `composer.json`**: Setelah `platformOptimizedCopy()` selesai dan sebelum `composer install` dipanggil, tambahkan pengecekan:
   ```php
   if (!file_exists($tempDir . DIRECTORY_SEPARATOR . 'composer.json')) {
       throw new \RuntimeException(
           "composer.json not found in temp dir after copy.\n" .
           "  Source: {$source}\n" .
           "  Destination: {$tempDir}\n" .
           "This indicates the copy step failed to transfer root-level files."
       );
   }
   ```

**Pseudocode setelah fix:**
```
FUNCTION prepareLaravelBundle(excludeDevDependencies)
  // ← FIX: gunakan sys_get_temp_dir() sebagai default
  IF os = Windows THEN
    base = env('NATIVEPHP_BUILD_TEMP_DIR') ?? sys_get_temp_dir()
    tempDir = base + "\np-" + date('Ymd')
  ELSE
    tempDir = base_path('nativephp/android/laravel')
  END IF

  platformOptimizedCopy(source, tempDir, excludedDirs)

  // ← FIX: guard verifikasi sebelum composer install
  IF NOT file_exists(tempDir + "/composer.json") THEN
    THROW RuntimeException("composer.json not found in temp dir after copy...")
  END IF

  composer install ...
  composer dump-autoload ...
  createZipBundle(tempDir, destinationZip, excludedDirs)
END FUNCTION
```

---

## Testing Strategy

### Validation Approach

Strategi testing mengikuti dua fase: pertama, surface counterexample yang mendemonstrasikan bug pada kode yang belum diperbaiki untuk mengkonfirmasi root cause analysis; kemudian verifikasi bahwa fix bekerja dengan benar dan tidak merusak behavior yang sudah ada.

### Exploratory Bug Condition Checking

**Goal**: Surface counterexample yang mendemonstrasikan bug SEBELUM mengimplementasikan fix. Konfirmasi atau refutasi root cause analysis.

**Test Plan**: Buat unit test yang mensimulasikan kondisi bug — robocopy gagal (mock exit code 8) dan xcopy fallback dijalankan — lalu assert bahwa `composer.json` ada di direktori tujuan. Jalankan test ini pada kode YANG BELUM DIPERBAIKI untuk mengobservasi kegagalan.

**Test Cases**:
1. **Robocopy Failure + xcopy Fallback Test**: Simulasikan robocopy gagal (exit code 8), jalankan xcopy fallback, assert `composer.json` ada di destination — AKAN GAGAL pada kode unfixed karena xcopy `\*` melewati root files
2. **Root Files Presence Test**: Setelah copy dengan xcopy fallback, cek keberadaan `composer.json`, `artisan`, `.env` di destination — AKAN GAGAL pada kode unfixed
3. **Deep Temp Dir Path Length Test**: Hitung panjang path `NATIVEPHP_BUILD_TEMP_DIR=D:\Temp\NativeBuild` + `\vendor\composer\tmp-xxxxxxxx\laravel\framework\src\Illuminate\Foundation\` — AKAN MENUNJUKKAN > 260 karakter
4. **No-ExcludedDirs Branch Test**: Panggil `platformOptimizedCopy()` tanpa `$excludedDirs`, assert semua root files tersalin — AKAN GAGAL pada kode unfixed (xcopy `\*` di else branch)

**Expected Counterexamples**:
- `composer.json` tidak ada di destination setelah xcopy fallback
- File root lainnya (`artisan`, `.env`) juga tidak ada
- Possible causes: xcopy `\*` wildcard pattern, tidak ada PHP-native fallback

### Fix Checking

**Goal**: Verifikasi bahwa untuk semua input di mana bug condition terpenuhi, fungsi yang sudah diperbaiki menghasilkan perilaku yang diharapkan.

**Pseudocode:**
```
FOR ALL X WHERE isBugCondition(X) DO
  result := platformOptimizedCopy_fixed(X.source, X.destination, X.excludedDirs)
  ASSERT file_exists(X.destination + "/composer.json")
  ASSERT file_exists(X.destination + "/artisan")
  ASSERT length(X.temp_dir_path) <= 200
END FOR
```

### Preservation Checking

**Goal**: Verifikasi bahwa untuk semua input di mana bug condition TIDAK terpenuhi, fungsi yang sudah diperbaiki menghasilkan hasil yang sama dengan fungsi original.

**Pseudocode:**
```
FOR ALL X WHERE NOT isBugCondition(X) DO
  ASSERT platformOptimizedCopy_original(X) = platformOptimizedCopy_fixed(X)
  // Linux/macOS: rsync command identik
  // Windows robocopy success: tidak ada fallback yang dijalankan
END FOR
```

**Testing Approach**: Property-based testing direkomendasikan untuk preservation checking karena:
- Menghasilkan banyak test case secara otomatis di seluruh domain input
- Menangkap edge case yang mungkin terlewat oleh unit test manual
- Memberikan jaminan kuat bahwa behavior tidak berubah untuk semua non-buggy inputs

**Test Plan**: Observasi behavior pada kode unfixed untuk Linux/macOS dan Windows robocopy success path, kemudian tulis property-based test yang mengkaptulasi behavior tersebut.

**Test Cases**:
1. **Linux/macOS Preservation**: Verifikasi bahwa rsync command yang dihasilkan identik sebelum dan sesudah fix untuk berbagai kombinasi `$excludedDirs`
2. **Robocopy Success Preservation**: Verifikasi bahwa ketika robocopy berhasil (exit code 0–7), tidak ada fallback yang dijalankan dan hasil identik
3. **NATIVEPHP_BUILD_TEMP_DIR Preservation**: Verifikasi bahwa ketika env var di-set, nilainya tetap digunakan sebagai base path

### Unit Tests

- Test `platformOptimizedCopy()` dengan robocopy failure: assert `File::copyDirectory()` dipanggil sebagai fallback
- Test `platformOptimizedCopy()` tanpa `$excludedDirs`: assert PHP-native copy digunakan (bukan xcopy)
- Test `prepareLaravelBundle()` guard: assert exception dilempar ketika `composer.json` tidak ada setelah copy
- Test path length: assert temp dir yang dihasilkan `prepareLaravelBundle()` ≤ 200 karakter di Windows
- Test `sys_get_temp_dir()` default: assert temp dir menggunakan system temp ketika `NATIVEPHP_BUILD_TEMP_DIR` tidak di-set

### Property-Based Tests

- Generate berbagai kombinasi `$excludedDirs` (kosong, satu item, banyak item) dan verifikasi bahwa setelah fix, semua root files selalu tersalin di Windows
- Generate berbagai nilai `NATIVEPHP_BUILD_TEMP_DIR` (pendek, panjang, tidak di-set) dan verifikasi bahwa path yang dihasilkan selalu ≤ 200 karakter
- Generate berbagai OS family values dan verifikasi bahwa Linux/macOS selalu menggunakan rsync tanpa perubahan

### Integration Tests

- Test full pipeline `prepareLaravelBundle()` di Windows dengan robocopy failure yang disimulasikan: verifikasi `composer.json` ada di temp dir sebelum `composer install`
- Test bahwa `composer install` berhasil setelah fix diterapkan (temp dir memiliki `composer.json`)
- Test bahwa `createZipBundle()` tidak terpengaruh oleh perubahan di `platformOptimizedCopy()` dan `prepareLaravelBundle()`
