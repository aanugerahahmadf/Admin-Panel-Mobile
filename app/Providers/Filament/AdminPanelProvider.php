<?php

namespace App\Providers\Filament;

use Filament\Enums\ThemeMode;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\MenuItem;
use Filament\Navigation\NavigationGroup;
use App\Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\MaxWidth;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use App\Filament\Pages\EditProfilePage;
use App\Http\Middleware\VerifyCsrfToken;
use App\Http\Middleware\SuperAdmin;
use Livewire\Livewire;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login(\App\Filament\Auth\Login::class)
            ->registration(\App\Filament\Auth\Register::class)
            ->passwordReset(
                \App\Filament\Auth\OtpRequestPasswordReset::class,
                \App\Filament\Auth\OtpResetPassword::class
            )
            ->emailVerification(\App\Filament\Auth\OtpEmailVerificationPrompt::class)
            ->sidebarFullyCollapsibleOnDesktop()
            ->brandName(config('app.name'))
            ->simplePageMaxContentWidth(MaxWidth::Small)
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
            ->databaseNotifications()
            ->plugins([
                \App\Providers\Filament\LanguageSwitcherPlugin::make(),
            ])
            ->userMenuItems([
                'profile' => MenuItem::make()
                    ->label(fn (): string => Auth::user()?->full_name ?? __('Profil'))
                    ->url(fn (): string => EditProfilePage::getUrl())
                    ->icon('eos-account-circle')
                    ->visible(fn (): bool => Auth::check()),
            ])
            ->navigationGroups([
                NavigationGroup::make()->label(__('Pengguna')),
                NavigationGroup::make()->label(__('Blog & Media')),
                NavigationGroup::make()->label(__('Studio')),
                NavigationGroup::make()->label(__('Transaksi')),
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                \App\Filament\Widgets\StatsOverview::class,
                \App\Filament\Widgets\RevenueChart::class,
                \App\Filament\Widgets\OrdersChart::class,
                \App\Filament\Widgets\RecentOrders::class,
            ])
            ->middleware([
                VerifyCsrfToken::class,
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                \App\Http\Middleware\SetLocale::class,
                ShareErrorsFromSession::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
                SuperAdmin::class,
            ])
            ->routes(function (Panel $panel) {
                \App\Filament\Auth\VerifyOtp::registerRoutes($panel);
            })
            ->bootUsing(function () {
                Livewire::component('app.filament.auth.login', \App\Filament\Auth\Login::class);
                Livewire::component('app.filament.auth.register', \App\Filament\Auth\Register::class);
                Livewire::component('app.filament.auth.otp-request-password-reset', \App\Filament\Auth\OtpRequestPasswordReset::class);
                Livewire::component('app.filament.auth.otp-reset-password', \App\Filament\Auth\OtpResetPassword::class);
                Livewire::component('app.filament.auth.verify-otp', \App\Filament\Auth\VerifyOtp::class);
                Livewire::component('app.filament.auth.otp-email-verification-prompt', \App\Filament\Auth\OtpEmailVerificationPrompt::class);
            });
    }
}
