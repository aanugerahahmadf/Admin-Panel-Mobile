<?php

namespace App\Filament\User\Resources\VoucherResource\Pages;

use App\Filament\User\Concerns\HasMobilePagination;
use App\Filament\User\Resources\VoucherResource;
use Filament\Resources\Pages\ManageRecords;

class ManageVouchers extends ManageRecords
{
    use HasMobilePagination;

    protected static string $resource = VoucherResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // No creation action for user
        ];
    }

    public function getTitle(): string
    {
        return static::$title ?? static::getResource()::getTitleCasePluralModelLabel();
    }
}
