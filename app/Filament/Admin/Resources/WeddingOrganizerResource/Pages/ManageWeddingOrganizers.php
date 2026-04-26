<?php

namespace App\Filament\Admin\Resources\WeddingOrganizerResource\Pages;

use App\Filament\Admin\Exports\WeddingOrganizerExporter;
use App\Filament\Admin\Resources\WeddingOrganizerResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ManageRecords;

/**
 * @property-read \App\Filament\Resources\WeddingOrganizerResource $resource
 */
class ManageWeddingOrganizers extends ManageRecords
{
    protected static string $resource = WeddingOrganizerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ExportAction::make()
                ->exporter(WeddingOrganizerExporter::class)
                ->label(__('Ekspor Data'))
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success'),
            Actions\CreateAction::make()
                ->label(__('Tambah Studio'))
                ->icon('heroicon-o-plus')
                ->successNotification(
                    Notification::make()
                        ->success()
                        ->title(__('Studio Ditambahkan'))
                        ->body(__('Data vendor dekorasi baru telah berhasil ditambahkan.'))
                ),
        ];
    }

    // Geocoding logic moved to WeddingOrganizer model saving event for 100% sync reliability.
}
