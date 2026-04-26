<?php

namespace App\Filament\User\Resources\PackageResource\Pages;

use App\Filament\User\Resources\PackageResource;
use Filament\Resources\Pages\ViewRecord;

class ViewPackage extends ViewRecord
{
    protected static string $resource = PackageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // No edit/delete for users ideally, maybe a "Book Now" or "Chat"
        ];
    }
}
