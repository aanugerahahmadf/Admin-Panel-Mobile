<?php

return [

    /*
    |--------------------------------------------------------------------------
    | App Version Name
    |--------------------------------------------------------------------------
    |
    | This is the human-readable version of your app (e.g. "1.0.0"). It is
    | used as the versionName in Android builds and may be displayed in
    | the app or console to determine the current app release version.
    |
    | IMPORTANT: Must be valid semver format for Electron auto-updater.
    | Use "X.Y.Z" or "X.Y.Z-dev" format, NOT "DEBUG".
    |
    */

    'version' => env('NATIVEPHP_APP_VERSION', '1.0.0'),

    /*
    |--------------------------------------------------------------------------
    | App Author
    |--------------------------------------------------------------------------
    |
    | The author of your application. This is used in app metadata and
    | may be displayed in the about dialog or app information.
    |
    */

    'author' => env('NATIVEPHP_APP_AUTHOR', null),

    /*
    |--------------------------------------------------------------------------
    | App Description
    |--------------------------------------------------------------------------
    |
    | A brief description of your application. This is used in app metadata
    | and packaging information.
    |
    */

    'description' => env('NATIVEPHP_APP_DESCRIPTION', 'An awesome app built with NativePHP'),

    /*
    |--------------------------------------------------------------------------
    | App Website
    |--------------------------------------------------------------------------
    |
    | The website or homepage URL for your application. This is used in app
    | metadata and may be displayed in the about dialog.
    |
    */

    'website' => env('NATIVEPHP_APP_WEBSITE', 'https://nativephp.com'),

    /*
    |--------------------------------------------------------------------------
    | App Version Code
    |--------------------------------------------------------------------------
    |
    | This is the internal numeric version code used for Play Store builds.
    | It must increase with every release. This is used as versionCode in
    | Android builds and is required for publishing updates to the store.
    |
    */

    'version_code' => env('NATIVEPHP_APP_VERSION_CODE', 1),

    /*
    |--------------------------------------------------------------------------
    | App ID
    |--------------------------------------------------------------------------
    |
    | This is the unique ID of your application used by Android to identify
    | the app package. It is typically written in reverse domain format,
    | such as "com.nativephp.app".
    |
    */

    'app_id' => env('NATIVEPHP_APP_ID'),

    /*
    |--------------------------------------------------------------------------
    | Deeplink Scheme
    |--------------------------------------------------------------------------
    |
    | The deep link scheme to use for opening your app from URLs. For
    | example, using the scheme "nativephp" allows links like:
    | nativephp://some/path to open the app directly.
    |
    */

    'deeplink_scheme' => env('NATIVEPHP_DEEPLINK_SCHEME'),

    /*
    |--------------------------------------------------------------------------
    | Deeplink Host
    |--------------------------------------------------------------------------
    |
    | The domain name to associate with verified HTTPS links and NFC tags.
    | This allows URLs like https://your-host.com/path to open your app
    | when tapped from an NFC tag or clicked from the browser.
    |
    */

    'deeplink_host' => env('NATIVEPHP_DEEPLINK_HOST'),

    /*
    |--------------------------------------------------------------------------
    | Start URL
    |--------------------------------------------------------------------------
    |
    | The initial URL/path to load when the app starts. This should be a
    | path relative to the app root (e.g., "/dashboard", "/onboarding").
    | If not set, the app will load the root path ("/").
    |
    */

    'start_url' => env('NATIVEPHP_START_URL', '/'),

    /*
    |--------------------------------------------------------------------------
    | Development Team (iOS)
    |--------------------------------------------------------------------------
    |
    | The Apple Developer Team ID to use for code signing iOS apps. This is
    | automatically detected from your installed certificates, but you can
    | override it here if needed. Find your Team ID in your Apple Developer
    | account under Membership details.
    |
    */
    'development_team' => env('NATIVEPHP_DEVELOPMENT_TEAM'),

    /*
    |--------------------------------------------------------------------------
    | Environment Keys to Clean Up
    |--------------------------------------------------------------------------
    |
    | These are keys that will be removed from the .env file during app
    | bundling to prevent secrets or development credentials from being
    | leaked. Wildcards are supported (e.g. AWS_* or *_SECRET).
    |
    */

    'cleanup_env_keys' => [
        'AWS_*',
        'GITHUB_*',
        'DO_SPACES_*',
        'DB_PASSWORD',
        'DB_USERNAME',
    ],

    /*
    |--------------------------------------------------------------------------
    | Files to Exclude Before Bundling
    |--------------------------------------------------------------------------
    |
    | These files and folders will be removed before the final bundle is
    | built for production. You may use glob/wildcard patterns here to
    | skip unnecessary assets like logs, sessions, or temp data.
    |
    */

    'cleanup_exclude_files' => [
        'storage/framework/sessions',
        'storage/framework/cache',
        'storage/framework/testing',
        'storage/logs/laravel.log',
        'storage/logs',
        'storage/app/public',
        'storage/app/native-build',
        '.git',
        '.idea',
        '.vscode',
        'node_modules',
        'vendor/nativephp/mobile/resources',
    ],

    'android' => [
        'gradle_jdk_path' => env('NATIVEPHP_GRADLE_PATH'),
        'android_sdk_path' => env('NATIVEPHP_ANDROID_SDK_LOCATION'),
        'android_avd_home' => env('ANDROID_AVD_HOME'),
        'default_avd' => env('NATIVEPHP_ANDROID_AVD'),
        'emulator_path' => env('ANDROID_EMULATOR'),
        '7zip-location' => env('NATIVEPHP_7ZIP_LOCATION', PHP_OS_FAMILY === 'Windows' ? 'C:\\Program Files\\7-Zip\\7z.exe' : '/usr/bin/7z'),
        'min_sdk' => env('NATIVEPHP_ANDROID_MIN_SDK', 31),

        /*
        |--------------------------------------------------------------------------
        | Status Bar Style
        |--------------------------------------------------------------------------
        |
        | Set the color of the status bar and navigation bar icons.
        | Options: 'auto'  - Auto-detect from system theme (recommended)
        |          'light' - Light/white icons
        |          'dark'  - Dark icons
        |
        */
        'status_bar_style' => env('NATIVEPHP_ANDROID_STATUS_BAR_STYLE', 'auto'),

        /*
        |--------------------------------------------------------------------------
        | Android Build Configuration
        |--------------------------------------------------------------------------
        |
        | These options control how your Android app is built and optimized.
        | The defaults maintain current behavior while allowing customization
        | for production builds, debugging, and app store optimization.
        |
        */
        'build' => [
            // R8/ProGuard Configuration - currently disabled
            'minify_enabled' => env('NATIVEPHP_ANDROID_MINIFY_ENABLED', false),
            'shrink_resources' => env('NATIVEPHP_ANDROID_SHRINK_RESOURCES', false),
            'obfuscate' => env('NATIVEPHP_ANDROID_OBFUSCATE', false),

            // Debug Symbol Configuration - currently enabled
            'debug_symbols' => env('NATIVEPHP_ANDROID_DEBUG_SYMBOLS', 'FULL'),
            'generate_mapping_files' => env('NATIVEPHP_ANDROID_MAPPING_FILES', false),
            'mapping_file_path' => env('NATIVEPHP_ANDROID_MAPPING_PATH', 'build/outputs/mapping/release/'),

            // ProGuard Rules - currently disabled
            'keep_line_numbers' => env('NATIVEPHP_ANDROID_KEEP_LINE_NUMBERS', false),
            'keep_source_file' => env('NATIVEPHP_ANDROID_KEEP_SOURCE_FILE', false),
            'custom_proguard_rules' => env('NATIVEPHP_ANDROID_CUSTOM_PROGUARD_RULES', []),

            // Build Performance - using Gradle defaults
            'parallel_builds' => env('NATIVEPHP_ANDROID_PARALLEL_BUILDS', true),
            'incremental_builds' => env('NATIVEPHP_ANDROID_INCREMENTAL_BUILDS', true),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Electron Desktop Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration specific to Electron desktop applications.
    |
    */

    'electron' => [
        /*
        |--------------------------------------------------------------------------
        | PHP Binary Path
        |--------------------------------------------------------------------------
        |
        | Specify the absolute path to the PHP binary executable. If not set,
        | NativePHP will attempt to auto-detect PHP from common installation
        | paths and the system PATH. This is useful if you have multiple PHP
        | versions or PHP is installed in a non-standard location.
        |
        | Examples:
        |   Windows XAMPP: C:\xampp\php\php.exe
        |   Windows WAMP:  C:\wamp64\bin\php\php83\php.exe
        |   Windows Custom: C:\php\php.exe
        |   macOS Homebrew: /opt/homebrew/bin/php
        |   Linux: /usr/bin/php
        |
        */
        'php_binary_path' => env('NATIVEPHP_PHP_BINARY', null),

        /*
        |--------------------------------------------------------------------------
        | Disable GPU Hardware Acceleration
        |--------------------------------------------------------------------------
        |
        | Enable this option to disable GPU hardware acceleration in Electron.
        | This can resolve crashes on systems with incompatible or outdated
        | graphics drivers. Software rendering will be used instead.
        |
        */
        'disable_gpu' => env('ELECTRON_DISABLE_GPU', false),

        /*
        |--------------------------------------------------------------------------
        | Auto-Updater Configuration
        |--------------------------------------------------------------------------
        |
        | Enable or disable the Electron auto-updater. When disabled, the app
        | will not check for updates automatically. Useful for development or
        | enterprise deployments with custom update mechanisms.
        |
        */
        'auto_updater_enabled' => env('NATIVEPHP_AUTO_UPDATER', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Development Server Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for the NativePHP development server that allows hot
    | reloading of mobile applications during development.
    |
    */

    'server' => [
        // HTTP server port for serving the app
        'http_port' => env('NATIVEPHP_HTTP_PORT', 3000),

        // WebSocket server port for hot reload communication
        'ws_port' => env('NATIVEPHP_WS_PORT', 8081),

        // Service name advertised on the network
        'service_name' => env('NATIVEPHP_SERVICE_NAME', 'NativePHP Server'),

        // Service type for mDNS advertisement
        'service_type' => '_http._tcp',

        // Public directory to serve (relative to Laravel root)
        'public_path' => env('NATIVEPHP_PUBLIC_PATH', 'public'),

        // Build output directory (where the ZIP will be created)
        'build_path' => env('NATIVEPHP_BUILD_PATH', 'storage/app/native-build'),

        // Automatically open browser with QR code when server starts
        'open_browser' => env('NATIVEPHP_OPEN_BROWSER', true),

        // Watch these directories for changes
        'watch_paths' => [
            'app',
            'resources',
            'routes',
            'public/build',
        ],

        // File extensions to watch for changes
        'watch_extensions' => ['php', 'blade.php', 'js', 'css', 'ts', 'vue', 'json'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Hot Reload Configuration
    |--------------------------------------------------------------------------
    */
    'hot_reload' => [
        'watch_paths' => [
            'app',
            'resources',
            'routes',
            'config',
            'public',
        ],

        'exclude_patterns' => [
            '\.git',
            'storage',
            'tests',
            'nativephp',
            'credentials',
            'node_modules',
            '\.swp',
            '\.tmp',
            '~',
            '\.log',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | App Store Connect API Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for uploading apps to App Store Connect using the API.
    | These values are used for automated uploads during the package process.
    | Store sensitive data in environment variables for security.
    |
    */
    'app_store_connect' => [
        'api_key' => env('APP_STORE_API_KEY'),
        'api_key_id' => env('APP_STORE_API_KEY_ID'),
        'api_issuer_id' => env('APP_STORE_API_ISSUER_ID'),
        'app_name' => env('APP_STORE_APP_NAME'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Permissions
    |--------------------------------------------------------------------------
    |
    | Here you may enable or disable specific native features for your app.
    | Setting a permission to true allows NativePHP to request the necessary
    | access from the operating system at runtime (e.g., for NFC, biometrics,
    | or push notifications).
    |
    | For iOS, you can also provide a custom string that explains why your
    | app needs this permission. This text will be shown to users when they
    | are prompted to grant access. If you provide a string, the permission
    | will be enabled automatically.
    |
    | Android will interpret any string value as 'true', but the custom text
    | is only used on iOS (Android doesn't support permission reasons).
    |
    | Examples:
    |   'camera' => true,  // Uses default message
    |   'camera' => 'We need camera access to scan QR codes for login.',
    |   'camera' => false, // Permission disabled
    |
    | Make sure you run `php artisan native:install --force` after changing.
    |
    */
    'runtime' => [
        'mode' => env('NATIVEPHP_RUNTIME_MODE', 'persistent'), 
        'reset_instances' => true,
        'gc_between_dispatches' => false,
    ],

    'permissions' => [
        'camera' => 'Kami memerlukan akses kamera untuk mengambil foto portofolio.',
        'geolocation' => 'Akses lokasi diperlukan untuk menentukan titik penjemputan.',
        'notifications' => 'Izinkan kami mengirimkan notifikasi untuk update pesanan Anda.',
    ],

    /*
    |--------------------------------------------------------------------------
    | iPad Support
    |--------------------------------------------------------------------------
    |
    | Enable or disable iPad support for your iOS app. When enabled, your app
    | will support iPad devices and all iPad orientations (portrait, upside down,
    | landscape left, and landscape right) as required by Apple's App Store
    | guidelines. When disabled, your app will be iPhone-only.
    |
    | Note: Once an app is deployed to the App Store with iPad
    | support you cannot revoke this action.
    |
    */
    'ipad' => true,

    /*
    |--------------------------------------------------------------------------
    | Device Orientation Support
    |--------------------------------------------------------------------------
    |
    | Configure which orientations your app supports on different devices.
    | This will be applied during the build process to set appropriate
    | constraints in Info.plist (iOS) and AndroidManifest.xml (Android).
    |
    | For iPhone and Android, you can configure specific orientations.
    | For iPad, when enabled above, all orientations are automatically supported
    | as required by Apple's App Store guidelines.
    |
    | If all orientations are false for iPhone, the build will fail with a
    | helpful error message. If all orientations are false for Android, the
    | build will fail with a helpful error message.
    |
    */
    'orientation' => [
        'iphone' => [
            'portrait' => true,
            'upside_down' => false,
            'landscape_left' => false,
            'landscape_right' => false,
        ],
        'android' => [
            'portrait' => true,
            'upside_down' => false,
            'landscape_left' => false,
            'landscape_right' => false,
        ],
    ],
];
