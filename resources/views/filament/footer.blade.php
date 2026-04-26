@php
    $isAuth = str_contains(request()->route()?->getName() ?? '', 'auth');
@endphp

<footer
    class="fixed bottom-0 left-0 right-0 z-20 flex items-center justify-center h-16 bg-white border-t border-gray-200 dark:bg-gray-900 dark:border-white/10 {{ $isAuth ? 'fi-auth-footer' : '' }}">
    <div class="px-4 text-sm font-medium text-center text-gray-500 dark:text-gray-400">
        &copy; {{ date('Y') }} {{ __(config('app.name')) }}. {{ __('All rights reserved') }}
    </div>
</footer>

<style>
    /* Override for Auth Page */
    .fi-auth-footer {
        position: static !important;
        background-color: transparent !important;
        border-top: none !important;
        height: auto !important;
        margin-top: 0.5rem;
        padding-bottom: 0;
    }

    .dark .fi-auth-footer {
        background-color: transparent !important;
    }

    /* Only add padding if footer is fixed */
    body:not(:has(.fi-auth-footer)) .fi-layout,
    body:not(:has(.fi-auth-footer)) .fi-main,
    body:not(:has(.fi-auth-footer)) .fi-content {
        padding-bottom: 4.5rem !important;
    }
</style>