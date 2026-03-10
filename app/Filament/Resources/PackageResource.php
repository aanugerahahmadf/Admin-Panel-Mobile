<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PackageResource\Pages;
use App\Models\Package;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;

class PackageResource extends Resource
{
    protected static ?string $model = Package::class;

    protected static ?string $navigationIcon = 'heroicon-o-gift';

    protected static ?string $navigationLabel = 'Paket Rias';

    protected static ?string $navigationGroup = 'Studio';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'name';

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'theme', 'color'];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::$model::count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'primary';
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'Total Paket Rias';
    }

    public static function form(\Filament\Forms\Form $form): \Filament\Forms\Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Klasifikasi Layanan Rias')
                    ->description('Atur layanan rias Anda berdasarkan kategori.')
                    ->schema([
                        Forms\Components\Select::make('wedding_organizer_id')
                            ->label('Studio')
                            ->relationship('weddingOrganizer', 'name')
                            ->default(fn () => \App\Models\WeddingOrganizer::first()?->id)
                            ->searchable()
                            ->preload()
                            ->required()
                            ->hidden(fn () => \App\Models\WeddingOrganizer::count() <= 1),
                        Forms\Components\Select::make('category_id')
                            ->label('Kategori Rias')
                            ->relationship('category', 'name')
                            ->searchable()
                            ->preload(),
                    ])->columns(2),

                Forms\Components\Section::make('Identitas Paket')
                    ->description('Penamaan utama dan detail deskriptif.')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nama Paket')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Forms\Set $set, ?string $state) => $set('slug', str($state)->slug())),
                        Forms\Components\TextInput::make('slug')
                            ->required()
                            ->unique(ignorable: fn ($record) => $record)
                            ->maxLength(255),
                        Forms\Components\Textarea::make('description')
                            ->label('Deskripsi')
                            ->columnSpanFull(),
                    ])->columns(2),

                Forms\Components\Section::make('Harga & Fitur')
                    ->description('Aspek finansial dan fungsional dari layanan.')
                    ->schema([
                        Forms\Components\TextInput::make('price')
                            ->label('Harga Dasar')
                            ->required()
                            ->numeric()
                            ->prefix('Rp'),
                        Forms\Components\TextInput::make('discount_price')
                            ->label('Harga Diskon')
                            ->numeric()
                            ->prefix('Rp')
                            ->validationAttribute('price')
                            ->rules(['nullable', 'numeric']),
                        Forms\Components\Toggle::make('is_featured')
                            ->label('Paket Unggulan')
                            ->inline(false),
                        Forms\Components\TagsInput::make('features')
                            ->label('Fitur')
                            ->placeholder('Tambahkan fitur dan tekan enter')
                            ->columnSpanFull(),
                    ])->columns(2),

                Forms\Components\Section::make('Tema & Kapasitas')
                    ->description('Estetika visual dan akomodasi tamu.')
                    ->schema([
                        Forms\Components\TextInput::make('theme')
                            ->label('Tema')
                            ->maxLength(255),
                        Forms\Components\ColorPicker::make('color')
                            ->label('Warna'),
                        Forms\Components\TextInput::make('min_capacity')
                            ->label('Kapasitas Minimum')
                            ->numeric()
                            ->suffix('Pax'),
                        Forms\Components\TextInput::make('max_capacity')
                            ->label('Kapasitas Maksimum')
                            ->numeric()
                            ->suffix('Pax'),
                    ])->columns(2),

                Forms\Components\Section::make('Media Paket')
                    ->description('Upload foto utama dan video portfolio paket rias ini.')
                    ->schema([
                        Forms\Components\SpatieMediaLibraryFileUpload::make('package_image')
                            ->label('Foto Utama Paket')
                            ->collection('package')
                            ->image()
                            ->imageEditor()
                            ->maxSize(102400000) // 100GB
                            ->columnSpanFull(),
                        Forms\Components\SpatieMediaLibraryFileUpload::make('videos')
                            ->label('Video Portfolio')
                            ->collection('videos')
                            ->multiple()
                            ->acceptedFileTypes(['video/mp4', 'video/webm', 'video/ogg', 'video/quicktime'])
                            ->maxSize(102400000) // 100GB
                            ->maxFiles(5)
                            ->helperText('Upload video portfolio paket. Format: MP4, WebM, MOV. Maks 100GB per file.')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(\Filament\Tables\Table $table): \Filament\Tables\Table
    {
        return $table
            ->mobileCards()
            ->mobileCardFeatured('price', 'rose')
            ->columns([
                Tables\Columns\TextColumn::make('weddingOrganizer.name')
                    ->label('Organizer')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('category.name')
                    ->label('Kategori')
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Paket')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('slug')
                    ->label('Slug')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('description')
                    ->label('Deskripsi')
                    ->limit(50)
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('price')
                    ->label('Harga Dasar')
                    ->money('IDR')
                    ->sortable()
                    ->alignment('right'),
                Tables\Columns\TextColumn::make('theme')
                    ->label('Tema')
                    ->searchable()
                    ->alignment('center'),
                Tables\Columns\TextColumn::make('color')
                    ->label('Warna')
                    ->searchable()
                    ->alignment('center')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('min_capacity')
                    ->label('Min Pax')
                    ->numeric()
                    ->sortable()
                    ->alignment('center'),
                Tables\Columns\TextColumn::make('max_capacity')
                    ->label('Max Pax')
                    ->numeric()
                    ->sortable()
                    ->alignment('center'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat Pada')
                    ->dateTime()
                    ->sortable()
                    ->alignment('center')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Diperbarui Pada')
                    ->dateTime()
                    ->sortable()
                    ->alignment('center')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->button()
                    ->color('info')
                    ->size('lg'),
                Tables\Actions\EditAction::make()
                    ->button()
                    ->color('warning')
                    ->size('lg')
                    ->successNotification(
                        Notification::make()
                            ->success()
                            ->title('Paket diperbarui')
                            ->body('Paket telah berhasil diperbarui.')
                    ),
                Tables\Actions\DeleteAction::make()
                    ->button()
                    ->color('danger')
                    ->size('lg')
                    ->successNotification(
                        Notification::make()
                            ->success()
                            ->title('Paket dihapus')
                            ->body('Paket telah berhasil dihapus.')
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
            'index' => Pages\ManagePackages::route('/'),
        ];
    }
}
