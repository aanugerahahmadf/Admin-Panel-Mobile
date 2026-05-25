<?php

namespace App\Traits\NativePHP;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;

/**
 * Local override of Native\Mobile\Traits\PreparesBuild.
 *
 * Changes vs vendor:
 *  - prepareLaravelBundle: temp dir reads NATIVEPHP_BUILD_TEMP_DIR env (default D:\temp)
 *    instead of hardcoded C:\temp on Windows.
 */
trait PreparesBuild
{
    /**
     * Prepare Laravel bundle — override to use configurable temp dir on Windows.
     */
    /**
     * Prepare Laravel bundle — override to use configurable temp dir on Windows and robust zip unlinking.
     */
    protected function prepareLaravelBundle(bool $excludeDevDependencies = true): void
    {
        $excludeDevDependencies = true; // Force-exclude dev dependencies to prevent massive APK size (e.g. PHPUnit)
        $this->logToFile('Preparing Laravel bundle...');

        $source = realpath(base_path());
        $destinationZip = base_path('nativephp/android/app/src/main/assets/laravel_bundle.zip');

        $this->logToFile("  Source: $source");
        $this->logToFile("  Destination: $destinationZip");

        // ── KEY CHANGE: read from env instead of hardcoded C:\temp ──────────
        $tempDir = PHP_OS_FAMILY === 'Windows'
            ? (env('NATIVEPHP_BUILD_TEMP_DIR', 'D:\\Temp').'\\')
            : base_path('nativephp/android/laravel');
        // ────────────────────────────────────────────────────────────────────

        $this->logToFile("  Temp directory: $tempDir");

        if (is_dir($tempDir)) {
            $this->logToFile('  Removing existing temp directory...');
            $this->removeDirectory($tempDir);
        }
        File::ensureDirectoryExists($tempDir);

        try {
            if (file_exists($destinationZip)) {
                $this->logToFile('  Removing existing bundle zip...');

                $unlinked = false;
                for ($i = 0; $i < 5; $i++) {
                    if (@unlink($destinationZip)) {
                        $unlinked = true;
                        break;
                    }
                    $this->logToFile('  Attempt '.($i + 1).' to remove bundle zip failed, retrying in 200ms...');
                    usleep(200000); // 200ms
                }

                if (! $unlinked) {
                    $this->logToFile('  Failed to remove bundle zip. Attempting to stop Gradle daemon to release locks...');
                    if (PHP_OS_FAMILY === 'Windows') {
                        $androidPath = base_path('nativephp/android');
                        @exec("cd /d \"$androidPath\" && gradlew.bat --stop");
                        usleep(500000); // 500ms
                    }

                    if (! @unlink($destinationZip)) {
                        $this->logToFile('WARNING: Could not remove existing bundle zip. 7-Zip will attempt to overwrite/update it.');
                    }
                }
            }

            $excludedDirs = match (PHP_OS_FAMILY) {
                'Windows' => array_merge(config('nativephp.cleanup_exclude_files'), ['.git', 'node_modules', 'nativephp', 'vendor/nativephp/mobile/resources']),
                'Linux' => array_merge(config('nativephp.cleanup_exclude_files'), ['.git', 'node_modules', 'nativephp/ios', 'nativephp/android']),
                'Darwin' => array_merge(config('nativephp.cleanup_exclude_files'), ['.git', 'node_modules', 'nativephp/ios', 'nativephp/android']),
                default => config('nativephp.cleanup_exclude_files'),
            };

            $this->logToFile('  Excluded directories: '.implode(', ', $excludedDirs));

            $srcDir = base_path('vendor/nativephp/mobile/bootstrap/android');

            $this->logToFile('  Copying Laravel source...');
            $this->components->task('Copying Laravel source', fn () => $this->platformOptimizedCopy($source, $tempDir, $excludedDirs));

            $composerArgs = $excludeDevDependencies ? '--no-dev --no-interaction' : '--no-interaction';

            $this->logToFile('  Installing Composer dependencies'.($excludeDevDependencies ? ' (--no-dev)' : '').'...');
            $this->components->task('Installing Composer dependencies', function () use ($tempDir, $composerArgs) {
                $result = Process::path($tempDir)
                    ->timeout(0)
                    ->run("composer install {$composerArgs}");

                $this->logToFile($result->output());
                if ($result->errorOutput()) {
                    $this->logToFile($result->errorOutput());
                }

                return $result->successful();
            });

            $this->logToFile('  Optimizing autoloader...');
            $this->components->task('Optimizing autoloader', function () use ($tempDir) {
                $result = Process::path($tempDir)
                    ->timeout(0)
                    ->run('composer dump-autoload --optimize --classmap-authoritative');

                $this->logToFile($result->output());
                if ($result->errorOutput()) {
                    $this->logToFile($result->errorOutput());
                }

                return $result->successful();
            });

            $version = config('nativephp.version', now()->format('Ymd-His'));
            $this->logToFile("  Writing version file: $version");
            file_put_contents($tempDir.DIRECTORY_SEPARATOR.'.version', $version.PHP_EOL);

            if (file_exists($source.DIRECTORY_SEPARATOR.'.env')) {
                $this->logToFile('  Copying and cleaning .env file...');
                $envPath = $tempDir.DIRECTORY_SEPARATOR.'.env';
                copy($source.DIRECTORY_SEPARATOR.'.env', $envPath);
                $this->cleanEnvFile($envPath);
            }

            $artisanPhp = "{$srcDir}/artisan.php";
            if (file_exists($artisanPhp)) {
                $this->logToFile('  Copying artisan.php bootstrap...');
                File::copy($artisanPhp, "{$tempDir}/artisan.php");
            }

            $this->logToFile('  Creating bundle archive...');
            if (PHP_OS_FAMILY === 'Windows') {
                $this->logToFile('  Windows detected: sleeping 2 seconds to let filesystem and antivirus settle...');
                sleep(2);
            }
            $this->components->task('Creating bundle archive', fn () => $this->createZipBundle($tempDir, $destinationZip, $excludedDirs));

            if (! file_exists($destinationZip) || filesize($destinationZip) <= 1000) {
                $this->logToFile('ERROR: Failed to create valid zip file');
                \Laravel\Prompts\error('Failed to create valid zip file.');
                exit(1);
            }

            // Write bundle_meta.json alongside the ZIP
            $assetsDir = dirname($destinationZip);
            $bifrostAppId = null;
            if (file_exists($source.DIRECTORY_SEPARATOR.'.env')) {
                $envContent = file_get_contents($source.DIRECTORY_SEPARATOR.'.env');
                if (preg_match('/BIFROST_APP_ID=(.+)/', $envContent, $matches)) {
                    $bifrostAppId = trim($matches[1]);
                }
            }
            $bundleMeta = json_encode([
                'version' => $version,
                'bifrost_app_id' => $bifrostAppId,
                'runtime_mode' => config('nativephp.runtime.mode', 'persistent'),
            ], JSON_PRETTY_PRINT);
            file_put_contents($assetsDir.DIRECTORY_SEPARATOR.'bundle_meta.json', $bundleMeta);

            $runtimeMode = config('nativephp.runtime.mode', 'persistent');
            $this->logToFile("  Written bundle_meta.json: version=$version, bifrost=".($bifrostAppId ?? 'null').", runtime_mode=$runtimeMode");

            $sizeMB = round(filesize($destinationZip) / 1024 / 1024, 2);
            $this->logToFile("  Bundle size: {$sizeMB} MB");
            $this->components->twoColumnDetail('Bundle size', "{$sizeMB} MB");

        } finally {
            $this->logToFile('  Cleaning up temp directory...');
            $this->removeDirectory($tempDir);
        }
    }

    /**
     * Create ZIP bundle with cross-platform support — customized with -ssw switch on Windows.
     */
    protected function createZipBundle(string $source, string $destination, array $excludedDirs = []): void
    {
        if (PHP_OS_FAMILY === 'Windows') {
            $sevenZip = config('nativephp.android.7zip-location');
            if (! file_exists($sevenZip)) {
                \Laravel\Prompts\error("7-Zip not found at: $sevenZip");
                exit(1);
            }

            // Added -ssw switch to allow 7-zip to read files locked/open for writing (e.g. by antivirus or other processes)
            $cmd = "\"$sevenZip\" a -tzip -ssw \"$destination\" \"$source\\*\" -xr!node_modules";
            exec($cmd, $output, $code);

            if ($code !== 0 && $code !== 1) {
                \Laravel\Prompts\error("7-Zip failed with exit code $code");
                exit(1);
            }

            if ($code === 1) {
                $this->logToFile('  7-Zip completed with warnings (non-fatal, archive successfully created). Continuing...');
            }

            return;
        }

        $zip = new \ZipArchive;
        $result = $zip->open($destination, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
        if ($result !== true) {
            \Laravel\Prompts\error("Cannot create zip file at: $destination");
            exit(1);
        }

        $this->addDirectoryToZip($zip, $source, '', $excludedDirs);

        $requiredDirs = [
            'bootstrap/cache',
            'storage/framework/cache',
            'storage/framework/sessions',
            'storage/framework/views',
        ];

        foreach ($requiredDirs as $dir) {
            if (! $zip->statName($dir)) {
                $zip->addEmptyDir($dir);
            }
        }

        $closeResult = $zip->close();
        if (! $closeResult) {
            \Laravel\Prompts\error('Failed to close ZIP file properly');
            exit(1);
        }
    }

    /**
     * Install Android icon — overridden to handle potential write/permission locks.
     */
    public function installAndroidIcon(): void
    {
        $this->logToFile('Installing Android icon...');
        $iconPath = public_path('icon.png');

        if (! File::exists($iconPath)) {
            $this->logToFile('  No icon.png found at public/icon.png, skipping');

            return;
        }

        $this->logToFile("  Source icon: $iconPath");

        $resDir = base_path('nativephp/android/app/src/main/res/');

        $sizes = [
            'mipmap-mdpi' => 48,
            'mipmap-hdpi' => 72,
            'mipmap-xhdpi' => 96,
            'mipmap-xxhdpi' => 144,
            'mipmap-xxxhdpi' => 192,
        ];

        $adaptiveSizes = [
            'mipmap-mdpi' => 108,
            'mipmap-hdpi' => 162,
            'mipmap-xhdpi' => 216,
            'mipmap-xxhdpi' => 324,
            'mipmap-xxxhdpi' => 432,
        ];

        $targets = [
            'ic_launcher.png',
            'ic_launcher_round.png',
            'ic_launcher_foreground.png',
        ];

        $this->logToFile('  Generating icon sizes: '.implode(', ', array_keys($sizes)));

        foreach ($sizes as $folder => $size) {
            $dstDir = $resDir.$folder;
            File::ensureDirectoryExists($dstDir);

            foreach ($targets as $filename) {
                $dstPath = $dstDir.'/'.$filename;

                $webpPath = str_replace('.png', '.webp', $dstPath);
                if (File::exists($webpPath)) {
                    @unlink($webpPath);
                }

                $targetSize = ($filename === 'ic_launcher_foreground.png') ? $adaptiveSizes[$folder] : $size;

                $this->resizePng($iconPath, $dstPath, $targetSize, $targetSize);
            }
        }

        $this->logToFile('  Android icon installed');
    }

    /**
     * Resize PNG with robust file deleting and retrying for Windows environments.
     */
    private function resizePng(string $src, string $dst, int $width, int $height): void
    {
        $srcImage = imagecreatefrompng($src);
        $srcWidth = imagesx($srcImage);
        $srcHeight = imagesy($srcImage);

        $resized = imagecreatetruecolor($width, $height);
        imagesavealpha($resized, true);
        $transparent = imagecolorallocatealpha($resized, 0, 0, 0, 127);
        imagefill($resized, 0, 0, $transparent);

        $isAndroidForeground = str_contains($dst, 'ic_launcher_foreground');
        // Android adaptive icons: 108dp canvas with 66dp safe zone (61%)
        // Use 0.55 to ensure icon stays within safe zone with padding for all mask shapes
        $scaleFactor = $isAndroidForeground ? 0.69 : 1.0;

        $srcRatio = $srcWidth / $srcHeight;
        $dstRatio = $width / $height;

        if ($srcRatio > $dstRatio) {
            $newWidth = (int) ($width * $scaleFactor);
            $newHeight = (int) (($width * $scaleFactor) / $srcRatio);
            $offsetX = (int) (($width - $newWidth) / 2);
            $offsetY = (int) (($height - $newHeight) / 2);
        } else {
            $newWidth = (int) (($height * $scaleFactor) * $srcRatio);
            $newHeight = (int) ($height * $scaleFactor);
            $offsetX = (int) (($width - $newWidth) / 2);
            $offsetY = (int) (($height - $newHeight) / 2);
        }

        imagecopyresampled(
            $resized, $srcImage,
            $offsetX, $offsetY, 0, 0,
            $newWidth, $newHeight,
            $srcWidth, $srcHeight
        );

        if (file_exists($dst)) {
            @unlink($dst);
        }

        $success = @imagepng($resized, $dst, 0);
        if (! $success) {
            $this->logToFile("  Warning: Failed to write icon to {$dst}. Retrying after short delay...");
            usleep(150000); // 150ms
            if (file_exists($dst)) {
                @unlink($dst);
            }
            imagepng($resized, $dst, 0);
        }

        imagedestroy($resized);
        imagedestroy($srcImage);
    }

    /**
     * Overridden platformOptimizedCopy to fix the Windows directory separation bug
     * where forward slashes prevent Robocopy from excluding directories.
     */
    protected function platformOptimizedCopy(string $source, string $destination, array $excludedDirs = []): void
    {
        if (PHP_OS_FAMILY === 'Windows') {
            // Use robocopy on Windows
            if (! empty($excludedDirs)) {
                $excludeArgs = '';
                foreach ($excludedDirs as $dir) {
                    // Robocopy on Windows requires backslashes for path matching
                    $normalizedDir = str_replace('/', '\\', $dir);
                    $excludeArgs .= " /XD \"{$source}\\{$normalizedDir}\"";
                }
                // Explicitly exclude any nested .git and node_modules directories
                $excludeArgs .= ' /XD .git node_modules';

                $cmd = "robocopy \"{$source}\" \"{$destination}\" /MIR /NFL /NDL /NJH /NJS /NP /R:0 /W:0{$excludeArgs}";
            } else {
                $cmd = "xcopy \"{$source}\\*\" \"{$destination}\\\" /E /I /Y /Q";
            }

            $this->logToFile("  Executing platformOptimizedCopy command: $cmd");
            exec($cmd, $output, $result);

            // Robocopy returns 0-7 as success codes
            if ($result >= 8 && strpos($cmd, 'robocopy') !== false) {
                $this->logToFile("WARNING: robocopy failed with exit code $result");
            }
        } else {
            // Use rsync on Unix-like systems
            if (! empty($excludedDirs)) {
                $excludedDirs[] = 'vendor/*/vendor';
                $excludedDirs[] = 'vendor/nativephp/mobile/vendor';
                $excludeFlags = implode(' ', array_map(fn ($d) => "--exclude='{$d}'", $excludedDirs));
                $cmd = "rsync -aL {$excludeFlags} \"{$source}/\" \"{$destination}/\"";
            } else {
                $cmd = "cp -a \"{$source}/.\" \"{$destination}/\"";
            }
            exec($cmd);
        }
    }
}
