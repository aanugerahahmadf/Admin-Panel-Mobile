# Implementation Plan

- [x] 1. Write bug condition exploration test
  - **Property 1: Bug Condition** - Desktop App Launch Failures
  - **CRITICAL**: This test MUST FAIL on unfixed code - failure confirms the bugs exist
  - **DO NOT attempt to fix the test or the code when it fails**
  - **NOTE**: This test encodes the expected behavior - it will validate the fix when it passes after implementation
  - **GOAL**: Surface counterexamples that demonstrate the bugs exist (PHP spawn error, invalid version, GPU crash)
  - **Scoped PBT Approach**: For these deterministic bugs, scope the property to the concrete failing cases to ensure reproducibility
  - Test that `php artisan native:serve` command triggers:
    - PHP binary spawn attempt that fails with errno -4094 "spawn UNKNOWN"
    - App version validation that rejects "DEBUG" as invalid semver
    - GPU process initialization that crashes with exit_code=-1073741515
  - Test implementation details from Bug Condition in design (isBugCondition, phpSpawnFails, appVersionInvalid, gpuProcessCrashes pseudocode)
  - The test assertions should match the Expected Behavior Properties from design:
    - ASSERT phpSpawned = true (will fail - confirms spawn bug)
    - ASSERT appVersionValid = true OR autoUpdaterDisabled = true (will fail - confirms version bug)
    - ASSERT gpuCrash = false (will fail - confirms GPU bug)
    - ASSERT windowVisible = true (will fail - confirms app doesn't launch)
  - Run test on UNFIXED code
  - **EXPECTED OUTCOME**: Test FAILS (this is correct - it proves the bugs exist)
  - Document counterexamples found:
    - Specific spawn error message and errno
    - Actual app version value found ("DEBUG")
    - GPU crash exit code and crash count
    - Any error logs from Electron console
  - Mark task complete when test is written, run, and failures are documented
  - _Requirements: 1.1, 1.2, 1.3, 1.4_

- [x] 2. Write preservation property tests (BEFORE implementing fix)
  - **Property 2: Preservation** - Non-Desktop Platform Functionality
  - **IMPORTANT**: Follow observation-first methodology
  - Observe behavior on UNFIXED code for non-desktop scenarios:
    - Run `php artisan native:run` for Android app - observe it works correctly
    - Run `php artisan serve` for web app - observe it works correctly
    - Test environment config loading for mobile platform - observe it works correctly
    - Test hot reload in mobile development - observe it works correctly
  - Write property-based tests capturing observed behavior patterns from Preservation Requirements:
    - FOR ALL platform IN [Mobile, Web] ASSERT launchApp(platform) succeeds
    - FOR ALL envConfig NOT related to desktop ASSERT configLoading(envConfig) = original behavior
    - FOR ALL features IN [auth, database, fileUpload] ASSERT feature works after non-desktop launch
  - Property-based testing generates many test cases for stronger guarantees across:
    - Different platform modes (Mobile Android, Mobile iOS, Web)
    - Different environment configurations (.env.mobile, .env.web)
    - Different feature combinations
  - Run tests on UNFIXED code
  - **EXPECTED OUTCOME**: Tests PASS (this confirms baseline behavior to preserve)
  - Mark task complete when tests are written, run, and passing on unfixed code
  - _Requirements: 3.1, 3.2, 3.3, 3.4, 3.5_

- [ ] 3. Fix desktop application launch errors

  - [x] 3.1 Fix PHP binary spawn error
    - Locate the NativePHP Electron main process file (likely `vendor/nativephp/electron/resources/js/main.js` or similar)
    - Implement PHP binary path detection logic:
      - Add function to scan common Windows PHP installation paths (C:\php, C:\xampp\php, C:\wamp\bin\php, etc.)
      - Add logic to parse `process.env.PATH` and search for php.exe
      - Add fallback to check for packaged PHP binary in NativePHP resources
      - Add logging to console for debugging which path is being used
    - Add error handling before spawn attempt:
      - Check if PHP binary file exists before attempting to spawn
      - Wrap spawn call in try-catch block
      - Log detailed error information if spawn fails
      - Display user-friendly error dialog with troubleshooting steps
    - Add configuration option in `config/nativephp.php`:
      - Add `'php_binary_path' => env('NATIVEPHP_PHP_BINARY', null)` to allow manual override
    - Update `.env.desktop.example` with PHP binary path example:
      - Add `# NATIVEPHP_PHP_BINARY=C:\php\php.exe  # Optional: override PHP binary path`
    - _Bug_Condition: phpSpawnFails(input) where spawnError = 'UNKNOWN' AND errno = -4094_
    - _Expected_Behavior: phpSpawned = true (from design expectedBehavior pseudocode)_
    - _Preservation: Android app, web app, and environment config loading unchanged_
    - _Requirements: 1.1, 2.2, 2.5, 3.1, 3.4_

  - [x] 3.2 Fix app version validation error
    - Locate app version configuration (check `package.json`, `electron-builder.yml`, or NativePHP config files)
    - If version is hardcoded to "DEBUG", change to valid semver format:
      - Use "1.0.0-dev" for development builds
      - Or use environment variable: `process.env.npm_package_version` or `process.env.NATIVEPHP_APP_VERSION`
    - Alternative: Disable auto-updater in development mode:
      - Add configuration in `config/nativephp.php`:
        - `'auto_updater_enabled' => env('NATIVEPHP_AUTO_UPDATER', true)`
      - In Electron main process, conditionally initialize electron-updater:
        - Skip updater initialization if `NATIVEPHP_AUTO_UPDATER=false`
        - Add logging to indicate updater is disabled
    - Update `.env.desktop` or `.env.desktop.example`:
      - Add `NATIVEPHP_AUTO_UPDATER=false` for development
      - Add `NATIVEPHP_APP_VERSION=1.0.0-dev` if using env-based version
    - _Bug_Condition: appVersionInvalid(input) where appVersion = 'DEBUG' AND NOT isSemver(appVersion)_
    - _Expected_Behavior: appVersionValid = true OR autoUpdaterDisabled = true_
    - _Preservation: Production build versioning unchanged_
    - _Requirements: 1.2, 2.3, 3.3_

  - [x] 3.3 Fix GPU process crash
    - Locate Electron app initialization code (main process file)
    - Add GPU hardware acceleration disable logic:
      - Import electron app module: `const { app } = require('electron')`
      - Before `app.on('ready')` event, add conditional disable:
        ```javascript
        if (process.env.ELECTRON_DISABLE_GPU === 'true' || process.env.NODE_ENV === 'development') {
          app.disableHardwareAcceleration();
          console.log('Hardware acceleration disabled for compatibility');
        }
        ```
      - Or add `--disable-gpu` commandline switch to Electron launch args
    - Add configuration option:
      - In `config/nativephp.php`, add:
        ```php
        'electron' => [
            'disable_gpu' => env('ELECTRON_DISABLE_GPU', false),
        ],
        ```
      - Pass this config to Electron process as environment variable
    - Update `.env.desktop` or `.env.desktop.example`:
      - Add `ELECTRON_DISABLE_GPU=true` for development or problematic systems
      - Add comment explaining when to use this setting
    - Add graceful error handling for GPU crashes:
      - Listen for `child-process-gone` event on app
      - If reason is 'crashed' and type is 'GPU', log warning and suggest disabling GPU
    - _Bug_Condition: gpuProcessCrashes(input) where gpuProcessExitCode = -1073741515_
    - _Expected_Behavior: gpuCrash = false (uses software rendering fallback)_
    - _Preservation: Production GPU settings and mobile app rendering unchanged_
    - _Requirements: 1.3, 2.4, 3.2_

  - [x] 3.4 Add comprehensive error logging and user feedback
    - Enhance error messages for all three failure modes:
      - PHP spawn error: "Failed to start PHP server. Please check your PHP installation."
      - Version error: "Auto-updater disabled due to invalid version in development mode."
      - GPU error: "Hardware acceleration disabled due to graphics driver issues."
    - Add startup progress indicators:
      - Log each initialization step (PHP spawn, version check, GPU init)
      - Show loading window or splash screen during startup
      - Display specific error in dialog if startup fails
    - Add troubleshooting guide to error dialogs:
      - For PHP error: suggest checking PATH, installing PHP, or setting NATIVEPHP_PHP_BINARY
      - For GPU error: suggest setting ELECTRON_DISABLE_GPU=true
      - Include link to documentation or README
    - _Expected_Behavior: Clear error messages and troubleshooting guidance_
    - _Preservation: Existing logging for other platforms unchanged_
    - _Requirements: 2.5_

  - [x] 3.5 Verify bug condition exploration test now passes
    - **Property 1: Expected Behavior** - Desktop App Launches Successfully
    - **IMPORTANT**: Re-run the SAME test from task 1 - do NOT write a new test
    - The test from task 1 encodes the expected behavior
    - When this test passes, it confirms the expected behavior is satisfied
    - Run bug condition exploration test from step 1:
      - Launch desktop app via `php artisan native:serve`
      - Verify PHP binary spawns successfully (no errno -4094)
      - Verify app version is valid or auto-updater disabled (no "DEBUG" error)
      - Verify GPU doesn't crash (no exit_code=-1073741515)
      - Verify Electron window appears and app is usable
    - **EXPECTED OUTCOME**: Test PASSES (confirms all three bugs are fixed)
    - Document that all counterexamples from task 1 are now resolved
    - _Requirements: 2.1, 2.2, 2.3, 2.4, 2.5 (Expected Behavior Properties from design)_

  - [ ] 3.6 Verify preservation tests still pass
    - **Property 2: Preservation** - Non-Desktop Functionality Unchanged
    - **IMPORTANT**: Re-run the SAME tests from task 2 - do NOT write new tests
    - Run preservation property tests from step 2:
      - Test `php artisan native:run` for Android app
      - Test `php artisan serve` for web app
      - Test environment config loading for mobile
      - Test hot reload in development
      - Test existing features (auth, database, file upload) in non-desktop modes
    - **EXPECTED OUTCOME**: Tests PASS (confirms no regressions)
    - Confirm all tests still pass after fix
    - Verify property-based tests generated many test cases and all passed
    - _Requirements: 3.1, 3.2, 3.3, 3.4, 3.5 (Preservation Requirements from design)_

- [x] 4. Checkpoint - Ensure all tests pass and desktop app launches
  - Run full test suite (unit tests, property-based tests, integration tests)
  - Manually test desktop app launch via `php artisan native:serve`
  - Verify Electron window appears without errors
  - Verify all three previous error messages no longer appear in logs
  - Test basic app functionality after launch (navigation, auth, etc.)
  - Verify Android app still works via `php artisan native:run`
  - Verify web app still works via `php artisan serve`
  - Ask user if any issues arise or if additional testing is needed
