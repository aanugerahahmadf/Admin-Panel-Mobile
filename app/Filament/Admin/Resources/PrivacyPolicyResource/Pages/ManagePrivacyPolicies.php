<?php

namespace App\Filament\Admin\Resources\PrivacyPolicyResource\Pages;

use App\Filament\Admin\Resources\PrivacyPolicyResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ManageRecords;

class ManagePrivacyPolicies extends ManageRecords
{
    protected static string $resource = PrivacyPolicyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label(__('Tambah Kebijakan Privasi'))
                ->icon('heroicon-o-plus')
                ->successNotification(
                    Notification::make()
                        ->success()
                        ->title(__('Kebijakan Privasi Ditambahkan'))
                        ->body(__('Kebijakan privasi baru telah berhasil ditambahkan.'))
                ),
        ];
    }
}
