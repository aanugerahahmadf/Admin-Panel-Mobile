<?php

namespace App\Filament\User\Widgets;

use App\Models\Package;
use App\Providers\NativeServiceProvider;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Contracts\Support\Htmlable;

class CombinedCatalogWidget extends BaseWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    protected function getTableHeading(): string|Htmlable|null
    {
        return '';
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(Package::query())
            ->poll(NativeServiceProvider::isNativeMobile() ? null : '30s')
            ->content(view('filament.user.components.combined-catalog-grid'))
            ->paginated(false);
    }
}
