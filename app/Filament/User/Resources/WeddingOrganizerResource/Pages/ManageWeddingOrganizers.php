<?php

namespace App\Filament\User\Resources\WeddingOrganizerResource\Pages;

use App\Filament\User\Concerns\HasMobilePagination;
use App\Filament\User\Resources\WeddingOrganizerResource;
use App\Filament\User\Widgets\StatsOverview;
use Filament\Resources\Pages\ManageRecords;

class ManageWeddingOrganizers extends ManageRecords
{
    use HasMobilePagination;

    protected static string $resource = WeddingOrganizerResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    public function getHeaderWidgets(): array
    {
        return [
            StatsOverview::class,
            // \App\Filament\User\Widgets\UserOrdersChart::class,
            // \App\Filament\User\Widgets\UserSpendingChart::class,
        ];
    }

    public function getTitle(): string
    {
        return __('Beranda');
    }
}
