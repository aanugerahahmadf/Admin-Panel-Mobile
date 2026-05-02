<?php

namespace App\Providers\Filament;

use App\Filament\User\Auth\Login;
use App\Filament\User\Auth\OtpEmailVerificationPrompt;
use App\Filament\User\Auth\OtpRequestPasswordReset;
use App\Filament\User\Auth\OtpResetPassword;
use App\Filament\User\Auth\Register;
use App\Filament\User\Auth\VerifyOtp;
use App\Filament\User\Pages\Dashboard;
use App\Filament\User\Pages\EditProfilePage;
use App\Http\Middleware\MidtransCspMiddleware;
use App\Http\Middleware\SetLocale;
use App\Providers\NativeServiceProvider;
use Filament\Enums\ThemeMode;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\MenuItem;
use Filament\Navigation\NavigationGroup;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\MaxWidth;
use Illuminate\Contracts\View\View;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class UserPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        $isMobile = NativeServiceProvider::isNativeMobile();

        $panel = $panel
            ->id('user')
            ->path('user')
            ->login(Login::class)
            ->registration(Register::class)
            ->passwordReset(
                OtpRequestPasswordReset::class,
                OtpResetPassword::class
            )
            ->emailVerification(OtpEmailVerificationPrompt::class)
            ->brandName(fn () => __('Dekorasi Bunga Pernikahan'))
            ->brandLogo(fn () => NativeServiceProvider::normalizeUrl(asset('images/logo.png')))
            ->brandLogoHeight('3rem')
            ->colors([
                'danger' => Color::Rose,
                'gray' => Color::Gray,
                'info' => Color::Blue,
                'primary' => Color::Yellow,
                'success' => Color::Emerald,
                'warning' => Color::Orange,
            ])
            ->font('Inter')
            ->defaultThemeMode(ThemeMode::System)
            ->topNavigation()
            ->maxContentWidth(MaxWidth::Full)
            ->spa()
            ->globalSearch()
            ->renderHook(
                'panels::global-search.after',
                fn (): ?View => (! NativeServiceProvider::isNativeMobile() && ! preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino/i', request()->userAgent()))
                    ? view('filament.filament-language-switcher.language-switcher')
                    : null
            )
            ->renderHook(
                'panels::auth.form.before',
                fn (): ?View => (! NativeServiceProvider::isNativeMobile() && ! preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino/i', request()->userAgent()))
                    ? view('filament.filament-language-switcher.language-switcher')
                    : null
            )
            ->renderHook(
                'panels::styles.after',
                fn (): string => Blade::render('@vite(\'resources/css/app.css\')')
            )
            ->renderHook(
                'panels::footer',
                fn (): ?View => ! str_contains(request()->route()?->getName() ?? '', 'auth') ? view('filament.footer') : null
            )
            ->discoverResources(in: app_path('Filament/User/Resources'), for: 'App\\Filament\\User\\Resources')
            ->discoverPages(in: app_path('Filament/User/Pages'), for: 'App\\Filament\\User\\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/User/Widgets'), for: 'App\\Filament\\User\\Widgets')
            ->widgets([])
            ->navigationGroups([
                NavigationGroup::make()->label(fn () => __('Beranda')),
                NavigationGroup::make()->label(fn () => __('Belanja & Jelajahi')),
                NavigationGroup::make()->label(fn () => __('Transaksi & Aktivitas')),
                NavigationGroup::make()->label(fn () => __('Pesan')),
            ])
            ->userMenuItems([
                'profile' => MenuItem::make()
                    ->label(fn (): string => Auth::user()?->full_name ?? __('Profil'))
                    ->url(fn (): string => EditProfilePage::getUrl())
                    ->icon('eos-account-circle')
                    ->visible(fn (): bool => Auth::check()),
            ])
            ->middleware([
                MidtransCspMiddleware::class,
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                SetLocale::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->routes(function (Panel $panel): void {
                VerifyOtp::registerRoutes($panel);
            });

        // Database notifications — aktif di web, nonaktif di mobile (hemat polling)
        if (! $isMobile) {
            $panel->databaseNotifications();
        }

        // snap-script — Handled globally in AppServiceProvider for both Admin and User panels

        return $panel;
    }
}
