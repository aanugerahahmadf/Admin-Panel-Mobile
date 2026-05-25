@php
    $isMobile = \App\Providers\NativeServiceProvider::isAnyMobile();
    $isAuth   = str_contains(request()->route()?->getName() ?? '', 'auth');
@endphp

<footer
    class="fixed bottom-0 left-0 right-0 z-50 flex items-center justify-center
        {{ $isAuth ? 'fi-auth-footer' : 'fi-main-footer' }}"
    style="pointer-events: none;">
    <div
        class="px-6 py-2 text-[11px] font-semibold text-center text-gray-900 dark:text-gray-100"
        style="pointer-events: auto;">
        &copy; {{ date('Y') }}
        <span class="text-primary-600 dark:text-primary-400">Wedding Flower Decoration</span>.
        All Rights Reserved.
    </div>
</footer>

<style>
    /* ── Main footer (non-auth pages) ─────────────────────────────────── */
    .fi-main-footer {
        height: calc(3.5rem + env(safe-area-inset-bottom, 0px));
        background-color: white;
        border-top: 1px solid rgba(0, 0, 0, 0.08);
        padding-bottom: env(safe-area-inset-bottom, 0px);
    }

    .dark .fi-main-footer {
        background-color: rgb(17 24 39); /* gray-900 */
        border-top-color: rgba(255, 255, 255, 0.10);
    }

    /* ── Auth footer (login/register pages) ───────────────────────────── */
    .fi-auth-footer {
        position: static !important;
        background-color: transparent !important;
        border-top: none !important;
        height: auto !important;
        margin-top: 1.5rem;
        padding-bottom: calc(1.5rem + env(safe-area-inset-bottom, 0px)) !important;
    }

    /* ── Content padding so footer doesn't overlap ────────────────────── */
    body:not(:has(.fi-auth-footer)) .fi-layout,
    body:not(:has(.fi-auth-footer)) .fi-main,
    body:not(:has(.fi-auth-footer)) .fi-content {
        padding-bottom: calc(3.5rem + env(safe-area-inset-bottom, 0px)) !important;
    }
</style>
