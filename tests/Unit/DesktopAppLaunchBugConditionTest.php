<?php

/**
 * Bug Condition Exploration Test for Desktop App Launch Failures
 *
 * **CRITICAL**: This test MUST FAIL on unfixed code - failure confirms the bugs exist
 * **DO NOT attempt to fix the test or the code when it fails**
 * **NOTE**: This test encodes the expected behavior - it will validate the fix when it passes after implementation
 *
 * **Property 1: Bug Condition** - Desktop App Launch Failures
 *
 * **Validates: Requirements 1.1, 1.2, 1.3, 1.4**
 *
 * This test verifies three critical bug conditions that prevent the desktop app from launching:
 * 1. PHP binary spawn failures (errno -4094 "spawn UNKNOWN")
 * 2. Invalid app version "DEBUG" (not semver compliant)
 * 3. GPU process crashes (exit_code=-1073741515)
 *
 * The test assertions match the Expected Behavior Properties from design:
 * - ASSERT phpSpawned = true (will fail - confirms spawn bug)
 * - ASSERT appVersionValid = true OR autoUpdaterDisabled = true (will fail - confirms version bug)
 * - ASSERT gpuCrash = false (will fail - confirms GPU bug)
 * - ASSERT windowVisible = true (will fail - confirms app doesn't launch)
 *
 * **EXPECTED OUTCOME**: Test FAILS (this is correct - it proves the bugs exist)
 *
 * When this test passes after fixes are implemented, it confirms all three bugs are resolved.
 */

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;

describe('Property 1: Bug Condition - Desktop App Launch Failures', function () {

    // ── Helper Functions ─────────────────────────────────────────────────────

    /**
     * Get the path to the NativePHP Electron package.json file
     */
    function getElectronPackageJsonPath(): string
    {
        return base_path('vendor/nativephp/electron/resources/js/package.json');
    }

    /**
     * Read and parse the Electron package.json file
     */
    function getElectronPackageJson(): array
    {
        $path = getElectronPackageJsonPath();

        if (! File::exists($path)) {
            throw new \RuntimeException("Electron package.json not found at: {$path}");
        }

        $contents = File::get($path);
        $json = json_decode($contents, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException('Failed to parse Electron package.json: ' . json_last_error_msg());
        }

        return $json;
    }

    /**
     * Check if a version string is valid semver format (X.Y.Z or X.Y.Z-suffix)
     */
    function isValidSemver(string $version): bool
    {
        // Semver pattern: major.minor.patch with optional pre-release and build metadata
        // Examples: 1.0.0, 1.0.0-dev, 1.0.0-alpha.1, 1.0.0+build.123
        $semverPattern = '/^(0|[1-9]\d*)\.(0|[1-9]\d*)\.(0|[1-9]\d*)(?:-((?:0|[1-9]\d*|\d*[a-zA-Z-][0-9a-zA-Z-]*)(?:\.(?:0|[1-9]\d*|\d*[a-zA-Z-][0-9a-zA-Z-]*))*))?(?:\+([0-9a-zA-Z-]+(?:\.[0-9a-zA-Z-]+)*))?$/';

        return preg_match($semverPattern, $version) === 1;
    }

    /**
     * Check if auto-updater is disabled in environment config
     */
    function isAutoUpdaterDisabled(): bool
    {
        // Check if NATIVEPHP_AUTO_UPDATER is explicitly set to false
        $envValue = env('NATIVEPHP_AUTO_UPDATER');

        // Also check alternative environment variable names
        if ($envValue === null) {
            $envValue = env('ELECTRON_AUTO_UPDATER');
        }

        if ($envValue === null) {
            $envValue = env('DESKTOP_ENABLE_AUTO_UPDATE');
        }

        // Auto-updater is disabled if explicitly set to false, 'false', '0', or 0
        return $envValue === false || $envValue === 'false' || $envValue === '0' || $envValue === 0;
    }

    /**
     * Check if GPU hardware acceleration is disabled in environment config
     */
    function isGpuDisabled(): bool
    {
        // Check if ELECTRON_DISABLE_GPU is set to true
        $envValue = env('ELECTRON_DISABLE_GPU');

        return $envValue === true || $envValue === 'true' || $envValue === '1' || $envValue === 1;
    }

    /**
     * Check if PHP binary path is properly configured
     */
    function isPhpBinaryConfigured(): bool
    {
        // Check if NATIVEPHP_PHP_BINARY is set
        $phpBinaryPath = env('NATIVEPHP_PHP_BINARY');

        if ($phpBinaryPath !== null && ! empty($phpBinaryPath)) {
            // If explicitly set, check if it exists
            return File::exists($phpBinaryPath);
        }

        // If not explicitly set, check if PHP is in PATH
        $result = Process::run('where php');

        return $result->successful() && ! empty(trim($result->output()));
    }

    /**
     * Simulate PHP spawn attempt (without actually spawning)
     */
    function canSpawnPhp(): bool
    {
        // Check if PHP binary is available and executable
        $phpBinaryPath = env('NATIVEPHP_PHP_BINARY');

        if ($phpBinaryPath !== null && ! empty($phpBinaryPath)) {
            // Explicitly configured path
            if (! File::exists($phpBinaryPath)) {
                return false;
            }

            // Check if file is executable (on Unix-like systems)
            if (DIRECTORY_SEPARATOR === '/' && ! is_executable($phpBinaryPath)) {
                return false;
            }

            return true;
        }

        // Check if PHP is in system PATH
        $result = Process::run('php --version');

        return $result->successful();
    }

    // ── Bug Condition 1: PHP Binary Spawn Error ─────────────────────────────

    test('Bug Condition 1.1: PHP binary spawn attempt fails (errno -4094)', function () {
        // **Validates: Requirements 1.1, 2.2, 2.5**
        //
        // **Bug Condition**: phpSpawnFails(input) where spawnError = 'UNKNOWN' AND errno = -4094
        // **Expected Behavior**: phpSpawned = true (will fail - confirms spawn bug)
        //
        // This test checks if the PHP binary can be spawned successfully.
        // On unfixed code, this will likely fail because:
        // - PHP binary path is not configured
        // - PHP binary path is incorrect
        // - PHP is not in system PATH
        //
        // **EXPECTED TO FAIL on unfixed code**

        $canSpawn = canSpawnPhp();

        // Document the counterexample
        if (! $canSpawn) {
            $phpBinaryPath = env('NATIVEPHP_PHP_BINARY');
            $pathResult = Process::run('where php');

            dump([
                'counterexample' => 'PHP Spawn Failure',
                'error_type' => 'spawn UNKNOWN',
                'errno' => -4094,
                'php_binary_path_env' => $phpBinaryPath ?: 'not set',
                'php_in_path' => $pathResult->successful() ? 'yes' : 'no',
                'path_output' => trim($pathResult->output()) ?: 'empty',
                'diagnosis' => 'PHP binary not found or not accessible',
            ]);
        }

        // ASSERTION: phpSpawned = true
        // This assertion encodes the expected behavior
        expect($canSpawn)->toBeTrue(
            'PHP binary spawn should succeed. ' .
            'COUNTEREXAMPLE: PHP binary not found (errno -4094 "spawn UNKNOWN" expected). ' .
            'This failure confirms the bug exists.'
        );
    });

    test('Bug Condition 1.2: PHP binary path is properly configured', function () {
        // **Validates: Requirements 1.1, 2.2, 2.5**
        //
        // **Bug Condition**: PHP binary path is missing or incorrect
        // **Expected Behavior**: PHP binary path is configured via env var or available in PATH
        //
        // **EXPECTED TO FAIL on unfixed code**

        $isConfigured = isPhpBinaryConfigured();

        // Document the counterexample
        if (! $isConfigured) {
            dump([
                'counterexample' => 'PHP Binary Not Configured',
                'nativephp_php_binary' => env('NATIVEPHP_PHP_BINARY') ?: 'not set',
                'diagnosis' => 'PHP binary path not configured and PHP not in system PATH',
            ]);
        }

        // ASSERTION: PHP binary is accessible
        expect($isConfigured)->toBeTrue(
            'PHP binary should be configured via NATIVEPHP_PHP_BINARY env var or available in system PATH. ' .
            'COUNTEREXAMPLE: PHP binary not configured. ' .
            'This failure confirms the bug exists.'
        );
    });

    // ── Bug Condition 2: Invalid App Version ────────────────────────────────

    test('Bug Condition 2.1: App version validation rejects "DEBUG" as invalid semver', function () {
        // **Validates: Requirements 1.2, 2.3**
        //
        // **Bug Condition**: appVersionInvalid(input) where appVersion = 'DEBUG' AND NOT isSemver(appVersion)
        // **Expected Behavior**: appVersionValid = true OR autoUpdaterDisabled = true
        //
        // This test checks if the app version in package.json is valid semver format.
        // On unfixed code, this will fail because the version is set to "DEBUG".
        //
        // **EXPECTED TO FAIL on unfixed code**

        $packageJson = getElectronPackageJson();
        $appVersion = $packageJson['version'] ?? null;

        expect($appVersion)->not->toBeNull('App version should be defined in package.json');

        $isValid = isValidSemver($appVersion);

        // Document the counterexample
        if (! $isValid) {
            dump([
                'counterexample' => 'Invalid App Version',
                'actual_version' => $appVersion,
                'expected_format' => 'X.Y.Z (semver compliant)',
                'error_message' => "App version is not a valid semver version: '{$appVersion}'",
                'diagnosis' => 'electron-updater will reject this version',
            ]);
        }

        // ASSERTION: appVersionValid = true
        // This assertion encodes the expected behavior
        expect($isValid)->toBeTrue(
            'App version should be valid semver format (e.g., "1.0.0", "1.0.0-dev"). ' .
            "COUNTEREXAMPLE: App version is '{$appVersion}' (not semver compliant). " .
            'This failure confirms the bug exists.'
        );
    });

    test('Bug Condition 2.2: Auto-updater is disabled OR app version is valid', function () {
        // **Validates: Requirements 1.2, 2.3**
        //
        // **Expected Behavior**: appVersionValid = true OR autoUpdaterDisabled = true
        //
        // This test checks the full expected behavior: either the app version is valid,
        // or the auto-updater is disabled (making version validation irrelevant).
        //
        // **EXPECTED TO FAIL on unfixed code** (version invalid AND updater not disabled)

        $packageJson = getElectronPackageJson();
        $appVersion = $packageJson['version'] ?? null;
        $versionValid = isValidSemver($appVersion);
        $updaterDisabled = isAutoUpdaterDisabled();

        // Document the counterexample
        if (! $versionValid && ! $updaterDisabled) {
            dump([
                'counterexample' => 'Version Invalid AND Auto-Updater Enabled',
                'app_version' => $appVersion,
                'version_valid' => $versionValid,
                'updater_disabled' => $updaterDisabled,
                'nativephp_auto_updater_env' => env('NATIVEPHP_AUTO_UPDATER') ?? 'not set',
                'electron_auto_updater_env' => env('ELECTRON_AUTO_UPDATER') ?? 'not set',
                'desktop_enable_auto_update_env' => env('DESKTOP_ENABLE_AUTO_UPDATE') ?? 'not set',
                'diagnosis' => 'electron-updater will fail to initialize with invalid version',
            ]);
        }

        // ASSERTION: appVersionValid = true OR autoUpdaterDisabled = true
        // This assertion encodes the expected behavior (either condition satisfies)
        expect($versionValid || $updaterDisabled)->toBeTrue(
            'Either app version should be valid semver OR auto-updater should be disabled. ' .
            "COUNTEREXAMPLE: App version is '{$appVersion}' (invalid) AND auto-updater is not disabled. " .
            'This failure confirms the bug exists.'
        );
    });

    // ── Bug Condition 3: GPU Process Crash ──────────────────────────────────

    test('Bug Condition 3.1: GPU hardware acceleration crash is prevented', function () {
        // **Validates: Requirements 1.3, 2.4**
        //
        // **Bug Condition**: gpuProcessCrashes(input) where gpuProcessExitCode = -1073741515
        // **Expected Behavior**: gpuCrash = false (uses software rendering fallback)
        //
        // This test checks if GPU hardware acceleration is disabled to prevent crashes.
        // On unfixed code, this will fail because GPU acceleration is enabled by default.
        //
        // Note: We cannot actually spawn Electron to test GPU crashes in a unit test,
        // so we check if the configuration prevents GPU crashes (by disabling GPU).
        //
        // **EXPECTED TO FAIL on unfixed code**

        $gpuDisabled = isGpuDisabled();

        // Document the counterexample
        if (! $gpuDisabled) {
            dump([
                'counterexample' => 'GPU Hardware Acceleration Enabled',
                'electron_disable_gpu' => env('ELECTRON_DISABLE_GPU') ?? 'not set',
                'expected_gpu_crash_exit_code' => -1073741515,
                'diagnosis' => 'GPU process will crash repeatedly with exit_code=-1073741515',
                'error_message' => "GPU process isn't usable. Goodbye.",
            ]);
        }

        // ASSERTION: gpuCrash = false (GPU is disabled or has fallback)
        // This assertion encodes the expected behavior
        expect($gpuDisabled)->toBeTrue(
            'GPU hardware acceleration should be disabled via ELECTRON_DISABLE_GPU env var. ' .
            'COUNTEREXAMPLE: GPU acceleration is enabled, which will cause crashes (exit_code=-1073741515). ' .
            'This failure confirms the bug exists.'
        );
    });

    // ── Integration: All Three Bug Conditions ───────────────────────────────

    test('Bug Condition Integration: Desktop app launch fails due to all three bugs', function () {
        // **Validates: Requirements 1.1, 1.2, 1.3, 1.4, 2.1**
        //
        // **Full Bug Condition**: isBugCondition(input)
        // **Expected Behavior**: All three conditions must be satisfied for successful launch
        //
        // This test verifies that ALL three bug conditions exist on unfixed code:
        // 1. PHP spawn fails
        // 2. App version is invalid (and auto-updater not disabled)
        // 3. GPU crashes (GPU not disabled)
        //
        // **EXPECTED TO FAIL on unfixed code**

        $phpCanSpawn = canSpawnPhp();
        $packageJson = getElectronPackageJson();
        $appVersion = $packageJson['version'] ?? null;
        $versionValid = isValidSemver($appVersion);
        $updaterDisabled = isAutoUpdaterDisabled();
        $gpuDisabled = isGpuDisabled();

        $allConditionsMet = $phpCanSpawn && ($versionValid || $updaterDisabled) && $gpuDisabled;

        // Document the full counterexample
        if (! $allConditionsMet) {
            dump([
                'counterexample' => 'Desktop App Launch Failure - Multiple Bugs',
                'bug_1_php_spawn' => [
                    'can_spawn' => $phpCanSpawn,
                    'status' => $phpCanSpawn ? 'OK' : 'FAIL (errno -4094)',
                ],
                'bug_2_app_version' => [
                    'version' => $appVersion,
                    'valid' => $versionValid,
                    'updater_disabled' => $updaterDisabled,
                    'status' => ($versionValid || $updaterDisabled) ? 'OK' : 'FAIL (invalid semver)',
                ],
                'bug_3_gpu_crash' => [
                    'gpu_disabled' => $gpuDisabled,
                    'status' => $gpuDisabled ? 'OK' : 'FAIL (GPU crash expected)',
                ],
                'overall_status' => 'LAUNCH FAILED',
                'window_visible' => false,
                'application_usable' => false,
            ]);
        }

        // ASSERTION: windowVisible = true AND applicationUsable = true
        // This requires all three conditions to be satisfied
        expect($allConditionsMet)->toBeTrue(
            'Desktop app should launch successfully with: ' .
            '(1) PHP binary spawnable, ' .
            '(2) valid app version OR auto-updater disabled, ' .
            '(3) GPU disabled or fallback enabled. ' .
            'COUNTEREXAMPLE: One or more conditions not met, app fails to launch. ' .
            'This failure confirms the bugs exist.'
        );
    });

    // ── Documentation of Bugs Found ──────────────────────────────────────────

    test('Documentation: Bug counterexamples are captured', function () {
        // **Validates: Requirements 1.1, 1.2, 1.3, 1.4**
        //
        // This test always passes but documents the counterexamples found during test execution.
        // Run this test suite to see all counterexamples in the test output.

        $packageJson = getElectronPackageJson();

        $bugs = [
            'Bug 1: PHP Spawn Error' => [
                'description' => 'PHP binary spawn fails with errno -4094 "spawn UNKNOWN"',
                'can_spawn_php' => canSpawnPhp(),
                'php_binary_env' => env('NATIVEPHP_PHP_BINARY') ?? 'not set',
            ],
            'Bug 2: Invalid App Version' => [
                'description' => 'App version "DEBUG" is not valid semver format',
                'app_version' => $packageJson['version'] ?? 'not set',
                'is_valid_semver' => isValidSemver($packageJson['version'] ?? ''),
                'auto_updater_disabled' => isAutoUpdaterDisabled(),
            ],
            'Bug 3: GPU Process Crash' => [
                'description' => 'GPU process crashes with exit_code=-1073741515',
                'gpu_disabled' => isGpuDisabled(),
                'electron_disable_gpu_env' => env('ELECTRON_DISABLE_GPU') ?? 'not set',
            ],
        ];

        dump([
            'test_suite' => 'Desktop App Launch Bug Condition Exploration',
            'expected_outcome' => 'Tests FAIL (confirms bugs exist)',
            'bugs_found' => $bugs,
            'next_steps' => [
                'Task 2: Write preservation property tests (before implementing fix)',
                'Task 3: Implement fixes for all three bugs',
                'Task 3.5: Re-run this test suite (should PASS after fixes)',
            ],
        ]);

        // This test always passes - it just documents the bugs
        expect(true)->toBeTrue('Bug documentation complete');
    });
});
