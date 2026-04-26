<?php

namespace App\Providers\Filament;

use App\Filament\User\Auth\Login;
use App\Filament\User\Auth\OtpEmailVerificationPrompt;
use App\Filament\User\Auth\OtpRequestPasswordReset;
use App\Filament\User\Auth\OtpResetPassword;
use App\Filament\User\Auth\Register;
use App\Filament\User\Auth\VerifyOtp;
use App\Filament\User\Pages\Dashboard;
use App\Http\Middleware\SetLocale;
use Filament\Enums\ThemeMode;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\MaxWidth;
use Filament\Widgets;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Contracts\View\View;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Filament\Navigation\MenuItem;
use Illuminate\Support\Facades\Auth;
use App\Filament\User\Pages\EditProfilePage;

class UserPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('user')
            ->path('user')
            ->login(Login::class)
            ->registration(Register::class)
            ->passwordReset(
                OtpRequestPasswordReset::class,
                OtpResetPassword::class
            )
            ->emailVerification(OtpEmailVerificationPrompt::class)
            // ->sidebarFullyCollapsibleOnDesktop()
            ->brandName(fn() => __('Dekorasi Bunga Pernikahan'))
            ->brandLogo(asset('images/logo.png'))
            ->brandLogoHeight('3rem')
            // ->simplePageMaxContentWidth(MaxWidth::Small)
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
            ->databaseNotifications()

            ->renderHook(
                'panels::global-search.after',
                fn (): View => view('filament.filament-language-switcher.language-switcher')
            )
            ->renderHook(
                'panels::auth.form.before',
                fn (): View => view('filament.filament-language-switcher.language-switcher')
            )
            ->renderHook(
                'panels::head.end',
                fn () => view('filament.snap-script'),
            )
            ->renderHook(
                'panels::styles.after',
                fn (): string => \Illuminate\Support\Facades\Blade::render('@vite(\'resources/css/app.css\')')
            )
            ->renderHook(
                'panels::footer',
                fn (): ?View => ! str_contains(request()->route()?->getName() ?? '', 'auth') ? view('filament.footer') : null
            )
            ->renderHook(
                'panels::auth.login.form.after',
                fn (): View => view('filament.footer')
            )
            ->discoverResources(in: app_path('Filament/User/Resources'), for: 'App\\Filament\\User\\Resources')
            ->discoverPages(in: app_path('Filament/User/Pages'), for: 'App\\Filament\\User\\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/User/Widgets'), for: 'App\\Filament\\User\\Widgets')
            ->widgets([
                // Widgets are auto-discovered from the Widgets directory
            ])
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
    }
}
