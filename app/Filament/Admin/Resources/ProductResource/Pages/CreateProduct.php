<?php

namespace App\Filament\Admin\Resources\ProductResource\Pages;

use App\Filament\Admin\Resources\ProductResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;

    public function getTitle(): string
    {
        return $this->record->name;
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title(__('Bunga Ditambahkan'))
            ->body(__('Bunga baru telah berhasil ditambahkan.'));
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('back')
                ->label(__('Kembali'))
                ->url(fn () => static::getResource()::getUrl('index'))
                ->color('gray')->button()
                ->icon('heroicon-o-arrow-left'),
        ];
    }
}
