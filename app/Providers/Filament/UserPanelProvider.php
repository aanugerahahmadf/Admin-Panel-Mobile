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
use App\Filament\User\Pages\MessagesPage;
use App\Filament\User\Pages\ProfilePage;
use App\Filament\User\Resources\CartResource;
use App\Filament\User\Resources\OrderResource;
use App\Filament\User\Resources\ReviewResource;
use App\Http\Middleware\MidtransCspMiddleware;
use App\Http\Middleware\ClerkFilamentAuth;
use App\Http\Middleware\SetLocale;
use Filament\Enums\ThemeMode;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\MenuItem;
use Filament\Navigation\NavigationGroup;
use Filament\Navigation\NavigationItem;
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
            ->brandLogo(fn () => '/images/logo.png')
            ->brandLogoHeight('5rem')
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
            // ->maxContentWidth(MaxWidth::Full)
            ->spa()
            ->unsavedChangesAlerts(false)
            ->collapsibleNavigationGroups()
            ->globalSearch()
            ->renderHook(
                'panels::styles.after',
                fn (): string => Blade::render('@vite(\'resources/css/app.css\')')
            )
            ->renderHook(
                'panels::footer',
                fn (): ?View => (
                    ! str_contains(request()->route()?->getName() ?? '', 'auth')
                    && ! \App\Providers\NativeServiceProvider::isAnyMobile()
                ) ? view('filament.footer') : null
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
                ClerkFilamentAuth::class,
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

        $panel->databaseNotifications();

        // snap-script — Handled globally in AppServiceProvider for both Admin and User panels

        // Mobile bottom navigation bar
        // Selalu dirender ke HTML untuk user panel (kecuali halaman auth).
        // CSS di mobile-bottom-nav.blade.php yang mengontrol visibilitas:
        //   - Desktop (≥ 1024px): display: none
        //   - Mobile (< 1024px): tampil
        // Dengan cara ini bottom nav muncul di:
        //   - Website mobile browser (Android/iOS)
        //   - Native mobile app (Android/iOS)
        //   - Responsive/simulator mode
        $panel->renderHook(
            'panels::body.end',
            fn (): ?\Illuminate\Contracts\View\View => ! str_contains(request()->route()?->getName() ?? '', 'auth')
                ? view('filament.user.components.mobile-bottom-nav', [
                    'items' => $this->getBottomNavItems(),
                    'moreButtonEnabled' => false,
                    'moreButtonLabel' => __('Lainnya'),
                ])
                : null
        );

        return $panel;
    }

    private function getBottomNavItems(): array
    {
        return [
            NavigationItem::make(__('Beranda'))
                ->icon('heroicon-o-home')
                ->activeIcon('heroicon-s-home')
                ->url(Dashboard::getUrl())
                ->isActiveWhen(fn () => in_array(
                    request()->route()?->getName() ?? '',
                    ['filament.user.pages.home', 'filament.user.pages.dashboard']
                ) || str_contains(request()->route()?->getName() ?? '', 'dashboard')),

            NavigationItem::make(__('Pesanan'))
                ->icon('heroicon-o-receipt-percent')
                ->activeIcon('heroicon-s-receipt-percent')
                ->url(OrderResource::getUrl())
                ->isActiveWhen(fn () => str_contains(request()->route()?->getName() ?? '', 'orders')),

            NavigationItem::make(__('Keranjang'))
                ->icon('heroicon-o-shopping-cart')
                ->activeIcon('heroicon-s-shopping-cart')
                ->url(CartResource::getUrl())
                ->isActiveWhen(fn () => str_contains(request()->route()?->getName() ?? '', 'carts')),

            NavigationItem::make(__('Pesan'))
                ->icon('heroicon-o-chat-bubble-left-ellipsis')
                ->activeIcon('heroicon-s-chat-bubble-left-ellipsis')
                ->url(MessagesPage::getUrl())
                ->isActiveWhen(fn () => str_contains(request()->route()?->getName() ?? '', 'messages')),

            NavigationItem::make(__('Profil'))
                ->icon('heroicon-o-user')
                ->activeIcon('heroicon-s-user')
                ->url(ProfilePage::getUrl())
                ->isActiveWhen(fn () => str_contains(request()->route()?->getName() ?? '', 'profile')),
        ];
    }
}
