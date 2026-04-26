<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\BannerResource\Pages;
use App\Models\Banner;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

/**
 * @mixin \Eloquent
 * @property-read \App\Models\Banner $record
 */
class BannerResource extends Resource
{
    protected static ?string $model = Banner::class;

    protected static ?string $navigationIcon = 'heroicon-o-photo';

    protected static ?int $navigationSort = 4;

    protected static ?string $recordTitleAttribute = 'title';

    public static function getModelLabel(): string
    {
        return __('Banner');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Banner');
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['title'];
    }

    public static function getNavigationGroup(): ?string
    {
        return __('Data Master');
    }

    public static function getNavigationLabel(): string
    {
        return __('Banner Promo');
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
        return __('Total Banner Promo');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make(__('Konten Banner'))
                            ->description(__('Detail promosi atau visual banner utama di aplikasi.'))
                            ->icon('heroicon-o-tv')
                            ->schema([
                                Forms\Components\TextInput::make('title')
                                    ->label(__('Judul Promosi/Banner'))

                                    ->maxLength(255)
                                    ->prefixIcon('heroicon-o-megaphone'),
                                Forms\Components\FileUpload::make('image_url')
                                    ->label(__('Gambar Visual Banner'))
                                    ->image()
                                    ->imageEditor()
                                    ->directory('banners')
                                    ->required()
                                    ->columnSpanFull(),
                            ])->columns(2),
                    ])->columnSpan(['lg' => 2]),

                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make(__('Konfigurasi Penayangan'))
                            ->description(__('Atur status tayang dan prioritas.'))
                            ->icon('heroicon-o-cog')
                            ->schema([
                                Forms\Components\Toggle::make('is_active')
                                    ->label(__('Status Aktif Tayang'))
                                    ->required()
                                    ->default(true)
                                    ->onColor('success')
                                    ->onIcon('heroicon-s-check')
                                    ->offIcon('heroicon-o-x-mark'),
                                Forms\Components\TextInput::make('sort_order')
                                    ->label(__('Prioritas Tampilan (Urutan)'))
                                    ->required()
                                    ->numeric()
                                    ->default(0)
                                    ->prefixIcon('heroicon-o-bars-3-bottom-left')
                                    ->helperText(__('Makin kecil angka, makin atas munculnya.')),
                            ]),
                    ])->columnSpan(['lg' => 1]),
            ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            
            ->columns([
                Tables\Columns\TextColumn::make('title')->searchable()
                    ->label(__('Judul Banner'))
                    ->alignment('center'),
                Tables\Columns\ImageColumn::make('image_url')
                    ->label(__('Pratinjau Gambar'))
                    ->alignment('center'),
                Tables\Columns\IconColumn::make('is_active')
                    ->label(__('Status'))
                    ->alignment('center')
                    ->boolean(),
                Tables\Columns\TextColumn::make('sort_order')
                    ->label(__('Prioritas'))
                    ->numeric()
                    ->alignment('center'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('Dibuat Pada'))
                    ->dateTime()
                    ->alignment('center')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label(__('Terakhir Diperbarui'))
                    ->dateTime()
                    ->alignment('center')
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
                            ->title(__('Banner diperbarui'))
                            ->body(__('Banner telah berhasil diperbarui.'))
                    ),
                Tables\Actions\DeleteAction::make()
                    ->button()
                    ->color('danger')
                    ->size('lg')
                    ->successNotification(
                        Notification::make()
                            ->success()
                            ->title(__('Banner dihapus'))
                            ->body(__('Banner telah berhasil dihapus.'))
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
            'index' => Pages\ManageBanners::route('/'),
        ];
    }
}
