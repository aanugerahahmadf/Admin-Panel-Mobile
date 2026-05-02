<?php

namespace App\Filament\User\Resources\WeddingOrganizerResource\Pages;

use App\Filament\User\Concerns\HasMobilePagination;
use App\Filament\User\Resources\WeddingOrganizerResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageWeddingOrganizers extends ManageRecords
{
    use HasMobilePagination;

    protected static string $resource = WeddingOrganizerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
