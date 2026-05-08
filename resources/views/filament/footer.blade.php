@php
    $isAuth = str_contains(request()->route()?->getName() ?? '', 'auth');
@endphp

<footer
    class="fixed bottom-0 left-0 right-0 z-50 flex items-center justify-center transition-all duration-300 {{ $isAuth ? 'fi-auth-footer' : 'h-14 bg-white/80 backdrop-blur-md border-t border-gray-200/50 dark:bg-gray-950/80 dark:border-white/5 pb-[env(safe-area-inset-bottom)]' }}"
    style="pointer-events: none;">
    <div class="px-6 py-2 text-[11px] font-semibold tracking-wider text-center uppercase text-gray-400 dark:text-gray-500 drop-shadow-sm" style="pointer-events: auto;">
        &copy; {{ date('Y') }} <span class="text-primary-600 dark:text-primary-400">{{ __(config('app.name')) }}</span>. {{ __('All rights reserved') }}
    </div>
</footer>

<style>
    /* Override for Auth Page */
    .fi-auth-footer {
        position: static !important;
        background-color: transparent !important;
        border-top: none !important;
        height: auto !important;
        margin-top: 1.5rem;
        padding-bottom: 2rem !important;
    }

    .dark .fi-auth-footer {
        background-color: transparent !important;
    }

    /* Seamless spacing for main content */
    body:not(:has(.fi-auth-footer)) .fi-layout,
    body:not(:has(.fi-auth-footer)) .fi-main,
    body:not(:has(.fi-auth-footer)) .fi-content {
        padding-bottom: calc(4rem + env(safe-area-inset-bottom)) !important;
    }

    /* Subtle hover effect for the text */
    .drop-shadow-sm:hover {
        opacity: 0.8;
        transform: translateY(-1px);
        transition: all 0.3s ease;
    }
</style>