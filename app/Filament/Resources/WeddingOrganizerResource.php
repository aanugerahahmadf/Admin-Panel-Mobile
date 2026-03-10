<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WeddingOrganizerResource\Pages;
use App\Models\WeddingOrganizer;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;

class WeddingOrganizerResource extends Resource
{
    protected static ?string $model = WeddingOrganizer::class;

    protected static ?string $navigationIcon = 'heroicon-o-sparkles';

    protected static ?string $navigationLabel = 'Profil Studio';

    protected static ?string $navigationGroup = 'Studio';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'name';

    public static function getGloballySearchableAttributes(): array
    {
        return ['name'];
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
        return 'Profil WO Aktif';
    }

    public static function form(\Filament\Forms\Form $form): \Filament\Forms\Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Studio')
                    ->description('Detail studio rias pengantin dan merek.')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nama Studio')
                            ->required()
                            ->maxLength(255)
                            ->default('Devi Make Up & Wedding')
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Forms\Set $set, ?string $state) => $set('slug', str($state)->slug())),
                        Forms\Components\TextInput::make('slug')
                            ->required()
                            ->unique(ignorable: fn ($record) => $record)
                            ->maxLength(255)
                            ->default('devi-makeup-wedding'),
                        Forms\Components\SpatieMediaLibraryFileUpload::make('gallery')
                            ->label('Galeri')
                            ->collection('gallery')
                            ->multiple()
                            ->reorderable()
                            ->image()
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('description')
                            ->label('Deskripsi Studio')
                            ->default('Professional bridal makeup artist specializing in traditional and modern wedding looks.')
                            ->columnSpanFull(),
                    ])->columns(2),

                Forms\Components\Section::make('Kontak & Lokasi')
                    ->description('Informasi kontak studio dan detail lokasi.')
                    ->schema([
                        Forms\Components\TextInput::make('address')
                            ->label('Alamat Studio')
                            ->maxLength(255)
                            ->default('Jakarta Selatan, DKI Jakarta'),
                        Forms\Components\Group::make([
                            Forms\Components\TextInput::make('latitude')
                                ->maxLength(255),
                            Forms\Components\TextInput::make('longitude')
                                ->maxLength(255),
                        ])->columns(2),
                        Forms\Components\TextInput::make('rating')
                            ->label('Rating')
                            ->required()
                            ->numeric()
                            ->default(0.00)
                            ->minValue(0)
                            ->maxValue(5),
                        Forms\Components\Toggle::make('is_verified')
                            ->label('Studio Terverifikasi')
                            ->required()
                            ->default(true)
                            ->inline(false),
                    ])->columns(2),

                Forms\Components\Section::make('Video Profil Studio')
                    ->description('Upload video perkenalan studio untuk ditampilkan di halaman profil.')
                    ->schema([
                        Forms\Components\SpatieMediaLibraryFileUpload::make('videos')
                            ->label('Video Profil Studio')
                            ->collection('videos')
                            ->multiple()
                            ->acceptedFileTypes(['video/mp4', 'video/webm', 'video/ogg', 'video/quicktime'])
                            ->maxSize(102400000) // 100GB
                            ->maxFiles(3)
                            ->helperText('Upload video profil/showreel studio. Format: MP4, WebM, MOV. Maks 100GB per file.')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(\Filament\Tables\Table $table): \Filament\Tables\Table
    {
        return $table
            ->mobileCards()
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Studio')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('slug')
                    ->label('Slug')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('address')
                    ->label('Alamat')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('latitude')
                    ->label('Latitude')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('longitude')
                    ->label('Longitude')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('rating')
                    ->label('Rating')
                    ->numeric()
                    ->sortable()
                    ->alignment('center'),
                Tables\Columns\IconColumn::make('is_verified')
                    ->label('Terverifikasi')
                    ->boolean()
                    ->searchable()
                    ->alignment('center'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Terdaftar Pada')
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
                            ->title('Studio Rias Pengantin diperbarui')
                            ->body('Studio rias pengantin telah berhasil diperbarui.')
                    ),
                Tables\Actions\DeleteAction::make()
                    ->button()
                    ->color('danger')
                    ->size('lg')
                    ->successNotification(
                        Notification::make()
                            ->success()
                            ->title('Studio Rias Pengantin dihapus')
                            ->body('Studio rias pengantin telah berhasil dihapus.')
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
            'index' => Pages\ManageWeddingOrganizers::route('/'),
        ];
    }
}
