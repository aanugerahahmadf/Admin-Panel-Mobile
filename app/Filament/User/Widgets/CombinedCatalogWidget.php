<?php

namespace App\Filament\User\Widgets;

use App\Models\WeddingOrganizer;
use App\Providers\NativeServiceProvider;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class CombinedCatalogWidget extends BaseWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    protected function getTableHeading(): string|\Illuminate\Contracts\Support\Htmlable|null
    {
        return '';
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(WeddingOrganizer::query())
            ->poll(NativeServiceProvider::isNativeMobile() ? null : '30s')
            ->content(view('filament.user.components.combined-catalog-grid'))
            ->paginated(false);
    }
}
