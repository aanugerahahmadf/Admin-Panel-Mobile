<?php

namespace App\Filament\Admin\Resources\UserResource\Pages;

use App\Filament\Admin\Exports\UserExporter;
use App\Filament\Admin\Resources\UserResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ManageRecords;

/**
 * @property-read \App\Filament\Resources\UserResource $resource
 */
class ManageUsers extends ManageRecords
{
    protected static string $resource = UserResource::class;

    public function getTitle(): string
    {
        return static::$title ?? static::getResource()::getTitleCasePluralModelLabel();
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\ExportAction::make()
                ->exporter(UserExporter::class)
                ->label(__('Ekspor Data'))
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success'),
            Actions\CreateAction::make()
                ->label(__('Tambah Pengguna'))
                ->icon('heroicon-o-plus')
                ->successNotification(
                    Notification::make()
                        ->success()
                        ->title(__('Pengguna Ditambahkan'))
                        ->body(__('Data pengguna baru telah berhasil ditambahkan.'))
                ),
        ];
    }
}
