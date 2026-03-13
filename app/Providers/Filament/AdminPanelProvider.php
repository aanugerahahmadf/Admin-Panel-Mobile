<?php

namespace App\Providers\Filament;

use Filament\Enums\ThemeMode;
use App\Livewire\PersonalInfoComponent;
use App\Livewire\UsernameComponent;
use App\Livewire\MobileSettingsComponent;
use App\Livewire\EditPasswordComponent;
use App\Livewire\BrowserSessionsComponent;
use App\Livewire\DeleteAccountComponent;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\MenuItem;
use Filament\Navigation\NavigationGroup;
use Filament\Pages;
 use App\Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\MaxWidth;
use Filament\View\PanelsRenderHook;
use Filament\Widgets;
use Illuminate\Contracts\View\View;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\Middleware\ShareErrorsFromSession;

use App\Filament\Pages\EditProfilePage;
use App\Http\Middleware\VerifyCsrfToken;
use App\Http\Middleware\SuperAdmin;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login(\App\Filament\Pages\Auth\Login::class)
            ->passwordReset(
                \App\Filament\Pages\Auth\OtpRequestPasswordReset::class,
                \App\Filament\Pages\Auth\OtpResetPassword::class
            )
            ->emailVerification(\App\Filament\Pages\Auth\OtpEmailVerificationPrompt::class)
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
            // ->spa()
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
                // AuthenticateSession::class,
                ShareErrorsFromSession::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
                SuperAdmin::class,
            ]);
    }
}
