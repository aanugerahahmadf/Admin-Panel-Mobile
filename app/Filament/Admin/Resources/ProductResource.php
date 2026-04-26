<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Exports\ProductExporter;
use App\Filament\Admin\Resources\ProductResource\Pages;
use App\Models\Product;
use App\Models\WeddingOrganizer;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'ri-flower-line';

    protected static ?int $navigationSort = 4;

    public static function getModelLabel(): string
    {
        return __('Bunga');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Bunga');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('Data Master');
    }

    public static function getNavigationLabel(): string
    {
        return __('Daftar Bunga');
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
        return __('Total Product');
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'slug', 'description'];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make(__('Informasi Product'))
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label(__('Nama Product'))
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Forms\Set $set, ?string $state) => $set('slug', str($state)->slug())),
                        Forms\Components\TextInput::make('slug')
                            ->label(__('Slug'))
                            ->required()
                            ->unique(ignorable: fn (?Product $record) => $record)
                            ->maxLength(255),
                        Forms\Components\Select::make('category_id')
                            ->label(__('Kategori'))
                            ->relationship('category', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\TextInput::make('price')
                            ->label(__('Harga'))
                            ->numeric()
                            ->prefix('Rp')
                            ->required(),
                        Forms\Components\TextInput::make('discount_price')
                            ->label(__('Harga Diskon'))
                            ->numeric()
                            ->prefix('Rp'),
                        Forms\Components\TextInput::make('stock')
                            ->label(__('Stok'))
                            ->numeric()
                            ->default(0)
                            ->required(),
                        Forms\Components\Toggle::make('is_active')
                            ->label(__('Aktif'))
                            ->default(true),
                        Forms\Components\RichEditor::make('description')
                            ->label(__('Deskripsi'))
                            ->columnSpanFull(),
                        Forms\Components\SpatieMediaLibraryFileUpload::make('product_image')
                            ->label(__('Foto Product'))
                            ->collection('product_image')
                            ->image()
                            ->columnSpanFull(),
                        Forms\Components\Hidden::make('wedding_organizer_id')
                            ->default(function () {
                                return WeddingOrganizer::getBrand()?->id ?? WeddingOrganizer::query()->value('id');
                            })
                            ->required(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query): void {
                $brandId = WeddingOrganizer::getBrand()?->id;
                if ($brandId) {
                    $query->where('wedding_organizer_id', $brandId);
                }
            })
            ->columns([
                Tables\Columns\SpatieMediaLibraryImageColumn::make('product_image')
                    ->label(__('Foto'))
                    ->collection('product_image'),
                Tables\Columns\TextColumn::make('name')
                    ->label(__('Nama Product'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('category.name')
                    ->label(__('Kategori'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('price')
                    ->label(__('Harga'))
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('discount_price')
                    ->label(__('Harga Diskon'))
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('stock')
                    ->label(__('Stok'))
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label(__('Aktif'))
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->relationship('category', 'name'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->button()
                    ->color('info')
                    ->size('lg'),
                Tables\Actions\EditAction::make()
                    ->url(fn (Product $record): string => static::getUrl('edit', ['record' => $record]))
                    ->button()
                    ->color('warning')
                    ->size('lg')
                    ->successNotification(
                        Notification::make()
                            ->success()
                            ->title(__('Product diperbarui'))
                            ->body(__('Product telah berhasil diperbarui.'))
                    ),
                Tables\Actions\DeleteAction::make()
                    ->button()
                    ->color('danger')
                    ->size('lg')
                    ->successNotification(
                        Notification::make()
                            ->success()
                            ->title(__('Product dihapus'))
                            ->body(__('Product telah berhasil dihapus.'))
                    ),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\ExportBulkAction::make()
                        ->exporter(ProductExporter::class)
                        ->label(__('Ekspor Data Terpilih')),
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
