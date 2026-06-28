<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\CategoryResource\Pages;
use App\Models\Category;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

/**
 * @mixin \Eloquent
 *
 * @property-read Category $record
 */
class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'name';

    public static function getModelLabel(): string
    {
        return __('Kategori');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Kategori');
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'slug'];
    }

    public static function getNavigationGroup(): ?string
    {
        return __('Data Master');
    }

    public static function getNavigationLabel(): string
    {
        return __('Kategori Layanan');
    }

    public static function getNavigationBadge(): ?string
    {
        /** @var Builder $query */
        $query = static::$model::query();

        return (string) $query->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'primary';
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return __('Total Kategori Layanan');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make(__('Detail Kategori'))
                            ->description(__('Klasifikasi layanan pernikahan untuk memudahkan pencarian.'))
                            ->icon('heroicon-o-tag')
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label(__('Nama Kategori'))
                                    ->required()
                                    ->maxLength(255)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn (Forms\Set $set, ?string $state) => $set('slug', str($state)->slug()))
                                    ->prefixIcon('heroicon-o-bookmark'),
                                Forms\Components\TextInput::make('slug')
                                    ->label(__('URL Slug'))
                                    ->required()
                                    ->unique(ignorable: fn (?Category $record) => $record)
                                    ->maxLength(255)
                                    ->prefixIcon('heroicon-o-link'),
                                Forms\Components\RichEditor::make('description')
                                    ->label(__('Deskripsi Kategori'))
                                    ->columnSpanFull()
                                    ->toolbarButtons(['bold', 'italic', 'underline', 'bulletList', 'orderedList']),
                            ])->columns(2),
                    ])->columnSpan(['lg' => 2]),

                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make(__('Visual'))
                            ->icon('heroicon-o-photo')
                            ->schema([
                                Forms\Components\TextInput::make('icon')
                                    ->label(__('Ikon Representasi (Class Name)'))
                                    ->maxLength(255)
                                    ->prefixIcon('heroicon-o-star')
                                    ->helperText(__('Gunakan Heroicons (contoh: heroicon-o-camera).')),
                            ]),
                    ])->columnSpan(['lg' => 1]),
            ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->paginated([5])
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->label(__('Nama Kategori'))
                    ->sortable()
                    ->icon('heroicon-o-bookmark'),
                Tables\Columns\TextColumn::make('slug')
                    ->label(__('URL Slug'))
                    ->badge()
                    ->color('info')
                    ->copyable()
                    ->copyableState(fn ($state) => $state)
                    ->sortable(),
                Tables\Columns\TextColumn::make('icon')
                    ->label(__('Ikon'))
                    ->badge()
                    ->color('warning')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('Dibuat Pada'))
                    ->dateTime()
                    ->alignment('center')
                    ->sortable()
                    ->icon('heroicon-o-calendar'),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label(__('Terakhir Diperbarui'))
                    ->dateTime()
                    ->alignment('center')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->slideOver()
                    ->button()
                    ->color('info')
                    ->size('lg'),
                Tables\Actions\EditAction::make()
                    ->slideOver()
                    ->button()
                    ->color('warning')
                    ->size('lg')
                    ->successNotification(
                        Notification::make()
                            ->success()
                            ->title(__('Kategori diperbarui'))
                            ->body(__('Kategori telah berhasil diperbarui.'))
                    ),
                Tables\Actions\DeleteAction::make()
                    ->button()
                    ->color('danger')
                    ->size('lg')
                    ->successNotification(
                        Notification::make()
                            ->success()
                            ->title(__('Kategori dihapus'))
                            ->body(__('Kategori telah berhasil dihapus.'))
                    ),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageCategories::route('/'),
        ];
    }
}
