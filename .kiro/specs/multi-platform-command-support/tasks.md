# Implementation Plan: Multi-Platform Command Support

## Overview

This implementation plan breaks down the multi-platform command support feature into sequential, actionable tasks for a coding agent. The feature enables the Laravel Wedding Organizer CBIR application to operate correctly across three distinct runtime environments (web, mobile, desktop) by detecting platform modes from Artisan commands, configuring environment settings, compiling platform-specific assets, and managing feature availability.

The implementation follows a layered approach: command detection → platform mode initialization → runtime platform detection → environment management → asset compilation → feature registry → integration with existing services → testing → documentation.

## Tasks

- [x] 1. Create core platform enums and detection foundation
  - [x] 1.1 Create PlatformMode enum with three cases
    - Create `app/Enums/PlatformMode.php`
    - Define three cases: Web, Mobile, Desktop with string values
    - Add helper methods: `label()`, `environmentFile()`, `assetDirectory()`, `viteInput()`
    - Add permission helper methods: `allowsCameraAccess()`, `allowsFileSystemAccess()`
    - _Requirements: 1.1, 1.2, 1.3_

  - [x] 1.2 Create PlatformCommandDetector class
    - Create `app/Support/Platform/PlatformCommandDetector.php`
    - Implement `detectMode()` static method that analyzes `$_SERVER['argv']`
    - Add `isRunningArtisan()` method to check if command is Artisan
    - Add `detectFromCommand()` method to map commands to platform modes
    - Add `detectFromRuntime()` method for HTTP/native runtime detection
    - Map `serve` → Web, `native:run` → Mobile, `native:serve` → Desktop
    - _Requirements: 1.1, 1.2, 1.3, 1.4, 1.5_

  - [x] 1.3 Write property test for command sequence handling
    - **Property 1: Platform Mode Switches with Last Command**
    - **Validates: Requirements 1.6**
    - Generate random sequences of Artisan commands
    - Verify the final active platform mode matches the last command in sequence
    - Test command precedence: later commands override earlier ones

- [ ] 2. Implement runtime platform detection
  - [x] 2.1 Create RuntimePlatformDetector class
    - Create `app/Support/Platform/RuntimePlatformDetector.php`
    - Implement `detect(PlatformMode $mode, ?Request $request)` method
    - Add `detectWebPlatform()` for user agent parsing (iOS, Android, macOS, Windows)
    - Add `detectMobilePlatform()` using NativePHP Mobile Device API
    - Add `detectDesktopPlatform()` using PHP_OS_FAMILY constant
    - Add exception handling with fallback to RuntimePlatform::WebsiteWindows
    - Add logging for detection failures
    - _Requirements: 2.1, 2.2, 2.3, 2.4, 2.5, 2.6, 2.7_

  - [x] 2.2 Write property test for user agent detection completeness
    - **Property 2: User Agent Detection Completeness**
    - **Validates: Requirements 2.1, 10.5**
    - Generate varied user agent strings (browsers, mobile devices, OS variations)
    - Verify all return valid website RuntimePlatform cases
    - Test edge cases: empty strings, malformed agents, unusual browsers

  - [x] 2.3 Write property test for valid enum returns
    - **Property 3: Platform Detection Always Returns Valid Enum**
    - **Validates: Requirements 2.4**
    - Generate random detection inputs (modes, requests, device info)
    - Verify all outputs are valid RuntimePlatform enum cases
    - Test with null/missing request objects

  - [x] 2.4 Write property test for exception handling defaults
    - **Property 4: Platform Detection Failure Defaults to WebsiteWindows**
    - **Validates: Requirements 2.6**
    - Generate inputs that trigger exceptions (invalid data, missing classes)
    - Verify detector catches exceptions, logs warnings, returns WebsiteWindows
    - Test recovery behavior across all platform modes

- [ ] 3. Create environment configuration management
  - [x] 3.1 Create EnvironmentManager class
    - Create `app/Support/Platform/EnvironmentManager.php`
    - Implement `loadPlatformEnvironment(PlatformMode $mode)` method
    - Add `parseEnvironmentFile(string $path)` method for .env parsing
    - Support loading `.env.web`, `.env.mobile`, `.env.desktop` files
    - Merge platform-specific variables into $_ENV, $_SERVER, and putenv()
    - Add logging for loaded environment files and variable counts
    - Handle missing platform environment files gracefully
    - _Requirements: 3.1, 3.2, 3.3, 3.4, 3.6, 3.7_

  - [x] 3.2 Write property test for environment merge precedence
    - **Property 5: Environment Variable Merge Precedence**
    - **Validates: Requirements 3.5**
    - Generate environment variable keys present in both base and platform files
    - Verify platform-specific values override base values
    - Test across all three platform modes

  - [x] 3.3 Create sample platform-specific environment files
    - Create `.env.web.example` with web-specific variables (SESSION_DRIVER=cookie, APP_URL)
    - Create `.env.mobile.example` with mobile-specific variables (SESSION_DRIVER=database)
    - Create `.env.desktop.example` with desktop-specific variables (SESSION_DRIVER=file)
    - Add comments explaining platform-specific configuration choices
    - _Requirements: 3.2, 3.3, 3.4_

- [x] 4. Checkpoint - Ensure core detection works
  - Ensure all tests pass, ask the user if questions arise.

- [ ] 5. Implement asset compilation and management
  - [x] 5.1 Create PlatformAssetManager class
    - Create `app/Support/Platform/PlatformAssetManager.php`
    - Implement `configure(PlatformMode $mode)` method
    - Add `getManifestPath()` to return platform-specific manifest path
    - Add `getBuildDirectory()` to return build/web, build/mobile, or build/desktop
    - Add `getViteInput()` to return platform-specific entry point
    - Add `asset(string $path)` method to resolve versioned asset URLs
    - Add `loadManifest()` private method to parse manifest.json
    - _Requirements: 4.1, 4.2, 4.3, 4.4, 4.5_

  - [x] 5.2 Write property test for asset manifest path matching
    - **Property 6: Asset Manifest Path Matches Platform Mode**
    - **Validates: Requirements 4.6**
    - For each platform mode (Web, Mobile, Desktop)
    - Verify manifest path contains correct directory name
    - Test getBuildDirectory() returns expected paths

  - [x] 5.3 Update Vite configuration for multi-platform builds
    - Modify `vite.config.js` to support multiple entry points
    - Add build configurations for web, mobile, desktop platforms
    - Configure output directories: `public/build/web`, `public/build/mobile`, `public/build/desktop`
    - Set up platform-specific asset manifests
    - Add hot module replacement support with platform detection
    - _Requirements: 4.1, 4.7_

  - [x] 5.4 Create platform-specific JavaScript entry points
    - Create `resources/js/app-web.js` importing web-specific components
    - Create `resources/js/app-mobile.js` importing mobile-specific components
    - Create `resources/js/app-desktop.js` importing desktop-specific components
    - Add conditional imports based on platform capabilities
    - _Requirements: 4.1, 4.5_

- [ ] 6. Create platform feature registry
  - [x] 6.1 Create PlatformFeatureRegistry class
    - Create `app/Support/Platform/PlatformFeatureRegistry.php`
    - Define FEATURE_MATRIX constant mapping features to RuntimePlatform cases
    - Add features: camera, desktop_notifications, push_notifications, file_system, webrtc, auto_updates, app_badge
    - Implement `isAvailable(string $feature, RuntimePlatform $platform)` method
    - Implement `getAvailableFeatures(RuntimePlatform $platform)` method
    - Implement `getPlatformsForFeature(string $feature)` method
    - _Requirements: 5.1, 5.2, 5.3, 5.4, 5.5, 5.6, 5.7, 5.9_

  - [x] 6.2 Write property test for file system availability
    - **Property 7: File System Availability Matches Platform Mode**
    - **Validates: Requirements 5.8**
    - For all RuntimePlatform enum cases
    - Verify file_system feature returns true only for Mobile/Desktop platforms
    - Verify returns false for all Website platforms

  - [x] 6.3 Integrate feature registry with existing RuntimePlatform enum
    - Update `app/Enums/RuntimePlatform.php` to add feature-checking methods if needed
    - Ensure consistency between RuntimePlatform methods and feature registry
    - Verify existing `cbirCameraMode()` method aligns with feature registry
    - _Requirements: 5.9_

- [ ] 7. Create platform mode service provider
  - [x] 7.1 Create PlatformModeServiceProvider
    - Create `app/Providers/PlatformModeServiceProvider.php`
    - Register platform mode as singleton in `register()` method
    - Register RuntimePlatformDetector, EnvironmentManager, PlatformAssetManager as singletons
    - Implement `boot()` method to detect mode, load environment, detect runtime platform
    - Store runtime platform as singleton accessible via `app('runtime.platform')`
    - Configure asset manager for the detected platform mode
    - Add development-mode logging for platform detection
    - _Requirements: 1.4, 1.5, 2.5_

  - [x] 7.2 Register PlatformModeServiceProvider in application config
    - Add PlatformModeServiceProvider to `config/app.php` providers array
    - Ensure provider runs early in bootstrap process
    - _Requirements: 1.4, 1.5_

- [x] 8. Checkpoint - Ensure service provider integration works
  - Ensure all tests pass, ask the user if questions arise.

- [x] 9. Create helper functions for convenient access
  - [x] 9.1 Add platform helper functions
    - Create or modify `app/helpers.php`
    - Add `platform_mode()` function returning current PlatformMode
    - Add `runtime_platform()` function returning current RuntimePlatform
    - Add `platform_feature(string $feature)` function checking feature availability
    - Add `is_web_mode()`, `is_mobile_mode()`, `is_desktop_mode()` boolean helpers
    - Register helpers file in composer.json autoload.files
    - _Requirements: 5.2_

  - [x] 9.2 Write unit tests for helper functions
    - Test each helper returns correct values for all platform modes
    - Mock platform mode and runtime platform in tests
    - Verify platform_feature() correctly delegates to registry

- [x] 10. Implement conditional route registration
  - [x] 10.1 Create platform-specific route files
    - Create `routes/mobile.php` for mobile-specific API routes
    - Create `routes/desktop.php` for desktop-specific API routes
    - Add example routes for camera, file system, notifications in each file
    - _Requirements: 9.2, 9.3, 9.4_

  - [x] 10.2 Update RouteServiceProvider for conditional registration
    - Modify `app/Providers/RouteServiceProvider.php`
    - Add logic to load mobile.php routes only when platform mode is Mobile
    - Add logic to load desktop.php routes only when platform mode is Desktop
    - Add route prefixes: `api/mobile`, `api/desktop`
    - _Requirements: 9.1, 9.2, 9.3, 9.4, 9.6_

  - [x] 10.3 Write property test for unsupported platform route access
    - **Property 10: Unsupported Platform Routes Return 404**
    - **Validates: Requirements 9.5**
    - Define platform-specific routes
    - Access routes from incompatible RuntimePlatforms
    - Verify all return HTTP 404 responses

  - [x] 10.4 Write property test for route cache compatibility
    - **Property 11: Route Cache Contains Only Compatible Routes**
    - **Validates: Requirements 9.7**
    - For each platform mode (Web, Mobile, Desktop)
    - Cache routes and inspect cached collection
    - Verify only compatible routes are present

- [x] 11. Implement camera access integration
  - [x] 11.1 Create platform-aware camera controller
    - Create `app/Http/Controllers/PlatformCameraController.php`
    - Add `capture()` method that delegates to platform-specific camera APIs
    - For MobileApp platforms: use NativePHP Mobile Camera API
    - For DesktopApp platforms: use NativePHP Desktop Camera API
    - For Website platforms: return WebRTC getUserMedia instructions
    - Use `RuntimePlatform::cbirCameraMode()` for camera mode selection
    - _Requirements: 6.1, 6.2, 6.3, 6.4_

  - [x] 11.2 Write property test for permission denial messages
    - **Property 8: Permission Denial Messages Are Platform-Aware**
    - **Validates: Requirements 6.5**
    - For all RuntimePlatform cases
    - Simulate camera permission denial
    - Verify non-empty, platform-appropriate error messages are displayed

  - [x] 11.3 Add camera routes to platform-specific route files
    - Add POST `/api/mobile/camera/capture` route in routes/mobile.php
    - Add POST `/api/desktop/camera/capture` route in routes/desktop.php
    - _Requirements: 6.1, 6.2, 6.6_

- [ ] 12. Add command validation and error handling
  - [x] 12.1 Create PlatformDependencyValidator class
    - Create `app/Support/Platform/PlatformDependencyValidator.php`
    - Implement `validateDependencies(PlatformMode $mode)` method
    - Check for NativePHP Electron classes when mode is Desktop
    - Check for Laravel Native classes when mode is Mobile
    - Return array of missing package names
    - _Requirements: 7.1, 7.2, 7.3, 7.4_

  - [x] 12.2 Write property test for missing dependency listing
    - **Property 9: Missing Dependencies Are Fully Listed**
    - **Validates: Requirements 7.5**
    - Generate sets of missing dependencies
    - Verify error messages include all missing packages
    - Test no dependencies are omitted

  - [x] 12.3 Create Artisan command wrappers with validation
    - Create `app/Console/Commands/ServePlatformCommand.php` wrapping `serve`
    - Create `app/Console/Commands/NativeRunCommand.php` wrapping `native:run`
    - Create `app/Console/Commands/NativeServeCommand.php` wrapping `native:serve`
    - Add dependency validation before command execution
    - Display error messages with missing packages and installation instructions
    - _Requirements: 7.2, 7.3, 7.5, 7.6_

  - [x] 12.4 Register custom commands in Kernel
    - Add custom commands to `app/Console/Kernel.php`
    - _Requirements: 7.2, 7.3_

- [x] 13. Checkpoint - Ensure command validation works
  - Ensure all tests pass, ask the user if questions arise.

- [ ] 14. Add development workflow support
  - [x] 14.1 Create platform mode inspection command
    - Create `app/Console/Commands/PlatformStatusCommand.php`
    - Display current platform mode, runtime platform, available features
    - Show loaded environment files and asset directories
    - Add as `php artisan platform:status` command
    - _Requirements: 8.3, 8.5_

  - [x] 14.2 Add platform cache clearing command
    - Create `app/Console/Commands/PlatformCacheClearCommand.php`
    - Clear cached platform detection state
    - Clear cached routes for platform mode switching
    - Add as `php artisan platform:clear` command
    - _Requirements: 8.4_

  - [x] 14.3 Configure multi-port support for simultaneous platform modes
    - Update server configuration to allow different ports per platform
    - Document port assignment strategy (8000 for web, 8001 for mobile, 8002 for desktop)
    - Update .env.example files with port configurations
    - _Requirements: 8.1, 8.2_

- [ ] 15. Implement testing infrastructure
  - [x] 15.1 Create test helpers for platform simulation
    - Create `tests/TestHelpers/PlatformTestHelper.php`
    - Add `setPlatformMode(PlatformMode $mode)` method
    - Add `setRuntimePlatform(RuntimePlatform $platform)` method
    - Add `mockPlatformDetection()` method for controlled testing
    - _Requirements: 10.1, 10.2, 10.3_

  - [x] 15.2 Write property test for RuntimePlatform category methods
    - **Property 12: RuntimePlatform Category Methods Are Mutually Exclusive**
    - **Validates: Requirements 10.6**
    - For all RuntimePlatform enum cases
    - Verify exactly one of isWebsite(), isDesktopApp(), isMobileApp() returns true
    - Verify the other two return false

  - [x] 15.3 Write integration tests for command initialization
    - Test `serve` command initializes Web mode correctly
    - Test `native:run` command initializes Mobile mode correctly
    - Test `native:serve` command initializes Desktop mode correctly
    - Verify RuntimePlatform is set appropriately after each command
    - _Requirements: 10.7_

  - [x] 15.4 Write integration tests for PlatformNotificationService
    - Test notification delivery for all RuntimePlatform cases
    - Verify correct notification API is used per platform (Filament, NativePHP Desktop, NativePHP Mobile)
    - Test withRecipientLocale() works with platform notifications
    - _Requirements: 10.8_

- [ ] 16. Update PlatformNotificationService integration
  - [x] 16.1 Enhance PlatformNotificationService with feature registry
    - Modify `app/Services/PlatformNotificationService.php`
    - Check feature availability before attempting desktop/mobile notifications
    - Use PlatformFeatureRegistry to validate notification support
    - Log when notification channels are skipped due to unavailability
    - _Requirements: 5.9_

  - [x] 16.2 Add platform-specific notification strategies
    - For Desktop: use NativePHP Notification API when feature available
    - For Mobile: use NativePHP Mobile Dialog API when feature available
    - For Web: use Filament database notifications only
    - _Requirements: 5.6, 5.7_

- [x] 17. Create comprehensive documentation
  - [x] 17.1 Write platform architecture documentation
    - Create `docs/platform-support.md`
    - Document three platform modes with command examples
    - Explain RuntimePlatform enum and its relationship to platform modes
    - Provide architecture diagrams (component overview, data flow)
    - _Requirements: 11.1, 11.2_

  - [x] 17.2 Document environment configuration strategy
    - Document .env, .env.web, .env.mobile, .env.desktop file structure
    - Explain environment variable merge precedence
    - Provide examples for common configuration scenarios
    - _Requirements: 11.3_

  - [x] 17.3 Document asset compilation process
    - Explain Vite multi-platform build setup
    - Document build commands for each platform
    - Provide hot module replacement configuration examples
    - _Requirements: 11.4_

  - [x] 17.4 Create platform feature matrix
    - Create table showing all features and their platform availability
    - Document how to check feature availability in code
    - Provide code examples using platform_feature() helper
    - _Requirements: 11.6, 11.8_

  - [x] 17.5 Create command usage decision tree
    - Create flowchart for choosing correct Artisan command
    - Document when to use serve, native:run, native:serve
    - Add troubleshooting section for common errors
    - _Requirements: 11.5, 11.7_

  - [x] 17.6 Update README.md with platform support overview
    - Add "Multi-Platform Support" section to README
    - Link to detailed platform documentation
    - Provide quick-start examples for each platform
    - Document development workflow recommendations
    - _Requirements: 8.7, 11.1, 11.2_

- [x] 18. Add production deployment documentation
  - [x] 18.1 Document web production build process
    - Create `docs/deployment/web.md`
    - Document build commands: `npm run build` with VITE_PLATFORM=web
    - Provide server configuration examples (Nginx, Apache)
    - Document environment variable requirements
    - _Requirements: 12.1, 12.6_

  - [x] 18.2 Document mobile app store deployment
    - Create `docs/deployment/mobile.md`
    - Document Laravel Native build process
    - Provide Android/iOS build configuration examples
    - Document app store submission requirements
    - _Requirements: 12.2, 12.6_

  - [x] 18.3 Document desktop application distribution
    - Create `docs/deployment/desktop.md`
    - Document NativePHP Electron build process
    - Provide Windows/Mac packaging examples
    - Document code signing and distribution strategies
    - _Requirements: 12.3, 12.6_

  - [x] 18.4 Create CI/CD pipeline examples
    - Create `.github/workflows/build-web.yml` example
    - Create `.github/workflows/build-mobile.yml` example
    - Create `.github/workflows/build-desktop.yml` example
    - Document multi-platform testing strategy in CI
    - _Requirements: 12.5_

  - [x] 18.5 Add production environment validation
    - Create `app/Support/Platform/ProductionValidator.php`
    - Implement environment variable validation for production mode
    - Check required variables are set per platform
    - Log errors and prevent startup when validation fails
    - _Requirements: 12.4, 12.7, 12.8_

- [x] 19. Final checkpoint - Complete system validation
  - Ensure all tests pass, ask the user if questions arise.

## Notes

- Tasks marked with `*` are optional and can be skipped for faster MVP
- Each task references specific requirements for traceability
- Property tests validate universal correctness properties from the design
- Unit tests validate specific examples and edge cases
- Integration tests ensure end-to-end flows work correctly
- The implementation follows a bottom-up approach: core components first, then integration, then documentation
- Platform detection must complete during early bootstrap before routes are registered
- Asset compilation requires separate Vite builds for each platform
- Feature availability checking is critical for conditional UI rendering and API availability
- Testing infrastructure enables comprehensive platform-specific test coverage

## Task Dependency Graph

```json
{
  "waves": [
    { "id": 0, "tasks": ["1.1", "1.2"] },
    { "id": 1, "tasks": ["1.3", "2.1"] },
    { "id": 2, "tasks": ["2.2", "2.3", "2.4", "3.1"] },
    { "id": 3, "tasks": ["3.2", "3.3", "5.1"] },
    { "id": 4, "tasks": ["5.2", "5.3", "5.4", "6.1"] },
    { "id": 5, "tasks": ["6.2", "6.3", "7.1"] },
    { "id": 6, "tasks": ["7.2", "9.1"] },
    { "id": 7, "tasks": ["9.2", "10.1", "10.2"] },
    { "id": 8, "tasks": ["10.3", "10.4", "11.1"] },
    { "id": 9, "tasks": ["11.2", "11.3", "12.1"] },
    { "id": 10, "tasks": ["12.2", "12.3"] },
    { "id": 11, "tasks": ["12.4", "14.1", "14.2", "14.3"] },
    { "id": 12, "tasks": ["15.1"] },
    { "id": 13, "tasks": ["15.2", "15.3", "15.4", "16.1"] },
    { "id": 14, "tasks": ["16.2", "17.1", "17.2", "17.3"] },
    { "id": 15, "tasks": ["17.4", "17.5", "17.6", "18.1"] },
    { "id": 16, "tasks": ["18.2", "18.3", "18.4", "18.5"] }
  ]
}
```
