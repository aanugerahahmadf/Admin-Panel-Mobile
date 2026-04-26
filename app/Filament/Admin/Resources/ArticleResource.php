<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\ArticleResource\Pages;
use App\Models\Article;
use App\Models\User;
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
 * @property-read Article $record
 */
class ArticleResource extends Resource
{
    protected static ?string $model = Article::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?int $navigationSort = 5;

    protected static ?string $recordTitleAttribute = 'title';

    public static function getModelLabel(): string
    {
        return __('Artikel');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Artikel');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('Data Master');
    }

    public static function getNavigationLabel(): string
    {
        return __('Artikel Blog');
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
        return __('Total Artikel Blog');
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['title', 'slug', 'content'];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make(__('Informasi Artikel'))
                            ->description(__('Detail tentang artikel dan penulisnya.'))
                            ->icon('heroicon-o-document-text')
                            ->schema([
                                Forms\Components\TextInput::make('title')
                                    ->label(__('Judul Artikel'))
                                    ->required()
                                    ->maxLength(255)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn (Forms\Set $set, ?string $state) => $set('slug', str($state)->slug()))
                                    ->prefixIcon('heroicon-o-pencil'),
                                Forms\Components\TextInput::make('slug')
                                    ->label(__('Slug URL'))
                                    ->required()
                                    ->unique(ignorable: fn (?Article $record) => $record)
                                    ->maxLength(255)
                                    ->prefixIcon('heroicon-o-link'),
                                Forms\Components\Select::make('author_id')
                                    ->searchable()
                                    ->label(__('Penulis'))
                                    ->options(User::query()->pluck('full_name', 'id')->toArray())
                                    ->default(fn () => auth()->id())
                                    ->required()
                                    ->prefixIcon('heroicon-o-user'),
                                Forms\Components\Select::make('category_id')
                                    ->searchable()
                                    ->label(__('Kategori Artikel'))
                                    ->relationship('category', 'name')
                                    ->preload()
                                    ->prefixIcon('heroicon-o-tag'),
                                Forms\Components\Hidden::make('wedding_organizer_id')
                                    ->default(1)
                                    ->required(),
                                Forms\Components\Textarea::make('excerpt')
                                    ->label(__('Ringkasan (Snippet)'))
                                    ->rows(3)
                                    ->columnSpanFull()

                                    ->helperText(__('Ringkasan ini akan tampil pada daftar artikel.')),
                            ])->columns(2),

                        Forms\Components\Section::make(__('Konten Artikel'))
                            ->description(__('Tulis konten utama artikel Anda.'))
                            ->icon('heroicon-o-pencil-square')
                            ->schema([
                                Forms\Components\RichEditor::make('content')
                                    ->label(__('Isi Konten'))
                                    ->required()
                                    ->columnSpanFull(),
                            ]),
                    ])->columnSpan(['lg' => 2]),

                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make(__('Media & Visual'))
                            ->description(__('Kumpulan gambar dan video artikel.'))
                            ->icon('heroicon-o-photo')
                            ->schema([
                                Forms\Components\SpatieMediaLibraryFileUpload::make('article_thumbnail')
                                    ->label(__('Gambar Utama (Thumbnail)'))
                                    ->collection('images')
                                    ->image(),
                                Forms\Components\SpatieMediaLibraryFileUpload::make('article_video')
                                    ->label(__('Video Pendukung'))
                                    ->collection('videos')
                                    ->acceptedFileTypes(['video/mp4', 'video/webm', 'video/ogg', 'video/quicktime'])
                                    ->maxSize(102400000) // 100MB
                                    ->helperText(__('Maks 100MB. Format: MP4, WebM, MOV. / opsional')),
                            ]),

                        Forms\Components\Section::make(__('Penerbitan'))
                            ->description(__('Status publikasi artikel.'))
                            ->icon('heroicon-o-globe-alt')
                            ->schema([
                                Forms\Components\Toggle::make('is_published')
                                    ->label(__('Status Diterbitkan'))
                                    ->required()
                                    ->onColor('success')
                                    ->onIcon('heroicon-s-check-circle')
                                    ->offIcon('heroicon-o-x-circle'),
                                Forms\Components\DateTimePicker::make('published_at')
                                    ->label(__('Jadwal / Waktu Publikasi'))
                                    ->native(false)
                                    ->prefixIcon('heroicon-o-clock'),
                            ]),
                    ])->columnSpan(['lg' => 1]),
            ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('author.full_name')
                    ->searchable()
                    ->label(__('Penulis')),
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->label(__('Judul Artikel'))
                    ->description(fn (Article $record): string => $record->excerpt ?? ''),
                Tables\Columns\TextColumn::make('category.name')
                    ->searchable()
                    ->label(__('Kategori'))
                    ->badge()
                    ->color('info'),
                Tables\Columns\TextColumn::make('slug')
                    ->label(__('Slug'))
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\ImageColumn::make('image_url')
                    ->label(__('Gambar Utama'))
                    ->circular()
                    ->alignment('center'),
                Tables\Columns\IconColumn::make('is_published')
                    ->label(__('Diterbitkan'))
                    ->alignment('center')
                    ->boolean(),
                Tables\Columns\IconColumn::make('video_url')
                    ->label(__('Video'))
                    ->icon('heroicon-m-video-camera')
                    ->color('warning')
                    ->alignment('center'),
                Tables\Columns\TextColumn::make('published_at')
                    ->label(__('Tanggal Terbit'))
                    ->dateTime()
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
                            ->title(__('Artikel diperbarui'))
                            ->body(__('Artikel telah berhasil diperbarui.'))
                    ),
                Tables\Actions\DeleteAction::make()
                    ->button()
                    ->color('danger')
                    ->size('lg')
                    ->successNotification(
                        Notification::make()
                            ->success()
                            ->title(__('Artikel dihapus'))
                            ->body(__('Artikel telah berhasil dihapus.'))
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
            'index' => Pages\ManageArticles::route('/'),
        ];
    }
}
