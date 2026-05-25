<?php

namespace App\Filament\User\Resources\ArticleResource\Pages;

use App\Filament\User\Resources\ArticleResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewArticle extends ViewRecord
{
    protected static string $resource = ArticleResource::class;

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
                ->url(fn () => ArticleResource::getUrl('index')),
        ];
    }
}
