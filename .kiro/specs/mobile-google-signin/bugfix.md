# Bugfix Requirements Document

## Introduction

This document outlines the requirements for fixing the "Sign with Google" button visibility issue in the Android mobile application. Currently, the button does not appear in the Android emulator, preventing users from authenticating via Google OAuth. The desktop Electron application works correctly, but the mobile implementation is broken. This affects the core authentication functionality and blocks users from accessing the Filament user panel through Google Sign-In on mobile devices.

## Bug Analysis

### Current Behavior (Defect)

1.1 WHEN a user opens the welcome page in the Android emulator THEN the "Sign with Google" button does not appear in the authentication options list

1.2 WHEN a user attempts to access Google authentication features on Android mobile THEN the OAuth flow fails to initialize because the button is not rendered

1.3 WHEN the welcome page loads in the Android emulator THEN only "Masuk" (Login) and "Daftar" (Register) buttons are visible, but "Masuk Dengan Google" is missing

### Expected Behavior (Correct)

2.1 WHEN a user opens the welcome page in the Android emulator THEN the system SHALL display the "Sign with Google" button with the Google logo icon in the authentication options list

2.2 WHEN a user taps the "Sign with Google" button on Android mobile THEN the system SHALL open the Google OAuth authentication flow using Browser::auth() with the reverse client ID scheme

2.3 WHEN the welcome page loads in the Android emulator THEN the system SHALL render all three authentication buttons: "Masuk" (Login), "Daftar" (Register), and "Masuk Dengan Google" (Sign in with Google) in a visible and accessible manner

2.4 WHEN a user completes Google authentication on Android mobile THEN the system SHALL process the OAuth callback via the reverse client ID scheme (com.googleusercontent.apps.CLIENT_ID:/oauth2redirect) and log the user into the Filament user panel

### Unchanged Behavior (Regression Prevention)

3.1 WHEN a user accesses the welcome page on desktop Electron application THEN the system SHALL CONTINUE TO display and function correctly with all authentication buttons including Google Sign-In

3.2 WHEN a user accesses the welcome page via web browser THEN the system SHALL CONTINUE TO display and function correctly with standard OAuth redirect flow

3.3 WHEN a user successfully authenticates via email/password login on Android mobile THEN the system SHALL CONTINUE TO work correctly without any impact from Google Sign-In changes

3.4 WHEN a user authenticates via Google on desktop THEN the system SHALL CONTINUE TO process OAuth callbacks correctly using the standard web callback route

3.5 WHEN authenticated users access the application on any platform THEN the system SHALL CONTINUE TO show the "Buka Beranda" (Open Dashboard) button instead of authentication buttons
