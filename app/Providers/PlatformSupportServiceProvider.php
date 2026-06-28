<?php

namespace App\Providers;

use Filament\Notifications\Livewire\DatabaseNotifications;
use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;
use Illuminate\Contracts\View\View;
use Illuminate\Support\ServiceProvider;

/**
 * Cross-platform Filament hooks: PWA (desktop install), language switcher, runtime detection.
 * Covers website + desktop app + mobile app on Windows, macOS, Android, and iOS.
 */
class PlatformSupportServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Trigger tersembunyi — bell custom di topbar/index.blade.php.
        // Filament's ->databaseNotifications() di UserPanelProvider sudah menangani
        // rendering modal Livewire secara otomatis di semua halaman (termasuk Cart).
        DatabaseNotifications::trigger('filament-panels::topbar.database-notifications-trigger');


        FilamentView::registerRenderHook(
            PanelsRenderHook::HEAD_END,
            fn (): View => view('filament.components.pwa-head'),
        );

        FilamentView::registerRenderHook(
            PanelsRenderHook::SCRIPTS_AFTER,
            fn (): View => view('filament.components.platform-runtime-script'),
        );

        FilamentView::registerRenderHook(
            PanelsRenderHook::GLOBAL_SEARCH_AFTER,
            function (): View|string {
                // Bell + modal: topbar/index.blade.php + BODY_END hook (semua platform).
                // Di mobile: language switcher disembunyikan.
                if (\App\Providers\NativeServiceProvider::isAnyMobile()) {
                    return '';
                }

                return view('filament.filament-language-switcher.language-switcher');
            },
        );

        // FilamentView::registerRenderHook(
        //     PanelsRenderHook::AUTH_LOGIN_FORM_BEFORE,
        //     fn (): View => view('filament.filament-language-switcher.language-switcher'),
        // );

        // FilamentView::registerRenderHook(
        //     PanelsRenderHook::AUTH_REGISTER_FORM_BEFORE,
        //     fn (): View => view('filament.filament-language-switcher.language-switcher'),
        // );

        // FilamentView::registerRenderHook(
        //     PanelsRenderHook::AUTH_PASSWORD_RESET_REQUEST_FORM_BEFORE,
        //     fn (): View => view('filament.filament-language-switcher.language-switcher'),
        // );
    }
}
