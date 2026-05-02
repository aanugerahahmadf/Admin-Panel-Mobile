<?php

namespace App\Filament\User\Resources\ArticleResource\Pages;

use App\Filament\User\Concerns\HasMobilePagination;
use App\Filament\User\Resources\ArticleResource;
use Filament\Resources\Pages\ManageRecords;

class ManageArticles extends ManageRecords
{
    use HasMobilePagination;

    protected static string $resource = ArticleResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
