<?php

namespace App\Filament\User\Resources\PackageResource\Pages;

use Filament\Actions;

use App\Filament\User\Resources\PackageResource;
use Filament\Resources\Pages\ViewRecord;

class ViewPackage extends ViewRecord
{
    protected static string $resource = PackageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('back')
                ->label(__('Kembali'))
                ->url(fn() => static::getResource()::getUrl('index'))
                ->color('gray')->button()
                ->icon('heroicon-o-arrow-left'),

            // No edit/delete for users ideally, maybe a "Book Now" or "Chat"
        ];
    }
}

