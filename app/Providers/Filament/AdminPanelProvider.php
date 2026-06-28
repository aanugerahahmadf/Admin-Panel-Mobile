<?php

namespace App\Providers\Filament;

use App\Filament\Admin\Auth\Login;
use App\Filament\Admin\Auth\OtpEmailVerificationPrompt;
use App\Filament\Admin\Auth\OtpRequestPasswordReset;
use App\Filament\Admin\Auth\OtpResetPassword;
use App\Filament\Admin\Auth\Register;
use App\Filament\Admin\Auth\VerifyOtp;
use App\Filament\Admin\Pages\Dashboard;
use App\Filament\Admin\Pages\EditProfilePage;
use App\Filament\Admin\Widgets\OrdersChart;
use App\Filament\Admin\Widgets\RecentOrders;
use App\Filament\Admin\Widgets\RevenueChart;
use App\Filament\Admin\Widgets\StatsOverview;
use App\Http\Middleware\MidtransCspMiddleware;
use App\Http\Middleware\SetLocale;
use App\Http\Middleware\ClerkFilamentAuth;
use App\Http\Middleware\SuperAdmin;
use App\Http\Middleware\VerifyCsrfToken;
use Filament\Enums\ThemeMode;
use Filament\Http\Middleware\Authenticate;
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
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('admin')
            ->path('admin')
            ->authGuard('web')
            ->login(Login::class)
            // ->registration(Register::class)
            ->passwordReset(
                OtpRequestPasswordReset::class,
                OtpResetPassword::class
            )
            ->emailVerification(OtpEmailVerificationPrompt::class)
            // ->sidebarFullyCollapsibleOnDesktop()
            ->brandName(fn () => __('Dekorasi Bunga Pernikahan'))
            ->brandLogo(fn () => '/images/logo.png')
            ->brandLogoHeight('5rem')
            // ->simplePageMaxContentWidth(MaxWidth::Small)
            ->colors([
                'danger' => Color::Rose,
                'gray' => Color::Gray,
                'info' => Color::Blue,
                'primary' => Color::Indigo,
                'success' => Color::Emerald,
                'warning' => Color::Orange,
            ])
            ->defaultThemeMode(ThemeMode::System)
            ->topNavigation()
            // ->maxContentWidth(MaxWidth::Full)
            ->spa()
            ->databaseNotifications()
            // Language switcher, PWA, platform runtime — PlatformSupportServiceProvider (global)
            ->renderHook(
                'panels::styles.after',
                fn (): string => Blade::render('@vite(\'resources/css/app.css\')')
            )
            ->renderHook(
                'panels::footer',
                fn (): ?View => ! str_contains(request()->route()?->getName() ?? '', 'auth') ? view('filament.footer') : null
            )
            ->userMenuItems([
                'profile' => MenuItem::make()
                    ->label(fn (): string => Auth::user()?->full_name ?? __('Profil'))
                    ->url(fn (): string => EditProfilePage::getUrl())
                    ->icon('eos-account-circle')
                    ->visible(fn (): bool => Auth::check()),
            ])
            ->navigationGroups([
                NavigationGroup::make()->label(fn () => __('Beranda')),
                NavigationGroup::make()->label(fn () => __('Data Master')),
                NavigationGroup::make()->label(fn () => __('Transaksi')),
                NavigationGroup::make()->label(fn () => __('Pesan')),
                NavigationGroup::make()->label(fn () => __('Manajemen Legal')),
            ])
            ->discoverResources(in: app_path('Filament/Admin/Resources'), for: 'App\\Filament\\Admin\\Resources')
            ->discoverPages(in: app_path('Filament/Admin/Pages'), for: 'App\\Filament\\Admin\\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Admin/Widgets'), for: 'App\\Filament\\Admin\\Widgets')
            ->widgets([
                StatsOverview::class,
                RevenueChart::class,
                OrdersChart::class,
                RecentOrders::class,
            ])
            ->middleware([
                ClerkFilamentAuth::class,
                MidtransCspMiddleware::class,
                VerifyCsrfToken::class,
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                SetLocale::class,
                ShareErrorsFromSession::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
                SuperAdmin::class,
            ])
            ->routes(function (Panel $panel): void {
                VerifyOtp::registerRoutes($panel);
            });
    }
}
