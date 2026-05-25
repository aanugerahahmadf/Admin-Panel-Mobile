<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Native\Mobile\Network;
use Native\Mobile\Providers\CameraServiceProvider;
use Native\Mobile\Providers\DeviceServiceProvider;
use Native\Mobile\Providers\NetworkServiceProvider;
use Native\Mobile\Providers\SystemServiceProvider;
use Native\Mobile\System;
use SRWieZ\NativePHP\Mobile\Screen\ScreenServiceProvider;

class NativeServiceProvider extends ServiceProvider
{
    // ═══════════════════════════════════════════════════════════════════════
    // ENVIRONMENT DETECTION HELPER
    // ═══════════════════════════════════════════════════════════════════════

    /**
     * Returns true if the request is from a mobile device (Native App OR Mobile Browser).
     */
    public static function isAnyMobile(): bool
    {
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';

        return self::isNativeMobile() || preg_match('/Mobile|Android|iPhone|iPad|iPod/i', $userAgent);
    }

    /**
     * Returns true ONLY when running inside a real NativePHP mobile app
     * (Android or iOS), even without NATIVEPHP_RUNNING being set.
     *
     * Detection priority:
     *  1. NATIVEPHP_RUNNING constant (set by NativePHP bootstrapper)
     *  2. NATIVEPHP_RUNNING env var (fallback)
     *  3. No REMOTE_ADDR + non-Windows OS (CLI / embedded PHP server on device)
     */
    public static function isNativeMobile(): bool
    {
        static $result = null;
        if ($result !== null) {
            return $result;
        }

        // 0. Guard: Never treat as mobile during CI or Unit Testing
        if (env('GITHUB_ACTIONS') || app()->runningUnitTests() || env('APP_ENV') === 'testing') {
            return $result = false;
        }

        // 1. Explicit NativePHP constant (most reliable — set by NativePHP bootstrapper)
        if (defined('NATIVEPHP_RUNNING') && constant('NATIVEPHP_RUNNING')) {
            return $result = true;
        }

        // 2. Explicit env flag
        if (env('NATIVEPHP_RUNNING') || env('IS_NATIVE_MOBILE')) {
            return $result = true;
        }

        // 3. NativePHP sets database.default = 'nativephp' (SQLite) on device
        //    Check this BEFORE any DB operations to avoid circular dependency
        $dbDefault = env('DB_CONNECTION', config('database.default', 'sqlite'));
        if ($dbDefault === 'nativephp' || $dbDefault === 'sqlite') {
            // Only treat as mobile if also running on Linux/Darwin (device OS)
            if (PHP_OS_FAMILY === 'Linux' || PHP_OS_FAMILY === 'Darwin') {
                // Ensure we are not on a desktop Linux/Darwin (like CI or Dev Mac)
                // Device detection usually has no REMOTE_ADDR and no SHELL_VERBOSITY
                if (! isset($_SERVER['REMOTE_ADDR']) && ! env('SHELL_VERBOSITY')) {
                    return $result = true;
                }
            }
        }

        // 3b. NATIVE_HOST_IP is set → explicitly configured for mobile (dev sets this)
        //     AND we are NOT on Windows (mobile devices run Linux/Darwin)
        if (env('NATIVE_HOST_IP') && PHP_OS_FAMILY !== 'Windows') {
            return $result = true;
        }

        // 4. Android / iOS WebView & Local embedded environment checks
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $remoteAddr = $_SERVER['REMOTE_ADDR'] ?? null;
        $isCI = env('GITHUB_ACTIONS') || app()->runningUnitTests();
        $isCloud = env('LARAVEL_CLOUD') || env('DOCKER_ENV') || env('APP_ENV') === 'production';

        if (! empty($userAgent)) {
            // Android WebView (sends "wv)" in UA)
            if (preg_match('/Android.*wv\)/i', $userAgent)) {
                return $result = true;
            }

            // iOS WKWebView: Only treat as Native App if:
            //  - Running on Linux or Darwin host (never Windows)
            //  - Not on standard Cloud hosting / CI environments
            //  - Accessing locally (empty Remote Address OR localhost loopback)
            if (preg_match('/iPhone|iPad.*Mobile.*Safari/i', $userAgent) && ! str_contains($userAgent, 'CriOS') && ! str_contains($userAgent, 'FxiOS')) {
                if (PHP_OS_FAMILY !== 'Windows' && ! $isCloud && ! $isCI) {
                    if (! isset($remoteAddr) || in_array($remoteAddr, ['127.0.0.1', '::1', '0:0:0:0:0:0:0:1'])) {
                        return $result = true;
                    }
                }
            }
        }

        // 5. Heuristic: non-Windows OS with no HTTP client or loopback client
        if (PHP_OS_FAMILY !== 'Windows' && ! $isCloud && ! $isCI) {
            // No REMOTE_ADDR → definitely embedded PHP server on device (no incoming network)
            if (! isset($remoteAddr)) {
                return $result = true;
            }
            // REMOTE_ADDR is loopback → local WebView calling embedded PHP server
            if (in_array($remoteAddr, ['127.0.0.1', '::1', '0:0:0:0:0:0:0:1'])) {
                return $result = true;
            }
        }

        return $result = false;
    }

    /**
     * Returns the correct "localhost" equivalent for the current environment:
     */
    public static function mobileHostIp(): string
    {
        static $ip = null;
        if ($ip !== null) {
            return $ip;
        }

        // Allow explicit override via environment variable
        if ($override = env('NATIVE_HOST_IP')) {
            return $ip = $override;
        }

        // Ekstrak dari APP_URL jika diset ke IP LAN atau ngrok (Penting untuk HP FISIK)
        $appUrl = env('APP_URL');
        if ($appUrl && str_starts_with($appUrl, 'http')) {
            $parsedHost = parse_url($appUrl, PHP_URL_HOST);
            if ($parsedHost && !in_array($parsedHost, ['127.0.0.1', 'localhost'])) {
                return $ip = $parsedHost;
            }
        }

        // Android emulator special loopback
        if (PHP_OS_FAMILY === 'Linux') {
            return $ip = '10.0.2.2';
        }

        // iOS simulator / macOS host
        if (PHP_OS_FAMILY === 'Darwin') {
            return $ip = '127.0.0.1';
        }

        return $ip = '127.0.0.1';
    }

    /**
     * Normalize a URL so it works on the current platform.
     * On mobile, replaces 127.0.0.1/localhost with the correct host IP.
     * On web, returns the URL unchanged.
     */
    public static function normalizeUrl(string $url): string
    {
        if (! self::isNativeMobile()) {
            if (app()->runningInConsole() || ! request()) {
                return $url;
            }

            $requestRoot = request()->getSchemeAndHttpHost();
            $urlHost = parse_url($url, PHP_URL_HOST);
            $urlScheme = parse_url($url, PHP_URL_SCHEME);
            $urlPort = parse_url($url, PHP_URL_PORT);
            $appHost = parse_url((string) env('APP_URL'), PHP_URL_HOST);

            if (! $urlHost || ! $urlScheme) {
                return $url;
            }

            $localHosts = array_filter([
                '127.0.0.1',
                'localhost',
                $appHost,
                request()->getHost(),
            ]);

            if (! in_array($urlHost, $localHosts, true)) {
                return $url;
            }

            $sourceRoot = $urlScheme.'://'.$urlHost.($urlPort ? ':'.$urlPort : '');

            return preg_replace('#^'.preg_quote($sourceRoot, '#').'#', $requestRoot, $url) ?: $url;
        }

        $hostIp = self::mobileHostIp();

        return str_replace(
            ['http://127.0.0.1', 'http://localhost', 'https://127.0.0.1', 'https://localhost'],
            ["http://{$hostIp}", "http://{$hostIp}", "https://{$hostIp}", "https://{$hostIp}"],
            $url
        );
    }

    // ═══════════════════════════════════════════════════════════════════════
    // REGISTER
    // ═══════════════════════════════════════════════════════════════════════

    public function register(): void
    {
        // Guard: skip all NativePHP-specific code on Docker/backend/Cloud envs
        if (env('DOCKER_ENV') || env('LARAVEL_CLOUD')) {
            return;
        }

        // Register native singletons only on mobile
        if (self::isNativeMobile()) {
            if (class_exists(Network::class)) {
                $this->app->singleton(Network::class, fn () => new Network);
            }
            if (class_exists(System::class)) {
                $this->app->singleton(System::class, fn () => new System);
            }

            // ── CRITICAL: Switch DB to proxy HERE (register phase) ──────────
            // This MUST happen before boot() so that session, cache, and any
            // middleware that access the DB use the proxy, not 127.0.0.1:3306
            // which does not exist on the mobile device.
            $proxyUrl = env('NATIVE_DB_PROXY_URL',
                rtrim(env('APP_URL', 'http://192.168.100.63:8000'), '/') . '/api/db-proxy'
            );
            $proxySecret = env('NATIVE_DB_PROXY_SECRET', 'nativephp-db-proxy-secret-2024');

            config([
                'database.default'                                          => 'mysql_proxy',
                'database.connections.mysql_proxy.proxy_url'               => $proxyUrl,
                'database.connections.mysql_proxy.proxy_secret'            => $proxySecret,
                'database.connections.mysql_proxy.database'                => env('DB_DATABASE', 'wedding_organizer'),
                // Also switch session/cache to use file driver to avoid DB chicken-egg on boot
                'session.driver'  => 'file',
                'cache.default'   => 'file',
            ]);

            error_log('[NativePHP] register() → DB switched to mysql_proxy. URL: ' . $proxyUrl);
        }
    }

    // ═══════════════════════════════════════════════════════════════════════
    // BOOT
    // ═══════════════════════════════════════════════════════════════════════

    public function boot(): void
    {
        // Guard: skip on Docker / pure backend / Cloud
        if (env('DOCKER_ENV') || env('LARAVEL_CLOUD')) {
            return;
        }

        $isMobile = self::isNativeMobile();

        // ── 1. RESOLVE HOST IPs ───────────────────────────────────────────
        $hostIp = self::mobileHostIp();           // e.g. 10.0.2.2 (Android)
        $serverPort = env('NATIVE_SERVER_PORT', 8000);  // port Laragon/artisan serve

        $dbHost = env('DB_HOST', '127.0.0.1');
        $reverbHost = env('REVERB_HOST', 'localhost');
        $appUrl = env('APP_URL', 'http://127.0.0.1');
        $currentHost = parse_url($appUrl, PHP_URL_HOST) ?? '127.0.0.1'; // default agar tidak undefined

        // Dynamic Host Detection: If accessed via LAN IP, emulator IP, or ngrok
        if (! app()->runningInConsole() && isset($_SERVER['HTTP_HOST'])) {
            $currentHost = $_SERVER['HTTP_HOST'];

            // Handle X-Forwarded headers from ngrok/proxies
            $proto = $_SERVER['HTTP_X_FORWARDED_PROTO'] ?? (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http');
            $host = $_SERVER['HTTP_X_FORWARDED_HOST'] ?? $_SERVER['HTTP_HOST'];

            // If it is mobile, we must NOT override appUrl with 127.0.0.1 or localhost,
            // because we need the app to connect to the developer's Host PC IP.
            // If it is NOT mobile (e.g. web browser or desktop app), we should always
            // update appUrl to match the host they are accessing from, so assets load correctly.
            if (! $isMobile || ! in_array(parse_url('http://'.$host, PHP_URL_HOST), ['127.0.0.1', 'localhost'])) {
                $appUrl = "{$proto}://{$host}";
                $hostIp = parse_url($appUrl, PHP_URL_HOST);
                $currentHost = $host;
            }
        }

        if ($isMobile) {
            // Replace "localhost" / "127.0.0.1" with the correct host IP for the platform
            $replace = ['127.0.0.1', 'localhost'];

            if (in_array($dbHost, $replace)) {
                $dbHost = $hostIp;
            }
            if (in_array($reverbHost, $replace)) {
                $reverbHost = $hostIp;
            }

            // Rebuild host PC URL to proxy requests to (preserve port if set)
            $parsedUrl = parse_url($appUrl);
            $scheme = $parsedUrl['scheme'] ?? 'http';
            // Priority: APP_URL port > NATIVE_SERVER_PORT > 8000
            $port = $parsedUrl['port'] ?? $serverPort;

            // Only append port if not standard (80 for http, 443 for https)
            $portSuffix = ($port == 80 || $port == 443) ? '' : ":$port";
            $hostServerUrl = "{$scheme}://{$hostIp}{$portSuffix}";
        } else {
            $hostServerUrl = $appUrl;
        }

        // ── 3. APPLY RUNTIME CONFIG ───────────────────────────────────────
        $runtimeConfig = [
            'app.url' => $appUrl,
            'sanctum.stateful' => array_unique(array_merge(
                explode(',', env('SANCTUM_STATEFUL_DOMAINS', 'localhost,127.0.0.1')),
                [$currentHost ?? '']
            )),

            // Session: use file driver on mobile to avoid proxy loop
            'session.driver' => $isMobile ? 'file' : env('SESSION_DRIVER', 'database'),

            // Database
            'database.connections.mysql.host' => $dbHost,
            'database.connections.mysql.port' => env('DB_PORT', '3306'),
            'database.connections.mysql.database' => env('DB_DATABASE', config('database.connections.mysql.database', 'Wedding_organizer')),
            'database.connections.mysql.username' => env('DB_USERNAME', 'root'),
            'database.connections.mysql.password' => env('DB_PASSWORD', ''),

            // Reverb / Broadcasting
            'reverb.apps.0.host' => $reverbHost,
            'broadcasting.connections.reverb.options.host' => $reverbHost,
            'broadcasting.connections.pusher.options.host' => $reverbHost,

            // AI / CBIR Service Synchronization
            // Optimization: Web/Native should use 127.0.0.1, Mobile Emulator should use $hostIp
            'services.ai_core_url' => $isMobile ? str_replace(['127.0.0.1', 'localhost'], $hostIp, env('AI_CORE_URL', 'http://127.0.0.1:5000')) : env('AI_CORE_URL', 'http://127.0.0.1:5000'),
            'services.cbir_api_url' => $isMobile ? str_replace(['127.0.0.1', 'localhost'], $hostIp, env('CBIR_API_URL', 'http://127.0.0.1:5000')) : env('CBIR_API_URL', 'http://127.0.0.1:5000'),
        ];

        $proxyUrl = "{$hostServerUrl}/api/db-proxy";

        if ($isMobile) {
            $runtimeConfig['database.default'] = 'mysql_proxy';
            $runtimeConfig['database.connections.mysql_proxy.proxy_url'] = $proxyUrl;
            $runtimeConfig['database.connections.mysql_proxy.proxy_secret'] = env('NATIVE_DB_PROXY_SECRET', 'nativephp-db-proxy-secret-2024');
            $runtimeConfig['database.connections.mysql_proxy.database'] = env('DB_DATABASE', config('database.connections.mysql.database', 'Wedding_organizer'));
            // Google OAuth: Alihkan redirect url ke skema deep link aplikasi Android/iOS
            if (env('GOOGLE_MOBILE_REDIRECT_URL')) {
                $runtimeConfig['services.google.redirect'] = env('GOOGLE_MOBILE_REDIRECT_URL');
            }

            // Memaksa asset() dan route() memakai URL Host PC, bukan localhost dari NativePHP
            \Illuminate\Support\Facades\URL::forceRootUrl($hostServerUrl);
        }

        // PASTIKAN public disk URL selalu absolute URL (untuk web & mobile), 
        // sehingga Spatie Media Library tidak me-return '/storage/...' 
        // yang menyebabkan blade menggandakan 'storage//storage/'
        $runtimeConfig['filesystems.disks.public.url'] = ($isMobile ? $hostServerUrl : config('app.url')) . '/storage';

        config($runtimeConfig);

        if ($isMobile) {
            $dbConnection = config('database.default');
            error_log(sprintf(
                '[NativePHP] Environment: %s | OS: %s | Host IP: %s | DB via: %s | Proxy URL: %s',
                PHP_OS_FAMILY,
                PHP_OS,
                $hostIp,
                $dbConnection,
                $proxyUrl ?? 'N/A'
            ));
        }

        // ── 4. REFRESH DB CONNECTION ──────────────────────────────────────
        // Removed redundant purge/reconnect to prevent connection thrashing
        // Laravel handles connection lifecycle efficiently.

        // ── 5. ON-DEMAND INITIALIZATION (Mobile only) ────────────────────
        // Optimization: Use a flag file to persist 'initialized' state across requests.
        // PHP static variables do not persist across separate HTTP requests.
        $flagFile = storage_path('framework/mobile_init.flag');

        // Bypassed if using mysql_proxy because PC already handles migrations and seeders!
        // This removes ALL delay and loading times on mobile boot.
        if ($isMobile && config('database.default') !== 'mysql_proxy' && ! file_exists($flagFile) && ! app()->runningInConsole()) {
            try {
                // FAST PING: Mencegah White Screen lama jika PC Host tidak terjangkau / Firewall aktif
                try {
                    Http::timeout(2)->post($proxyUrl, ['method' => 'select', 'query' => 'SELECT 1', 'bindings' => []]);
                } catch (\Throwable $e) {
                    error_log('[NativePHP] Host PC unreachable/Firewall active. Skipping DB init to prevent timeout delay.');
                    return; // Abort init agar tidak white screen
                }

                // Double check DB status only if flag is missing
                $hasUsers = false;
                try {
                    $hasUsers = User::exists();
                } catch (\Throwable $e) {
                    $hasUsers = false;
                }

                if (! $hasUsers) {
                    error_log('[NativePHP] Database empty. Initializing...');
                    Artisan::call('migrate', ['--force' => true]);

                    // Jalankan semua seeder sama persis seperti DatabaseSeeder::run()
                    // Urutan penting: roles → admin → organizer → products → packages → banners → articles → terms
                    $seeders = [
                        'RolesAndPermissionsSeeder',
                        'SuperAdminSeeder',
                        'WeddingOrganizerSeeder',
                        'ProductSeeder',
                        'PackageSeeder',
                        'BannerSeeder',
                        'ArticleSeeder',
                        'TermsAndConditionsSeeder',
                        'VoucherSeeder',
                    ];

                    foreach ($seeders as $seeder) {
                        try {
                            Artisan::call('db:seed', [
                                '--class' => "Database\\Seeders\\{$seeder}",
                                '--force' => true,
                            ]);
                            error_log("[NativePHP] Seeder done: {$seeder}");
                        } catch (\Throwable $e) {
                            error_log("[NativePHP] Seeder failed ({$seeder}): ".$e->getMessage());
                            // Lanjut ke seeder berikutnya meski ada yang gagal
                        }
                    }

                    error_log('[NativePHP] Initialization done.');
                }

                // Create flag to skip this check in future requests
                file_put_contents($flagFile, date('Y-m-d H:i:s'));

            } catch (\Throwable $e) {
                error_log('[NativePHP] Init failed: '.$e->getMessage());
            }
        }
    }

    // ═══════════════════════════════════════════════════════════════════════
    // NATIVEPHP PLUGINS
    // ═══════════════════════════════════════════════════════════════════════

    /**
     * The NativePHP plugins to enable.
     * Only plugins listed here will be compiled into your native builds.
     *
     * @return array<int, class-string<ServiceProvider>>
     */
    public function plugins(): array
    {
        return [
            ScreenServiceProvider::class,
            SystemServiceProvider::class,
            DeviceServiceProvider::class,
            NetworkServiceProvider::class,
            CameraServiceProvider::class,

        ];
    }
}
