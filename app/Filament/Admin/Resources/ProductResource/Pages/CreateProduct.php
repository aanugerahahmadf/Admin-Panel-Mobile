<?php

namespace App\Filament\Admin\Resources\ProductResource\Pages;

use Filament\Actions;

use App\Filament\Admin\Resources\ProductResource;
use Filament\Resources\Pages\CreateRecord;

class CreateProduct extends CreateRecord
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
