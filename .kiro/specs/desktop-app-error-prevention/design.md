# Desktop App Error Prevention Bugfix Design

## Overview

Aplikasi desktop NativePHP Electron mengalami tiga masalah kritis yang saling terkait yang mencegah aplikasi dari launching sama sekali: spawn errors (errno -4094), invalid semver version ("DEBUG"), dan GPU process crashes. Strategi fix melibatkan: (1) mengidentifikasi dan memperbaiki path PHP binary yang salah atau missing, (2) menyediakan app version yang valid untuk electron-updater atau menonaktifkan auto-updater di development mode, (3) mengaktifkan software rendering fallback atau menonaktifkan hardware acceleration untuk mencegah GPU crash. Pendekatan ini bersifat minimal dan targeted, fokus pada error handling dan configuration tanpa mengubah arsitektur core dari NativePHP Electron.

## Glossary

- **Bug_Condition (C)**: Kondisi yang memicu bug - ketika aplikasi desktop dijalankan dengan `php artisan native:serve` atau `npm run dev`, proses PHP gagal di-spawn, app version invalid, atau GPU crash
- **Property (P)**: Behavior yang diharapkan - aplikasi desktop harus launch dengan sukses, PHP binary dapat di-spawn, version valid atau auto-updater disabled, dan GPU menggunakan software rendering fallback
- **Preservation**: Existing behavior untuk Android app, production build, environment config, dan development hot reload yang harus tetap tidak berubah
- **native:serve**: Artisan command di `app/Console/Commands/NativeServeCommand.php` yang memvalidasi dependencies dan mendelegasikan ke NativePHP Electron
- **nativephp/electron**: Package vendor yang menyediakan Electron wrapper untuk Laravel desktop apps
- **PHP binary spawning**: Proses dimana Electron menjalankan PHP server sebagai child process untuk backend Laravel
- **electron-updater**: Modul yang menangani auto-update untuk Electron apps, requires semver-compliant version
- **GPU hardware acceleration**: Feature Electron yang menggunakan GPU untuk rendering, dapat crash jika driver incompatible

## Bug Details

### Bug Condition

Bug manifests when the desktop application is started using `php artisan native:serve` or `npm run dev` command. Three related failures occur: (1) the PHP binary spawn process fails with errno -4094 "spawn UNKNOWN", (2) electron-updater rejects the app version "DEBUG" as invalid semver, and (3) the GPU process crashes repeatedly with exit_code=-1073741515, causing the entire app to terminate.

**Formal Specification:**

```
FUNCTION isBugCondition(input)
  INPUT: input of type DesktopAppStartup
  OUTPUT: boolean
  
  RETURN (input.command IN ['php artisan native:serve', 'npm run dev'])
         AND (input.platform = 'Desktop')
         AND (phpSpawnFails(input) OR appVersionInvalid(input) OR gpuProcessCrashes(input))
END FUNCTION

FUNCTION phpSpawnFails(input)
  RETURN spawnError(input.phpBinary) = 'UNKNOWN' 
         AND errno(input.phpBinary) = -4094
END FUNCTION

FUNCTION appVersionInvalid(input)
  RETURN input.appVersion = 'DEBUG' 
         AND NOT isSemver(input.appVersion)
END FUNCTION

FUNCTION gpuProcessCrashes(input)
  RETURN gpuProcessExitCode(input) = -1073741515
         AND crashCount(input) >= 9
         AND errorMessage(input) CONTAINS 'GPU process isn\'t usable'
END FUNCTION
```

### Examples

- **Example 1 (PHP Spawn Error)**: Ketika `php artisan native:serve` dijalankan, Electron mencoba spawn PHP binary tapi gagal dengan error "Error: spawn UNKNOWN errno -4094" karena path PHP binary tidak ditemukan atau incorrect
- **Example 2 (Invalid Version)**: electron-updater membaca app version dari package.json atau config, menemukan value "DEBUG", dan throws error "App version is not a valid semver version: 'DEBUG'" karena tidak match format `X.Y.Z`
- **Example 3 (GPU Crash)**: Electron mencoba initialize hardware acceleration, GPU driver incompatible atau missing, GPU process crash dengan exit_code=-1073741515, setelah 9 kali restart attempt Electron terminate dengan "GPU process isn't usable. Goodbye."
- **Edge Case (Combined Failures)**: Semua tiga error dapat terjadi bersamaan dalam single startup attempt, dengan spawn error terjadi first, kemudian version error, kemudian GPU crash sebagai final fatal error

## Expected Behavior

### Preservation Requirements

**Unchanged Behaviors:**

- Android app functionality (via `php artisan native:run`) harus tetap berfungsi normal
- Semua existing features dalam desktop app (authentication, database, file upload, dll) harus tetap work setelah launch
- Production build process dan distribution harus tidak terpengaruh
- Environment variable reading dari .env files harus tetap sama
- Hot reload dan watch mode di development harus tetap functional

**Scope:**

All inputs that do NOT involve desktop application startup (`php artisan native:serve` or `npm run dev` on desktop platform) should be completely unaffected by this fix. This includes:

- Mobile app runs via `php artisan native:run`
- Web app runs via `php artisan serve`
- Production builds for any platform
- Configuration loading for non-desktop platforms
- Asset compilation for mobile or web

## Hypothesized Root Cause

Based on the bug description and error patterns, the most likely issues are:

1. **PHP Binary Path Issue**: NativePHP Electron config atau code mencari PHP binary di path yang incorrect atau menggunakan relative path yang tidak resolve correctly di Electron environment
   - Possible locations: `vendor/nativephp/electron/resources/js/` atau config files
   - May need to use absolute path atau proper binary detection di Windows

2. **App Version Configuration**: Version dalam package.json, electron-builder config, atau NativePHP config set ke "DEBUG" untuk development, tapi electron-updater strict requires semver format
   - Need to either: set valid semver version (e.g., "1.0.0-dev"), atau disable auto-updater di development mode
   - Config files: `package.json`, `nativephp.json`, atau electron-builder configuration

3. **GPU Hardware Acceleration**: Electron enables hardware acceleration by default, tapi GPU driver pada system incompatible atau outdated, causing repeated crashes
   - Solution: add `--disable-gpu` flag atau `app.disableHardwareAcceleration()` call
   - May need conditional logic: disable only in development atau provide config option

4. **Missing Error Handling**: PHP spawn process tidak memiliki proper error handling atau retry logic, causing silent failures yang cascade ke other issues
   - Need to add try-catch, logging, dan informative error messages
   - Should check PHP binary existence before attempting spawn

## Correctness Properties

Property 1: Bug Condition - Desktop App Launches Successfully

_For any_ startup attempt where the desktop application is launched via `php artisan native:serve` or `npm run dev`, the fixed code SHALL successfully spawn the PHP binary without "spawn UNKNOWN" errors, SHALL use a valid semver version or disable auto-updater to prevent version validation errors, and SHALL use software rendering or disable GPU acceleration to prevent GPU process crashes, resulting in the Electron window appearing and the application being usable.

**Validates: Requirements 2.1, 2.2, 2.3, 2.4, 2.5**

Property 2: Preservation - Non-Desktop Functionality Unchanged

_For any_ application run that is NOT a desktop app startup (including mobile app via `php artisan native:run`, web app via `php artisan serve`, production builds, and configuration loading for other platforms), the fixed code SHALL produce exactly the same behavior as the original code, preserving all existing functionality for Android apps, web apps, environment configuration, hot reload, and production builds.

**Validates: Requirements 3.1, 3.2, 3.3, 3.4, 3.5**

## Fix Implementation

### Changes Required

Assuming our root cause analysis is correct:

**File 1**: `vendor/nativephp/electron/resources/js/main.js` (atau equivalent Electron main process file)

**Changes**:

1. **PHP Binary Path Detection**: Add logic to detect PHP binary using absolute path resolution
   - Check common PHP installation paths on Windows (C:\php, C:\xampp\php, etc.)
   - Use `process.env.PATH` scanning to find php.exe
   - Fallback to packaged PHP binary if available
   - Add logging untuk debugging path resolution

2. **GPU Acceleration Handling**: Add conditional GPU disable based on environment
   - Add `app.disableHardwareAcceleration()` call before app ready event di development mode
   - Atau add `--disable-gpu` commandline switch
   - Make it configurable via environment variable (e.g., `ELECTRON_DISABLE_GPU=true`)

3. **Error Handling for PHP Spawn**: Wrap PHP spawn logic in try-catch dengan proper error messages
   - Log actual PHP binary path being attempted
   - Check file existence before spawn
   - Provide informative error message dengan troubleshooting steps

**File 2**: `package.json` atau `electron-builder.yml`

**Changes**:

1. **App Version Fix**: Change app version from "DEBUG" to valid semver format
   - Set to "1.0.0-dev" atau similar untuk development
   - Atau use environment variable to inject version (e.g., `process.env.npm_package_version`)

**File 3**: `config/nativephp.php` atau NativePHP Electron config

**Changes**:

1. **Auto-Updater Configuration**: Add option to disable auto-updater in development mode
   - Add config key `'auto_updater_enabled' => env('NATIVEPHP_AUTO_UPDATER', true)`
   - Set to false di `.env` atau `.env.desktop` untuk development

2. **PHP Binary Configuration**: Add explicit PHP binary path configuration option
   - Add config key `'php_binary_path' => env('NATIVEPHP_PHP_BINARY', null)`
   - Allow override via environment variable untuk custom PHP installations

**File 4**: `.env.desktop` atau `.env.desktop.example`

**Changes**:

1. **Add Development-Specific Config**:
   ```
   NATIVEPHP_AUTO_UPDATER=false
   ELECTRON_DISABLE_GPU=true
   NATIVEPHP_PHP_BINARY=C:\path\to\php.exe  # Optional override
   ```

**File 5**: Error handling di NativePHP Electron service provider atau bootstrap

**Changes**:

1. **Add Graceful Error Messages**: Intercept spawn errors dan provide user-friendly messages
   - Detect "spawn UNKNOWN" errors specifically
   - Display dialog dengan troubleshooting instructions
   - Log detailed error info untuk debugging

## Testing Strategy

### Validation Approach

The testing strategy follows a two-phase approach: first, surface counterexamples that demonstrate the bugs on unfixed code, then verify the fixes work correctly and preserve existing behavior.

### Exploratory Bug Condition Checking

**Goal**: Surface counterexamples that demonstrate the bugs BEFORE implementing the fix. Confirm or refute the root cause analysis. If we refute, we will need to re-hypothesize.

**Test Plan**: Write tests that attempt to start the desktop application and assert that PHP binary is spawned, version is valid, and GPU doesn't crash. Run these tests on the UNFIXED code to observe failures and understand the root cause.

**Test Cases**:

1. **PHP Spawn Test**: Attempt to launch desktop app dan verify PHP process is spawned successfully (will fail on unfixed code - expect "spawn UNKNOWN" error)
2. **Version Validation Test**: Check app version configuration dan verify it matches semver format (will fail on unfixed code - expect "DEBUG" value)
3. **GPU Process Test**: Launch desktop app dan monitor GPU process status (will fail on unfixed code - expect repeated crashes)
4. **Combined Launch Test**: Full integration test launching desktop app end-to-end (will fail on unfixed code - expect fatal crash after multiple GPU attempts)

**Expected Counterexamples**:

- PHP spawn fails dengan error "spawn UNKNOWN errno -4094"
- App version validation fails dengan "App version is not a valid semver version: 'DEBUG'"
- GPU process crashes dengan exit_code=-1073741515, followed by "GPU process isn't usable. Goodbye."
- Possible causes: incorrect PHP path, hardcoded "DEBUG" version, missing GPU fallback

### Fix Checking

**Goal**: Verify that for all inputs where the bug condition holds, the fixed function produces the expected behavior.

**Pseudocode:**

```
FOR ALL input WHERE isBugCondition(input) DO
  result := launchDesktopApp_fixed(input)
  ASSERT result.phpSpawned = true
  ASSERT result.appVersionValid = true OR result.autoUpdaterDisabled = true
  ASSERT result.gpuCrash = false
  ASSERT result.windowVisible = true
  ASSERT result.applicationUsable = true
END FOR
```

### Preservation Checking

**Goal**: Verify that for all inputs where the bug condition does NOT hold, the fixed function produces the same result as the original function.

**Pseudocode:**

```
FOR ALL input WHERE NOT isBugCondition(input) DO
  ASSERT launchApp_original(input) = launchApp_fixed(input)
END FOR
```

**Testing Approach**: Property-based testing is recommended for preservation checking because:

- It generates many test cases automatically across the input domain (different platforms, environments, configs)
- It catches edge cases that manual unit tests might miss (e.g., specific env var combinations)
- It provides strong guarantees that behavior is unchanged for all non-desktop-startup scenarios

**Test Plan**: Observe behavior on UNFIXED code first for mobile app runs, web app runs, production builds, then write property-based tests capturing that behavior.

**Test Cases**:

1. **Mobile App Preservation**: Observe that `php artisan native:run` works correctly on unfixed code, then write test to verify this continues after fix
2. **Web App Preservation**: Observe that `php artisan serve` works correctly on unfixed code, then write test to verify this continues after fix
3. **Production Build Preservation**: Observe that production build process completes successfully on unfixed code, then write test to verify this continues after fix
4. **Environment Config Preservation**: Observe that .env file reading works correctly for mobile/web on unfixed code, then write test to verify this continues after fix
5. **Hot Reload Preservation**: Observe that development watch mode works correctly on unfixed code, then write test to verify this continues after fix

### Unit Tests

- Test PHP binary path detection logic dengan various Windows path configurations
- Test app version parsing dan validation logic
- Test GPU disable flag toggling based on environment variables
- Test error handling for missing PHP binary
- Test config loading untuk desktop-specific settings

### Property-Based Tests

- Generate random desktop app startup configurations dan verify successful launch
- Generate random platform configurations (mobile, web, desktop) dan verify preservation of non-desktop functionality
- Test across many environment variable combinations to ensure config overrides work correctly
- Generate random PHP installation paths dan verify detection logic handles them

### Integration Tests

- Full desktop app launch flow dari `php artisan native:serve` hingga window visible
- Verify all three error conditions (spawn, version, GPU) are resolved in single launch
- Test switching between platforms (web → desktop → mobile) dan verify each works correctly
- Test production build process end-to-end untuk desktop platform
- Verify existing features (auth, database, file upload) work after successful desktop launch
