<?php

namespace App\Traits\NativePHP;

use Illuminate\Support\Facades\Process;

use function Laravel\Prompts\error;
use function Laravel\Prompts\note;
use function Laravel\Prompts\outro;
use function Laravel\Prompts\warning;

/**
 * Local override of Native\Mobile\Traits\RunsAndroid.
 *
 * Changes vs vendor:
 *  - runTheAndroidBuild: adb install uses ->timeout(0) — no timeout limit.
 *    Large APKs (1GB+) need more than the default 60s.
 */
trait RunsAndroid
{
    /**
     * Override: run Gradle build + adb install with no timeout.
     */
    private function runTheAndroidBuild(?string $targetDeviceId): void
    {
        $androidPath = base_path('nativephp/android');
        $gradleWrapper = PHP_OS_FAMILY === 'Windows' ? 'gradlew.bat' : './gradlew';

        if (PHP_OS_FAMILY !== 'Windows') {
            $gradlePath = $androidPath.DIRECTORY_SEPARATOR.'gradlew';
            if (! is_executable($gradlePath)) {
                chmod($gradlePath, 0755);
            }
        }

        $gradleTask = match ($this->buildType) {
            'debug' => 'assembleDebug',
            'release' => 'assembleRelease',
            'bundle' => 'bundleRelease',
            default => throw new \Exception("Unknown build type: $this->buildType"),
        };

        $verbose = $this->getOutput()->isVerbose();

        $this->components->twoColumnDetail('Build type', $this->buildType);
        $this->components->twoColumnDetail('App version', config('nativephp.version', 'Not set'));
        $this->newLine();

        $this->logToFile('--- Starting Gradle Build ---');
        $this->logToFile("Gradle wrapper: $gradleWrapper");
        $this->logToFile("Gradle task: $gradleTask");
        $this->logToFile('Verbose mode: '.($verbose ? 'enabled' : 'disabled'));

        $buildSuccessful = false;

        if (PHP_OS_FAMILY === 'Windows') {
            $cmd = "cd /d \"$androidPath\" && $gradleWrapper $gradleTask";
            $this->logToFile("Windows command: $cmd");
            $exitCode = 0;
            passthru($cmd, $exitCode);
            $this->logToFile("Windows build exit code: $exitCode");
            $buildSuccessful = ($exitCode === 0);
        } else {
            $process = Process::path($androidPath)->timeout(0); // no timeout

            if (! $this->option('no-tty')) {
                $process->tty();
            }

            $result = $process->run("$gradleWrapper $gradleTask", function ($type, $output) {
                file_put_contents($this->androidLogPath, $output, FILE_APPEND);
            });

            if (! $result->successful()) {
                $this->logToFile('ERROR: Gradle build failed with exit code: '.$result->exitCode());
                error('Gradle build failed');
                note("Check the build log for details: {$this->androidLogPath}");

                return;
            }

            $buildSuccessful = $result->successful();
        }

        if (! $buildSuccessful) {
            $this->logToFile('ERROR: Build failed');
            error('Build failed.');
            note("Check the build log for details: {$this->androidLogPath}");

            return;
        }

        $this->logToFile('Gradle build completed successfully');

        if ($this->buildType === 'debug') {
            $appId = config('nativephp.app_id');
            $mainActivity = 'com.nativephp.mobile.ui.MainActivity';
            $adbCommand = PHP_OS_FAMILY === 'Windows' ? 'adb.exe' : 'adb';

            $apkPath = base_path('nativephp/android/app/build/outputs/apk/debug/app-debug.apk');
            $installCmd = "$adbCommand -s $targetDeviceId install -r \"$apkPath\"";
            $this->logToFile("Installing APK: $installCmd");

            // ── KEY CHANGE: timeout(0) — no limit for large APK installs ──
            $installResult = Process::timeout(0)->run($installCmd);
            // ──────────────────────────────────────────────────────────────

            if (! $installResult->successful()) {
                $this->logToFile('ERROR: APK installation failed');
                $this->logToFile($installResult->output());
                $this->logToFile($installResult->errorOutput());
                error('APK installation failed');
                note($installResult->errorOutput() ?: $installResult->output());
                note('Try freeing up space on the device or uninstalling old apps.');

                return;
            }

            $this->logToFile('APK installed on device');

            $launchCmd = "$adbCommand -s $targetDeviceId shell am start -n $appId/$mainActivity";
            $this->logToFile("Launching app: $launchCmd");

            // No timeout for launch either
            $launchResult = Process::timeout(0)->run($launchCmd);

            if (! $launchResult->successful()) {
                $this->logToFile('ERROR: App launch failed');
                $this->logToFile($launchResult->errorOutput());
                error('App launch failed');
                note($launchResult->errorOutput() ?: $launchResult->output());

                return;
            }

            $this->logToFile('App launched on device');
            outro('App launched!');

            $this->runAndroidPostBuildHooks();

        } else {
            $outputPath = match ($this->buildType) {
                'release' => $this->findReleaseApk(),
                'bundle' => base_path('nativephp/android/app/build/outputs/bundle/release/app-release.aab'),
                default => null,
            };

            if ($outputPath) {
                $outputPath = str_replace(['\\', "\r", "\n"], ['/', '', ''], $outputPath);
            }

            if ($outputPath && file_exists($outputPath)) {
                $fileSize = round(filesize($outputPath) / 1024 / 1024, 2);
                $this->logToFile("Build output: $outputPath");
                $this->logToFile("Output size: {$fileSize} MB");
                $this->components->twoColumnDetail('Output', $outputPath);

                if (PHP_OS_FAMILY === 'Windows') {
                    $windowsPath = str_replace('/', '\\', $outputPath);
                    $windowsPath = escapeshellarg($windowsPath);
                    exec("explorer.exe /select,$windowsPath");
                } elseif (PHP_OS_FAMILY === 'Darwin') {
                    exec("open -R \"$outputPath\"");
                } elseif (PHP_OS_FAMILY === 'Linux') {
                    if (shell_exec('which xdg-open')) {
                        exec('xdg-open "'.dirname($outputPath).'"');
                    }
                }
            } else {
                warning("Could not locate output file for build type: $this->buildType");
            }

            outro('Build complete!');

            $this->runAndroidPostBuildHooks();
        }
    }
}
