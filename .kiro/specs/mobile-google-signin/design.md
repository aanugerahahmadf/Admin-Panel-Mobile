# Mobile Google Sign-In Bugfix Design

## Overview

This design addresses the visibility and functionality issue of the "Sign with Google" button in the Android mobile application's Login and Register pages within the Filament User Panel. Currently, the button does not appear or function correctly in the Android emulator, preventing users from authenticating via Google OAuth on mobile devices. The desktop Electron application works correctly, indicating platform-specific logic or rendering issues in the mobile implementation.

The fix will ensure the Google Sign-In button is visible and functional on mobile devices, using Browser::auth() with the reverse client ID scheme for OAuth flow, while preserving existing desktop and web functionality.

## Glossary

- **Bug_Condition (C)**: The condition that triggers the bug - when a user accesses the Login or Register page on an Android mobile device, the "Sign with Google" button is not visible or not functional
- **Property (P)**: The desired behavior - the button should be visible, tappable, and should open Google OAuth flow using Browser::auth() with reverse client ID scheme
- **Preservation**: Existing desktop and web OAuth flows that must remain unchanged by the fix
- **social-buttons.blade.php**: The Blade component in `resources/views/filament/user/social-buttons.blade.php` that renders authentication buttons including Google Sign-In
- **NativeServiceProvider::isNativeMobile()**: Static method that detects if the current request is from a native mobile app (Android/iOS)
- **Browser::auth()**: NativePHP Mobile method that opens an in-app browser (Custom Tabs on Android, SFSafariViewController on iOS) for OAuth authentication
- **Reverse Client ID Scheme**: OAuth redirect pattern `com.googleusercontent.apps.CLIENT_ID:/oauth2redirect` that allows mobile apps to receive OAuth callbacks without a public server
- **SocialiteController**: The Laravel controller in `app/Http/Controllers/Auth/SocialiteController.php` that handles Google OAuth redirect and callback logic

## Bug Details

### Bug Condition

The bug manifests when a user accesses the Login page (`/user/login`) or Register page (`/user/register`) in the Android mobile application. The "Sign with Google" button is either not rendered in the DOM, rendered but not visible (CSS/display issue), or rendered but non-functional (JavaScript/click handler issue).

**Formal Specification:**
```
FUNCTION isBugCondition(input)
  INPUT: input of type PageAccessEvent {
    platform: RuntimePlatform,
    route: string,
    userAgent: string
  }
  OUTPUT: boolean
  
  RETURN (input.platform == RuntimePlatform.MobileAppAndroid OR 
          (input.userAgent CONTAINS "Android" AND input.userAgent CONTAINS "wv)"))
         AND (input.route == "/user/login" OR input.route == "/user/register")
         AND NOT googleButtonIsVisibleAndFunctional()
END FUNCTION

FUNCTION googleButtonIsVisibleAndFunctional()
  buttonElement = DOM.querySelector('button containing "Masuk Dengan Google"')
  
  IF buttonElement == null THEN
    RETURN false  // Not rendered
  END IF
  
  IF buttonElement.offsetWidth == 0 OR buttonElement.offsetHeight == 0 THEN
    RETURN false  // Not visible (CSS hidden)
  END IF
  
  IF buttonElement.onclick == null OR buttonElement.disabled == true THEN
    RETURN false  // Not functional
  END IF
  
  RETURN true
END FUNCTION
```

### Examples

- **Example 1 - Android Emulator Login Page**: User opens `/user/login` on Android emulator (API 30). Expected: Google button visible below Login button. Actual: Button not visible in UI.

- **Example 2 - Android Physical Device Register Page**: User opens `/user/register` on physical Android device. Expected: Google button visible with Google logo icon. Actual: Button missing from authentication options.

- **Example 3 - iOS Mobile App**: User opens `/user/login` on iOS device. Expected: Google button visible and functional. Actual: [Needs verification - likely also affected].

- **Edge Case - Desktop Electron**: User opens `/user/login` on desktop Electron app. Expected: Google button visible and functional with standard OAuth flow. Actual: Works correctly (no bug).

## Expected Behavior

### Preservation Requirements

**Unchanged Behaviors:**
- Desktop Electron application Google Sign-In must continue to work with the standard OAuth redirect flow
- Web browser Google Sign-In must continue to work with the standard OAuth redirect flow (`/auth/{provider}/callback`)
- Mobile OAuth callback handling via reverse client ID scheme must continue to function for existing mobile OAuth flows
- Email/password login on all platforms must remain unaffected
- The agreement checkbox and remember me checkbox validation must continue to work
- Visual styling and layout of authentication pages must remain consistent across platforms

**Scope:**
All inputs that do NOT involve accessing the Login or Register pages on Android mobile app should be completely unaffected by this fix. This includes:
- Desktop Electron authentication flows
- Web browser authentication flows (Chrome, Firefox, Safari, Edge)
- Mobile web browser authentication (not the native app)
- Other mobile platform implementations (iOS - if already working)
- Password reset flows
- OTP verification flows

## Hypothesized Root Cause

Based on the bug description and codebase analysis, the most likely issues are:

1. **Platform Detection Logic Failure**: The `NativeServiceProvider::isNativeMobile()` method may not correctly detect Android WebView environment in the emulator, causing the button to be hidden by conditional rendering logic that expects mobile detection.

2. **AlpineJS/Livewire Hydration Issue**: The `social-buttons.blade.php` component uses AlpineJS `x-data` and `x-on:click` directives. Android WebView (especially older versions) may fail to initialize AlpineJS properly, leaving the button rendered but non-functional.
   - The button's click handler uses `x-on:click="... window.location.href = googleUrl;"` which should work, but Alpine may not be hydrating correctly
   - The `x-bind:disabled` attribute may be incorrectly disabling the button

3. **Browser::auth() API Unavailable**: The mobile OAuth flow relies on `Native\Mobile\Browser::auth()` to open the in-app browser. If this API is not available or not properly initialized in the Android WebView:
   - The `redirectMobile()` method in SocialiteController checks `class_exists(Browser::class) && function_exists('nativephp_call')`
   - If this check fails, it falls back to `redirect($authUrl)` which may not work in WebView
   - The button may redirect but fail to open the OAuth flow

4. **CSS Display/Visibility Issue**: The button may be rendered in the DOM but hidden by CSS rules specific to mobile viewports or Android WebView.
   - Media queries in `social-buttons.blade.php` or global CSS may hide the button
   - `display: none` or `visibility: hidden` may be applied conditionally

5. **Route/URL Resolution Issue**: The `$googleRedirectUrl = '/auth/google/redirect';` may not resolve correctly in the Android WebView environment.
   - NativeServiceProvider::normalizeUrl() is explicitly NOT used in social-buttons.blade.php (commented as "normalizeUrl() tidak dipakai untuk navigasi halaman agar tidak buka Chrome di mobile")
   - Relative URLs may fail to route correctly in WebView

6. **Blade Component Not Included**: The `@include('filament.user.social-buttons')` directive in login.blade.php and register.blade.php may not be executing correctly on mobile, though this is less likely since the desktop version works.

## Correctness Properties

Property 1: Bug Condition - Google Sign-In Button Visibility and Functionality on Mobile

_For any_ page access event where the user opens the Login or Register page on an Android mobile device (isBugCondition returns true), the fixed implementation SHALL render a visible and functional "Sign with Google" button that, when tapped, opens the Google OAuth authentication flow using Browser::auth() with the reverse client ID scheme, and upon successful authentication, logs the user into the Filament user panel.

**Validates: Requirements 2.1, 2.2, 2.3, 2.4**

Property 2: Preservation - Non-Mobile OAuth Flow Behavior

_For any_ page access event where the user opens authentication pages on desktop Electron app or web browser (NOT isBugCondition), the fixed implementation SHALL produce exactly the same OAuth behavior as the original implementation, preserving the standard Google OAuth redirect flow, callback URL handling, and user login process without any changes to functionality or user experience.

**Validates: Requirements 3.1, 3.2, 3.3, 3.4, 3.5**

## Fix Implementation

### Changes Required

Assuming our root cause analysis is correct, we need to investigate and fix the following areas:

**File**: `resources/views/filament/user/social-buttons.blade.php`

**Function/Component**: Google Sign-In Button Rendering

**Specific Changes**:
1. **Add Debug Logging**: Add temporary logging to verify the button is actually rendered in the DOM on Android WebView
   - Add `console.log()` statements to track Alpine initialization
   - Log button element existence, dimensions, and event handlers
   - Log platform detection results (`isNativeMobile`, user agent string)

2. **Verify Platform Detection**: Ensure `NativeServiceProvider::isNativeMobile()` correctly detects Android WebView
   - Add explicit check for Android WebView user agent pattern
   - Consider adding fallback detection using JavaScript `navigator.userAgent`
   - Log detection results to verify correct platform identification

3. **Fix AlpineJS Hydration**: Ensure AlpineJS initializes correctly on Android WebView
   - Verify Alpine version compatibility with Android WebView
   - Consider using native JavaScript click handlers as fallback if Alpine fails
   - Test on older Android WebView versions (Android 7.0+)
   - Add explicit `x-init` to log Alpine initialization

4. **Verify Browser::auth() Availability**: Add fallback logic if Browser::auth() is unavailable
   - Log whether `class_exists(Browser::class)` and `function_exists('nativephp_call')` return true
   - If unavailable, provide clear error message or alternative flow
   - Consider graceful degradation to standard redirect if API unavailable

5. **Add CSS Visibility Guards**: Ensure no CSS rules hide the button on mobile
   - Review all media queries that might affect button visibility
   - Add explicit `display: block !important` for mobile platforms if needed
   - Test with Android Chrome DevTools device emulation

6. **Fix Route Resolution**: Verify `/auth/google/redirect` resolves correctly
   - Test URL generation in Android WebView console
   - Consider using `route('auth.redirect', ['provider' => 'google'])` instead of hardcoded path
   - Log the final URL before navigation

7. **Add Mobile-Specific Rendering**: If conditional rendering is needed, add explicit mobile logic
   - Use `@php $isMobile = \App\Providers\NativeServiceProvider::isNativeMobile(); @endphp`
   - Add mobile-specific button variant if needed for better Android WebView compatibility
   - Consider separate button implementation for mobile vs desktop if necessary

**File**: `app/Http/Controllers/Auth/SocialiteController.php`

**Function**: `redirectMobile()`

**Specific Changes**:
1. **Add Error Logging**: Log all failures in Browser::auth() call
2. **Verify Reverse Client ID**: Ensure CLIENT_ID is correctly read from config
3. **Add Fallback Flow**: If Browser::auth() fails, provide alternative redirect mechanism
4. **Return JSON for AJAX**: Ensure mobile JavaScript can detect success/failure of Browser::auth() call

**File**: `app/Providers/NativeServiceProvider.php`

**Function**: `isNativeMobile()`

**Specific Changes**:
1. **Add Android Emulator Detection**: Explicitly detect Android emulator environment
2. **Log Detection Signals**: Log all detection signals (NATIVEPHP_RUNNING, user agent, remote addr, etc.)
3. **Add Fallback Detection**: Consider JavaScript-based detection as fallback if PHP detection fails

## Testing Strategy

### Validation Approach

The testing strategy follows a two-phase approach: first, surface counterexamples that demonstrate the bug on unfixed code, then verify the fix works correctly and preserves existing behavior.

### Exploratory Bug Condition Checking

**Goal**: Surface counterexamples that demonstrate the bug BEFORE implementing the fix. Confirm or refute the root cause analysis. If we refute, we will need to re-hypothesize.

**Test Plan**: Write tests that simulate Android WebView page loads and inspect the DOM for button presence, visibility, and functionality. Run these tests on the UNFIXED code to observe failures and understand the root cause.

**Test Cases**:
1. **Android Emulator Login Page Load**: Open `/user/login` in Android emulator, inspect DOM for button element (will fail on unfixed code - button missing or hidden)
2. **Android Emulator Register Page Load**: Open `/user/register` in Android emulator, inspect DOM for button element (will fail on unfixed code - button missing or hidden)
3. **Button Click Simulation**: Simulate tapping the Google button if it exists, verify Browser::auth() is called (may fail on unfixed code - handler not attached)
4. **Platform Detection Verification**: Log `NativeServiceProvider::isNativeMobile()` result on Android device (may fail on unfixed code - incorrect detection)

**Expected Counterexamples**:
- Button element not found in DOM (`querySelector` returns null)
- Button element exists but has `display: none` or `visibility: hidden`
- Button element exists and visible but click handler not attached
- AlpineJS not initialized (no `x-data` instance created)
- Browser::auth() returns false (API unavailable)
- Possible causes: platform detection failure, AlpineJS hydration failure, Browser API unavailable, CSS hiding button, route resolution failure

### Fix Checking

**Goal**: Verify that for all inputs where the bug condition holds, the fixed function produces the expected behavior.

**Pseudocode:**
```
FOR ALL input WHERE isBugCondition(input) DO
  result := loadLoginOrRegisterPage(input.platform, input.route)
  ASSERT googleButtonIsVisibleAndFunctional(result.dom)
  
  result := simulateButtonClick(result.dom.googleButton)
  ASSERT result.browserAuthCalled == true
  ASSERT result.authUrlOpened == true
END FOR
```

**Test Implementation**:
- Use Dusk browser tests to load pages on Android WebView
- Use JavaScript DOM queries to verify button existence and visibility
- Use click simulation to verify button functionality
- Use network monitoring to verify OAuth flow initiation
- Test on multiple Android versions (7.0+, API 24+)
- Test on both Android emulator and physical devices

### Preservation Checking

**Goal**: Verify that for all inputs where the bug condition does NOT hold, the fixed function produces the same result as the original function.

**Pseudocode:**
```
FOR ALL input WHERE NOT isBugCondition(input) DO
  originalResult := originalImplementation(input)
  fixedResult := fixedImplementation(input)
  
  ASSERT originalResult.buttonVisible == fixedResult.buttonVisible
  ASSERT originalResult.oauthFlowType == fixedResult.oauthFlowType
  ASSERT originalResult.callbackRoute == fixedResult.callbackRoute
  ASSERT originalResult.userLoggedIn == fixedResult.userLoggedIn
END FOR
```

**Testing Approach**: Property-based testing is recommended for preservation checking because:
- It generates many test cases automatically across the input domain
- It catches edge cases that manual unit tests might miss
- It provides strong guarantees that behavior is unchanged for all non-buggy inputs

**Test Plan**: Observe behavior on UNFIXED code first for desktop and web platforms, then write property-based tests capturing that behavior.

**Test Cases**:
1. **Desktop Electron OAuth Preservation**: Observe that desktop Google Sign-In works correctly on unfixed code (button visible, standard OAuth flow), then write test to verify this continues after fix
2. **Web Browser OAuth Preservation**: Observe that web Google Sign-In works correctly on unfixed code (button visible, standard OAuth flow), then write test to verify this continues after fix
3. **Email/Password Login Preservation**: Observe that email/password login works correctly on unfixed code across all platforms, then write test to verify this continues after fix
4. **Mobile Web Browser Preservation**: Observe that mobile web browser (not native app) authentication works correctly, then write test to verify this continues after fix

### Unit Tests

- Test `NativeServiceProvider::isNativeMobile()` with various user agent strings (Android WebView, iOS WebView, Desktop, Web)
- Test `SocialiteController::redirect()` with mobile and non-mobile request headers
- Test `SocialiteController::redirectMobile()` with valid and invalid configurations
- Test button rendering in social-buttons.blade.php with mocked platform contexts
- Test AlpineJS click handler attachment and execution
- Test Browser::auth() call with mocked NativePHP environment

### Property-Based Tests

- Generate random Android WebView user agent strings and verify button always renders and functions
- Generate random platform configurations (mobile app, desktop app, web) and verify correct OAuth flow selection
- Generate random page routes and verify Google button only appears on login/register pages
- Generate random button states (enabled/disabled, visible/hidden) and verify correct rendering based on agreement/remember checkboxes

### Integration Tests

- Test full Google OAuth flow on Android emulator from button click to successful login
- Test full Google OAuth flow on desktop Electron from button click to successful login
- Test full Google OAuth flow on web browser from button click to successful login
- Test reverse client ID scheme callback handling on Android mobile
- Test deep link handling after OAuth callback on Android mobile
- Test token verification and user login after mobile OAuth completion
- Test error handling when OAuth fails (network error, user cancels, invalid credentials)
- Test visual feedback (loading spinner, disabled state) during OAuth process
