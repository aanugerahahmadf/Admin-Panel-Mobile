<?php

namespace App\Filament\Admin\Resources\WeddingOrganizerResource\Pages;

use App\Filament\Admin\Resources\WeddingOrganizerResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditWeddingOrganizer extends EditRecord
{
    protected static string $resource = WeddingOrganizerResource::class;

    public function getTitle(): string
    {
        return $this->record->name;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('back')
                ->label(__('Kembali'))
                ->icon('heroicon-o-arrow-left')
                ->color('gray')
                ->url(WeddingOrganizerResource::getUrl('index')),
            Actions\DeleteAction::make(),
        ];
    }
}
