<?php

namespace App\Filament\User\Resources\PackageResource\Pages;

use App\Filament\User\Resources\PackageResource;
use App\Filament\User\Pages\CbirSearchPage;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewPackage extends ViewRecord
{
    protected static string $resource = PackageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('back')
                ->label(__('Kembali'))
                ->url(fn () => str_contains(url()->previous(), 'cbir-search')
                    ? CbirSearchPage::getUrl()
                    : static::getResource()::getUrl('index'))
                ->color('gray')->button()
                ->icon('heroicon-o-arrow-left'),

            // No edit/delete for users ideally, maybe a "Book Now" or "Chat"
        ];
    }
}
