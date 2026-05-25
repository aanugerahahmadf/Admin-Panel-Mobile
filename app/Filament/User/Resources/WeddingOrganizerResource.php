<?php

namespace App\Filament\User\Resources;

use App\Filament\User\Pages\MessagesPage;
use App\Filament\User\Resources\WeddingOrganizerResource\Pages;
use App\Models\Message;
use App\Models\WeddingOrganizer;
use Filament\Facades\Filament;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class WeddingOrganizerResource extends Resource
{
    protected static ?string $model = WeddingOrganizer::class;


    protected static ?string $navigationIcon = 'heroicon-o-home';

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public static function getNavigationBadge(): ?string
    {
        $count = Message::query()
            ->whereJsonDoesntContain('read_by', Filament::auth()->id(), 'and')
            ->where('user_id', '!=', Filament::auth()->id())
            ->count('id');

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return static::getNavigationLabel();
    }

    public static function getNavigationGroup(): ?string
    {
        return __('Beranda');
    }

    public static function getNavigationLabel(): string
    {
        return __('Beranda');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Beranda');
    }

    public static function getModelLabel(): string
    {
        return __('Beranda');
    }

    public static function getGloballySearchableAttributes(): array
    {
        return [''];
    }

    public static function getGlobalSearchResultTitle(Model $record): string
    {
        return __($record->name).' ('.__('Profil Studio').')';
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            __('Lokasi') => __($record->city).', '.__($record->address),
            __('Kontak') => $record->phone.' / '.$record->email,
            __('Tentang') => Str::limit(__($record->description), 100),
        ];
    }

    public static function getGlobalSearchResultUrl(Model $record): ?string
    {
        return static::getUrl('index', ['record' => $record]);
    }

    public static function getEloquentQuery(): Builder
    {
        // Dynamically find the first WO to avoid 404 after fresh migration
        $firstId = WeddingOrganizer::value('id') ?? 1;
        return parent::getEloquentQuery()->where('id', $firstId);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordUrl(fn ($record) => static::getUrl('view', ['record' => $record]))
            ->poll(\App\Providers\NativeServiceProvider::isNativeMobile() ? null : '30s')
            ->content(view('filament.user.components.combined-catalog-grid'));
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageWeddingOrganizers::route('/'),
            'view' => Pages\ViewWeddingOrganizer::route('/{record}'),
        ];
    }
}
