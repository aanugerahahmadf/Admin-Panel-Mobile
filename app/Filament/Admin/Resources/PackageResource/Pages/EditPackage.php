<?php

namespace App\Filament\Admin\Resources\PackageResource\Pages;

use App\Filament\Admin\Resources\PackageResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditPackage extends EditRecord
{
    protected static string $resource = PackageResource::class;

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title(__('Paket Diperbarui'))
            ->body(__('Paket telah berhasil diperbarui.'));
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
