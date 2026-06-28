# Requirements Document

## Introduction

The cross-platform detection system identifies which of eight runtime environments the Laravel 12 + Filament v3 + NativePHP wedding organizer application is currently executing in: Website on Windows, Website on macOS, Website on Android, Website on iPhone/iPad (iOS), Desktop App on Windows (NativePHP/Electron), Desktop App on macOS (NativePHP/Electron), Mobile App on Android (NativePHP), and Mobile App on iOS (NativePHP). The system must produce a correct, unambiguous `RuntimePlatform` enum value in every execution context — HTTP request, Artisan console, and PHPUnit unit tests — without false positives on developer machines (Windows, WSL, CI/CD pipelines). It must also expose the detected platform to the JavaScript frontend so that UI behaviour can adapt without a round-trip to the server.

---

## Glossary

- **RuntimePlatform**: The PHP-backed enum (`App\Enums\RuntimePlatform`) with eight cases representing every supported runtime environment.
- **PlatformContext**: The PHP class (`App\Support\PlatformContext`) that resolves and caches the current `RuntimePlatform` for a request lifecycle.
- **NativeServiceProvider**: The PHP service provider (`App\Providers\NativeServiceProvider`) that bootstraps NativePHP-specific configuration and exposes low-level detection helpers (`isNativeMobile`, `isAnyMobile`, `mobileHostIp`, `normalizeUrl`).
- **PlatformSupportServiceProvider**: The PHP service provider (`App\Providers\PlatformSupportServiceProvider`) that registers Filament render hooks for PWA head, platform runtime script, and language switcher.
- **PlatformRuntimeScript**: The Blade component (`platform-runtime-script.blade.php`) rendered into every Filament page that detects display mode and exposes platform data to JavaScript.
- **DetectionSignal**: Any single piece of evidence used to determine the platform — e.g. `NATIVEPHP_RUNNING` constant, `NATIVEPHP_PLATFORM` env var, User-Agent string, `app_display_mode` cookie, `REMOTE_ADDR`, OS family.
- **NativePHP_Mobile**: The NativePHP mobile package that embeds a PHP server inside an Android or iOS app.
- **NativePHP_Desktop**: The NativePHP desktop package (Electron-based) that embeds a PHP server inside a Windows or macOS desktop application.
- **WSL**: Windows Subsystem for Linux — a developer environment that reports `Linux` as `PHP_OS_FAMILY` but is not a mobile device.
- **CI**: Continuous Integration environment (e.g. GitHub Actions) where no real device is present.
- **app_display_mode**: An HTTP cookie set by the PlatformRuntimeScript to `standalone` when the browser reports PWA standalone display mode.
- **window.AppPlatform**: A JavaScript global object injected by the PlatformRuntimeScript that exposes the server-detected platform slug and derived boolean flags to frontend code.
- **Platform Slug**: The string value of the `RuntimePlatform` enum case (e.g. `website_windows`, `mobile_app_android`).

---

## Requirements

### Requirement 1: Authoritative Platform Resolution

**User Story:** As a backend developer, I want a single authoritative source of truth for the current runtime platform, so that all application code reads the same value without duplicating detection logic.

#### Acceptance Criteria

1. THE `PlatformContext` SHALL expose a `current(?Request $request): RuntimePlatform` static method that returns exactly one of the eight `RuntimePlatform` cases for every call, and SHALL never return `null` or throw an exception under any supported execution context.
2. WHILE the same PHP process is handling a single request, THE `PlatformContext` SHALL return the same `RuntimePlatform` value on every call to `current()` by storing the resolved value in a static property, without re-executing detection logic; IF the static cache is already set, THE `PlatformContext` SHALL always return the cached value even if detection signals would yield a different result mid-request.
3. WHEN `PlatformContext::reset()` is called, THE `PlatformContext` SHALL set its static cache property to `null` so that the next call to `current()` re-executes the full detection logic.
4. WHEN `PlatformContext::current()` is invoked, THE `PlatformContext` SHALL evaluate detection signals in the following priority order, stopping at the first signal that yields a definitive result: (1) `NATIVEPHP_PLATFORM` env/config flag, (2) `NATIVEPHP_RUNNING` constant or env var (where "truthy" means a non-empty string that is not `"0"` or `"false"`), (3) User-Agent string, (4) `app_display_mode` cookie, (5) OS family heuristic.
5. IF no `Request` object is available (console context), THEN THE `PlatformContext` SHALL resolve the platform using only env vars, PHP constants, and `PHP_OS_FAMILY` — it SHALL NOT attempt to read the User-Agent, cookies, or any other HTTP-only signal.
6. IF `NATIVEPHP_PLATFORM` holds a value that does not match any recognised platform token (`android`, `ios`, `win32`, `windows`, `mac`, `macos`, `darwin`) using exact case-sensitive comparison, THEN THE `PlatformContext` SHALL ignore that signal and fall through to the next signal in the priority order; values such as `Android`, `IOS`, or `WIN32` SHALL NOT be recognised and SHALL be treated as unrecognised tokens.

---

### Requirement 2: Mobile App Detection (NativePHP Mobile)

**User Story:** As a backend developer, I want the system to reliably detect when the app is running inside a NativePHP mobile shell on Android or iOS, so that mobile-specific configuration (DB proxy, URL rewriting, camera mode) is applied correctly.

#### Acceptance Criteria

1. WHEN the `NATIVEPHP_RUNNING` PHP constant is defined and its value is truthy (non-empty, not `false`, not `0`), THE `NativeServiceProvider` SHALL classify the runtime as a NativePHP mobile app.
2. WHEN the `NATIVEPHP_RUNNING` environment variable is set to a truthy value (non-empty string that is not `"0"` or `"false"`) and the `NATIVEPHP_RUNNING` constant is not defined, THE `NativeServiceProvider` SHALL classify the runtime as a NativePHP mobile app.
3. WHEN `NATIVEPHP_PLATFORM` resolves to the string `android` using exact case-sensitive comparison, THE `PlatformContext` SHALL return `RuntimePlatform::MobileAppAndroid`.
4. WHEN `NATIVEPHP_PLATFORM` resolves to the string `ios` using exact case-sensitive comparison, THE `PlatformContext` SHALL return `RuntimePlatform::MobileAppIos`.
5. WHEN the User-Agent contains the pattern `/Android.*wv\)/i` (Android WebView marker) and no explicit `NATIVEPHP_PLATFORM` flag is set, THE `PlatformContext` SHALL return `RuntimePlatform::MobileAppAndroid`.
6. WHEN `NativeServiceProvider::isNativeMobile()` returns `true` and neither the `NATIVEPHP_PLATFORM` flag nor the Android WebView UA pattern indicates Android, THE `PlatformContext` SHALL return `RuntimePlatform::MobileAppIos` as the default mobile platform.
7. IF `APP_ENV` equals `testing` OR `app()->runningUnitTests()` returns `true`, THEN THE `NativeServiceProvider` SHALL return `false` from `isNativeMobile()` regardless of any other signal.
8. IF the `GITHUB_ACTIONS` environment variable is set to any non-empty value, THEN THE `NativeServiceProvider` SHALL return `false` from `isNativeMobile()`.
9. IF `PHP_OS_FAMILY` is `Linux` and reading `/proc/version` succeeds and the content contains `microsoft` (case-insensitive), THEN THE `NativeServiceProvider` SHALL return `false` from `isNativeMobile()`; this WSL exclusion SHALL apply only when WSL is detected via this specific `/proc/version` method — if WSL is detected through any other means, the exclusion SHALL NOT apply; IF reading `/proc/version` fails, THE `NativeServiceProvider` SHALL treat the WSL check as inconclusive and continue to the next detection step.

---

### Requirement 3: Desktop App Detection (NativePHP Desktop / Electron)

**User Story:** As a backend developer, I want the system to reliably detect when the app is running inside a NativePHP desktop shell on Windows or macOS, so that desktop-specific behaviour is applied without misidentifying web browsers or mobile apps.

#### Acceptance Criteria

1. WHEN `NATIVEPHP_PLATFORM` resolves to `win32` or `windows` using exact case-sensitive comparison, THE `PlatformContext` SHALL return `RuntimePlatform::DesktopAppWindows`.
2. WHEN `NATIVEPHP_PLATFORM` resolves to `mac`, `macos`, or `darwin` using exact case-sensitive comparison, THE `PlatformContext` SHALL return `RuntimePlatform::DesktopAppMacOS`.
3a. WHEN the User-Agent contains `NativePHP` (without `Mobile` suffix) or `Electron/` (case-insensitive) and the User-Agent also contains `Windows NT`, THE `PlatformContext` SHALL return `RuntimePlatform::DesktopAppWindows`.
3b. WHEN the User-Agent contains `NativePHP` (without `Mobile` suffix) or `Electron/` (case-insensitive) and the User-Agent does not contain `Windows NT`, THE `PlatformContext` SHALL return `RuntimePlatform::DesktopAppMacOS`.
4. WHEN `NativeServiceProvider::isNativeMobile()` returns `true`, THE `PlatformContext` SHALL NOT classify the runtime as a desktop app, regardless of any other signal; the underlying `isMobileApp` and `isDesktopApp` flags MAY both be `true` simultaneously, but the classification logic SHALL give mobile precedence over desktop when resolving the final `RuntimePlatform` case.
5. WHEN `NATIVEPHP_PLATFORM` resolves to `android` or `ios` (case-insensitive), THE `PlatformContext` SHALL NOT classify the runtime as a desktop app.
6. WHEN the `app_display_mode` cookie equals `standalone` and the User-Agent does not contain any of `Android`, `iPhone`, `iPad`, `iPod`, or `Mobile` (case-insensitive), THE `PlatformContext` SHALL classify the runtime as a desktop PWA and apply the same Windows/macOS UA distinction as criteria 3a/3b.
7. WHEN `NATIVEPHP_RUNNING` is truthy and `PHP_OS_FAMILY` is `Windows`, THE `PlatformContext` SHALL return `RuntimePlatform::DesktopAppWindows`.
8. WHEN `NATIVEPHP_RUNNING` is truthy and `PHP_OS_FAMILY` is `Darwin` and the User-Agent does not contain `iPhone`, `iPad`, or `iPod` (case-insensitive), THE `PlatformContext` SHALL return `RuntimePlatform::DesktopAppMacOS`.

---

### Requirement 4: Website Detection (Browser on Desktop and Mobile)

**User Story:** As a backend developer, I want the system to correctly identify website visitors on all four browser platforms (Windows, macOS, Android, iOS), so that PWA prompts, camera mode, and UI layout are appropriate for each platform.

#### Acceptance Criteria

1. WHEN the User-Agent contains `iPhone`, `iPad`, or `iPod` (case-insensitive match) and `NativeServiceProvider::isNativeMobile()` returns `false`, THE `PlatformContext` SHALL return `RuntimePlatform::WebsiteIos`; iOS detection SHALL take priority over any macOS signal present in the same User-Agent string (e.g. iPadOS desktop-mode UA containing `Macintosh`).
2. WHEN the User-Agent contains `Android` (case-insensitive match) and `NativeServiceProvider::isNativeMobile()` returns `false` and the User-Agent does not match the pattern `/Android.*wv\)/i`, THE `PlatformContext` SHALL return `RuntimePlatform::WebsiteAndroid`.
3. WHEN the User-Agent contains `Macintosh` or `Mac OS X` (case-insensitive match) and none of the following signals are present — `isNativeMobile()` returning `true`, `NATIVEPHP_PLATFORM` being non-null, UA containing `NativePHP`/`Electron/`, `app_display_mode` cookie equaling `standalone`, UA containing `iPhone`/`iPad`/`iPod` — THE `PlatformContext` SHALL return `RuntimePlatform::WebsiteMacOS`.
4. WHEN none of the iOS, Android, macOS, or native desktop signals enumerated in criteria 1–3 are present, or when the User-Agent is absent or empty, THE `PlatformContext` SHALL return `RuntimePlatform::WebsiteWindows` as the default fallback.
5. WHEN `PlatformContext::isAnyMobile()` is called, THE `PlatformContext` SHALL return `true` if and only if the current `RuntimePlatform` is one of `WebsiteAndroid`, `WebsiteIos`, `MobileAppAndroid`, or `MobileAppIos`, and SHALL return `false` for `WebsiteWindows`, `WebsiteMacOS`, `DesktopAppWindows`, and `DesktopAppMacOS`.

---

### Requirement 5: Console and Test Context Safety

**User Story:** As a backend developer, I want the detection system to behave predictably in Artisan console commands and PHPUnit tests, so that test suites pass reliably on developer machines and CI without false mobile or desktop detections.

#### Acceptance Criteria

1. IF `app()->runningUnitTests()` returns `true` OR `APP_ENV` equals `testing`, THEN THE `NativeServiceProvider` SHALL return `false` from `isNativeMobile()` regardless of OS family, env vars, or PHP constants, and this guard SHALL be evaluated before any other detection signal.
2. WHEN `app()->runningInConsole()` returns `true` and `app()->runningUnitTests()` returns `false`, THE `NativeServiceProvider::isAnyMobile()` SHALL return `true` only if `isNativeMobile()` returns `true` or `$_SERVER['HTTP_USER_AGENT']` matches the mobile UA pattern, and SHALL return `false` in all other cases — it SHALL NOT call `PlatformContext::current()`.
3. WHEN `PlatformContext::current()` is called with no `Request` object in a console context, THE `PlatformContext` SHALL NOT throw an exception and SHALL return `RuntimePlatform::WebsiteWindows` as the safe fallback when no env-var or constant signal is present.
4. WHEN `PlatformContext::reset()` is called, THE `PlatformContext` SHALL set its static cache to `null` so that the next call to `current()` re-executes the full detection logic; this enables test isolation between test cases.
5. WHEN `PlatformContext::current()` is called in a console context, THE `PlatformContext` SHALL NOT attempt to read HTTP-only signals (User-Agent header, cookies, `REMOTE_ADDR`) and SHALL rely solely on env vars, PHP constants, and `PHP_OS_FAMILY`.

---

### Requirement 6: JavaScript Platform Exposure

**User Story:** As a frontend developer, I want the server-detected platform to be available as a JavaScript global on every Filament page, so that client-side code can adapt UI behaviour (camera access, layout, feature flags) without an additional API call.

#### Acceptance Criteria

1. WHEN a Filament page is rendered, THE `PlatformRuntimeScript` SHALL inject an inline `<script>` tag in the document `<head>`, before any application script tags, that assigns a `window.AppPlatform` JavaScript object.
2. THE `window.AppPlatform` object SHALL contain a `slug` property whose value equals the `RuntimePlatform` enum value string (e.g. `"mobile_app_android"`) as resolved by `PlatformContext::current()` on the server for that request.
3. THE `window.AppPlatform` object SHALL contain a `label` property whose value equals the human-readable label returned by `RuntimePlatform::label()`.
4. THE `window.AppPlatform` object SHALL contain boolean properties: `isWebsite`, `isDesktopApp`, `isMobileApp`, `isMobileShell`, each reflecting the corresponding `RuntimePlatform` method result for the server-resolved platform; multiple boolean properties MAY be `true` simultaneously for the same request, enabling hybrid classifications, and no mutual-exclusivity constraint is enforced between them.
5. THE `window.AppPlatform` object SHALL contain a `cbirCameraMode` property whose value is one of `"native"`, `"mobile_browser_capture"`, or `"webrtc"` as returned by `RuntimePlatform::cbirCameraMode()`; IF an unrecognised value is produced, THE `PlatformRuntimeScript` SHALL default to `"webrtc"`.
6. WHEN the browser reports PWA standalone display mode (`window.matchMedia('(display-mode: standalone)').matches` or `window.navigator.standalone === true`) and the `app_display_mode` cookie is not already set to `standalone`, THE `PlatformRuntimeScript` SHALL set the `app_display_mode` cookie to `standalone` with a one-year expiry (`max-age=31536000`), `path=/`, and `SameSite=Lax`.
7. WHEN the `app_display_mode` cookie is already set to `standalone`, THE `PlatformRuntimeScript` SHALL NOT overwrite the cookie.
8. THE `PlatformRuntimeScript` SHALL wrap all JavaScript execution in a `try/catch` block; IF an exception is caught, THE script SHALL emit a `console.error` with the error details so that the runtime error does not prevent the rest of the page from loading.

---

### Requirement 7: Import and Dependency Correctness

**User Story:** As a backend developer, I want all PHP classes to declare their dependencies with correct `use` import statements, so that the application does not throw `Class not found` errors at runtime.

#### Acceptance Criteria

1. THE `NativeServiceProvider` SHALL declare `use App\Support\PlatformContext;` in the namespace block, before the class declaration, so that any call to `PlatformContext::isAnyMobile()` or `PlatformContext::current()` resolves without error.
2. THE `PlatformContext` SHALL declare both `use App\Enums\RuntimePlatform;` and `use App\Providers\NativeServiceProvider;` in the namespace block, before the class declaration, and both imports SHALL be present together.
3. THE `RuntimePlatform` enum SHALL NOT import any class under the `App\` namespace, keeping it a pure value object with no application-layer dependencies.
4. IF a `use` import is missing from `NativeServiceProvider`, `PlatformContext`, or `RuntimePlatform`, THEN PHP SHALL throw an `Error` exception before any method body in that class executes, making the missing dependency immediately visible rather than silently producing an incorrect platform value.

---

### Requirement 8: Detection Signal Round-Trip Consistency

**User Story:** As a QA engineer, I want the platform detection to be consistent across the full request lifecycle, so that the PHP-detected platform and the JavaScript-exposed platform always agree for the same request.

#### Acceptance Criteria

1. WHEN `PlatformContext::current()` returns a `RuntimePlatform` value for a given request, THE `PlatformRuntimeScript` SHALL read that same server-resolved value (passed via the Blade render context) and inject it as `window.AppPlatform.slug` — it SHALL NOT re-detect the platform independently in the Blade template.
2. THE `PlatformSupportServiceProvider` SHALL render the `PlatformRuntimeScript` using the `SCRIPTS_AFTER` Filament render hook so that `window.AppPlatform` is defined in the DOM before the closing `</body>` tag.
3. FOR ALL valid `RuntimePlatform` cases, calling `RuntimePlatform::from($case->value)` SHALL return the original case (round-trip property); no two cases SHALL share the same `->value` string.
4. THE `RuntimePlatform` enum SHALL define exactly eight cases: `WebsiteWindows`, `WebsiteMacOS`, `WebsiteAndroid`, `WebsiteIos`, `DesktopAppWindows`, `DesktopAppMacOS`, `MobileAppAndroid`, `MobileAppIos` — with no additional cases and no duplicate values.
