<?php

namespace App\Filament\User\Resources\ReviewResource\Pages;

use App\Filament\User\Concerns\HasMobilePagination;
use App\Filament\User\Resources\ReviewResource;
use Filament\Resources\Pages\ManageRecords;

class ManageReviews extends ManageRecords
{
    use HasMobilePagination;

    protected static string $resource = ReviewResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    public function getTitle(): string
    {
        return static::$title ?? static::getResource()::getTitleCasePluralModelLabel();
    }
}
