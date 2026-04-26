<?php

namespace App\Filament\User\Resources\HistoryResource\Pages;

use App\Filament\User\Resources\HistoryResource;
use Filament\Resources\Pages\ListRecords;

class ListHistories extends ListRecords
{
    protected static string $resource = HistoryResource::class;

    public function getTabs(): array
    {
        return [
            'all' => \Filament\Resources\Components\Tab::make(__('Semua'))
                ->badge(fn() => \App\Models\History::where('user_id', \Filament\Facades\Filament::auth()->id())->count()),
            'order' => \Filament\Resources\Components\Tab::make(__('Pembelian'))
                ->modifyQueryUsing(fn ($query) => $query->where('type', 'order'))
                ->badge(fn() => \App\Models\History::where('user_id', \Filament\Facades\Filament::auth()->id())->where('type', 'order')->count())
                ->badgeColor('info'),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [

        ];
    }
}
