<?php

namespace App\Filament\Admin\Resources\BannerResource\Pages;

use App\Filament\Admin\Exports\BannerExporter;
use App\Filament\Admin\Resources\BannerResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ManageRecords;

/**
 * @property-read \App\Filament\Resources\BannerResource $resource
 */
class ManageBanners extends ManageRecords
{
    protected static string $resource = BannerResource::class;

    public function getTitle(): string
    {
        return static::$title ?? static::getResource()::getTitleCasePluralModelLabel();
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\ExportAction::make()
                ->exporter(BannerExporter::class)
                ->label(__('Ekspor Data'))
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success'),
            Actions\CreateAction::make()
                ->label(__('Tambah Banner'))
                ->icon('heroicon-o-plus')
                ->successNotification(
                    Notification::make()
                        ->success()
                        ->title(__('Banner Ditambahkan'))
                        ->body(__('Banner baru telah berhasil ditambahkan.'))
                ),
        ];
    }
}
