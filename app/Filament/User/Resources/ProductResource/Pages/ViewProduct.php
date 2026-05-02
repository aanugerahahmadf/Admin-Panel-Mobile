<?php

namespace App\Filament\User\Resources\ProductResource\Pages;

use Filament\Actions;

use App\Filament\User\Resources\ProductResource;
use Filament\Resources\Pages\ViewRecord;

class ViewProduct extends ViewRecord
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('back')
                ->label(__('Kembali'))
                ->url(fn() => static::getResource()::getUrl('index'))
                ->color('gray')->button()
                ->icon('heroicon-o-arrow-left'),
];
    }
}

