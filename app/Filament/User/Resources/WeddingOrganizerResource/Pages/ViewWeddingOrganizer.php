<?php

namespace App\Filament\User\Resources\WeddingOrganizerResource\Pages;

use App\Filament\User\Resources\WeddingOrganizerResource;
use App\Filament\User\Widgets\StatsOverview;
use App\Filament\User\Widgets\UnifiedHistoryWidget;
use App\Filament\User\Widgets\UserOrdersChart;
use App\Filament\User\Widgets\UserSpendingChart;
use App\Models\WeddingOrganizer;
use Filament\Resources\Pages\ViewRecord;

class ViewWeddingOrganizer extends ViewRecord
{
    protected static string $resource = WeddingOrganizerResource::class;

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getResource()::getModel()::count();
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return static::getResource()::getNavigationLabel();
    }

    /**
     * Set the record to ID 1 automatically.
     */
    public function mount(int|string|null $record = null): void
    {
        // Langsung arahkan router ke record 1 (bisa asli atau placeholder memori)
        $this->record = $this->resolveRecord(1);

        parent::mount(1);
    }

    protected function resolveRecord(int|string $key): WeddingOrganizer
    {
        return WeddingOrganizer::getBrand() ?? new WeddingOrganizer;
    }

    /**
     * Set the record to ID 1 automatically.
     */
    public function getTitle(): string
    {
        return __('Beranda');
    }

    public function getBreadcrumbs(): array
    {
        return [];
    }

    public function getHeaderWidgets(): array
    {
        return [
            StatsOverview::class,
            UserOrdersChart::class,
            UserSpendingChart::class,
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            UnifiedHistoryWidget::class,
        ];
    }
}
