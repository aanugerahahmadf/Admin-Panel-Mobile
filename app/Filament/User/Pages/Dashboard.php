<?php

namespace App\Filament\User\Pages;

use App\Filament\User\Resources\WeddingOrganizerResource;
use App\Filament\User\Widgets\LatestBookings;
use App\Filament\User\Widgets\StatsOverview;
use App\Filament\User\Widgets\UserOrdersChart;
use App\Filament\User\Widgets\UserSpendingChart;
use App\Models\WeddingOrganizer;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-home';

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) WeddingOrganizer::query()->count('*');
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return static::getNavigationLabel();
    }

    public function mount(): void
    {
        // Redirect dashboard to studio profile since it's a one-studio application
        redirect()->to(WeddingOrganizerResource::getUrl('index', ['record' => 1]));
    }

    public static function getNavigationGroup(): ?string
    {
        return __('Beranda');
    }

    protected static ?int $navigationSort = 1;

    public static function getNavigationIcon(): ?string
    {
        return static::$navigationIcon;
    }

    public static function getNavigationLabel(): string
    {
        return __('Beranda');
    }

    public function getWidgets(): array
    {
        return [
            StatsOverview::class,
            UserOrdersChart::class,
            UserSpendingChart::class,
            LatestBookings::class,
        ];
    }

    public function getTitle(): string
    {
        return __('Beranda');
    }
}
