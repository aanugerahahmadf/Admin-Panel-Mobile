<?php

namespace App\Filament\Admin\Resources\BankResource\Pages;

use App\Filament\Admin\Exports\BankExporter;
use App\Filament\Admin\Resources\BankResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ManageRecords;

class ManageBanks extends ManageRecords
{
    protected static string $resource = BankResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ExportAction::make()
                ->exporter(BankExporter::class)
                ->label(__('Ekspor Data'))
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success'),
            Actions\CreateAction::make()
                ->label(__('Tambah Bank'))
                ->icon('heroicon-o-plus')
                ->successNotification(
                    Notification::make()
                        ->success()
                        ->title(__('Bank Ditambahkan'))
                        ->body(__('Bank baru telah berhasil ditambahkan.'))
                ),
        ];
    }
}
