<?php

namespace App\Filament\Admin\Resources\PackageResource\Pages;

use Filament\Actions;

use App\Filament\Admin\Resources\PackageResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreatePackage extends CreateRecord
{
    protected static string $resource = PackageResource::class;

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title(__('Paket Ditambahkan'))
            ->body(__('Paket baru telah berhasil ditambahkan.'));
    }

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
