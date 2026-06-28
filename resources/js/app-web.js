/**
 * Web Platform Entry Point
 *
 * Optimized for browser environments with WebRTC support.
 * Used when running: php artisan serve
 *
 * Requirements: 4.1, 4.5
 */

// ===================================
// Core Dependencies (All Platforms)
// ===================================
import './bootstrap';
import './advanced-file-upload';
import './sidebar-auto-expand';
import 'emoji-picker-element';
import './emoji-picker';
import './pdf-preview-plugin';
import './firebase-client';

// ===================================
// Web-Specific Components
// ===================================
// Web platform uses WebRTC getUserMedia for camera access (Req 6.3)
// Native camera / file-system APIs are intentionally excluded here (Req 4.5)
//
// phpProtocolAdapter is NOT imported — it is only needed by the Mobile entry
// point for iOS php:// protocol handling.

// ===================================
// Dark Mode Synchronization
// ===================================
function syncTheme() {
    const theme = localStorage.getItem('theme');
    const isDark =
        theme === 'dark' ||
        (!theme && window.matchMedia('(prefers-color-scheme: dark)').matches);

    document.documentElement.classList.toggle('dark', isDark);
    document.documentElement.style.colorScheme = isDark ? 'dark' : 'light';
}

syncTheme();
window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', syncTheme);

// ===================================
// Platform Capabilities (Req 4.5, 5.1–5.9)
// ===================================
// Exposes a consistent __PLATFORM__ object so Blade views and Livewire
// components can conditionally render platform-specific UI without requiring
// knowledge of the PHP-side PlatformMode.
window.__PLATFORM__ = {
    type: 'web',
    mode: 'web',

    // Feature flags — mirrors PlatformFeatureRegistry (Req 5.1–5.9)
    supportsWebRTC: true,               // Browser WebRTC getUserMedia API (Req 6.3)
    supportsNativeCamera: false,        // Native camera APIs — Mobile/Desktop only
    supportsFileSystem: false,          // Native file-system access — Mobile/Desktop only
    supportsPushNotifications: false,   // Native push notifications — Mobile only
    supportsDesktopNotifications: false,// Desktop system notifications — Desktop only
    supportsAppBadge: false,            // App-badge updates — Mobile only
    supportsAutoUpdates: false,         // Auto-update functionality — Mobile/Desktop only

    // Camera mode for CBIR feature (Req 6.4)
    cameraMode: 'webrtc',              // Uses WebRTC getUserMedia
};

// ===================================
// Conditional: WebRTC Camera Helper
// ===================================
// Lazy-load WebRTC utilities only when the browser supports them (Req 4.5)
if (window.__PLATFORM__.supportsWebRTC && navigator.mediaDevices) {
    /**
     * Opens the user's camera and returns a MediaStream.
     * Used by the CBIR browse modal for web-based image capture.
     *
     * @param {MediaStreamConstraints} constraints
     * @returns {Promise<MediaStream>}
     */
    window.__PLATFORM__.openCamera = async (constraints = { video: true }) => {
        try {
            return await navigator.mediaDevices.getUserMedia(constraints);
        } catch (err) {
            console.error('[Web Platform] Camera access denied:', err);
            throw new Error(
                'Camera access denied. Please allow camera access in your browser settings.'
            );
        }
    };
}

if (import.meta.env.DEV) {
    console.log('[Web Platform] Initialized — WebRTC camera support enabled');
}
