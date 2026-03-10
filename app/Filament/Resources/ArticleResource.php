<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ArticleResource\Pages;
use App\Models\Article;
use App\Models\User;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ArticleResource extends Resource
{
    protected static ?string $model = Article::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'Artikel Blog';

    protected static ?string $navigationGroup = 'Blog & Media';

    protected static ?int $navigationSort = 3;

    protected static ?string $recordTitleAttribute = 'title';

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
        return 'Total Artikel Blog';
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['title', 'slug', 'content'];
    }

    public static function form(\Filament\Forms\Form $form): \Filament\Forms\Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Article Information')
                    ->description('Details about the article and its author.')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Forms\Set $set, ?string $state) => $set('slug', str($state)->slug())),
                        Forms\Components\TextInput::make('slug')
                            ->required()
                            ->unique(ignorable: fn ($record) => $record)
                            ->maxLength(255),
                        Forms\Components\Select::make('author_id')
                            ->label('Author')
                            ->options(User::all()->pluck('name', 'id'))
                            ->searchable()
                            ->required(),
                    ])->columns(2),

                Forms\Components\Section::make('Content')
                    ->description('Write the main content of your article.')
                    ->schema([
                        Forms\Components\RichEditor::make('content')
                            ->required()
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Media & Publishing')
                    ->description('Article featured image, video, and publication status.')
                    ->schema([
                        Forms\Components\SpatieMediaLibraryFileUpload::make('article_image')
                            ->label('Foto Featured Artikel')
                            ->collection('article-images')
                            ->image()
                            ->imageEditor()
                            ->maxSize(102400000), // 100GB
                        Forms\Components\Group::make([
                            Forms\Components\Toggle::make('is_published')
                                ->label('Published')
                                ->required(),
                            Forms\Components\DateTimePicker::make('published_at')
                                ->label('Publication Date'),
                        ])->columns(1),
                    ])->columns(2),

                Forms\Components\Section::make('Video Artikel')
                    ->description('Tambahkan video pendukung untuk artikel ini.')
                    ->schema([
                        Forms\Components\SpatieMediaLibraryFileUpload::make('videos')
                            ->label('Video Artikel')
                            ->collection('videos')
                            ->acceptedFileTypes(['video/mp4', 'video/webm', 'video/ogg', 'video/quicktime'])
                            ->maxSize(102400000) // 100GB
                            ->helperText('Upload 1 video untuk artikel ini. Format: MP4, WebM, MOV. Maks 100GB.')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->mobileCards()
            ->mobileCardFeatured('title', 'sky')
            ->columns([
                Tables\Columns\TextColumn::make('author.name')
                    ->label('Author')
                    ->numeric()
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('title')
                    ->label('Article Title')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('slug')
                    ->label('Slug')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\ImageColumn::make('image_url')
                    ->label('Feature Image')
                    ->alignment('center'),
                Tables\Columns\IconColumn::make('is_published')
                    ->label('Published')
                    ->alignment('center')
                    ->boolean(),
                Tables\Columns\TextColumn::make('published_at')
                    ->label('Published Date')
                    ->dateTime()
                    ->sortable()
                    ->alignment('center'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created On')
                    ->dateTime()
                    ->sortable()
                    ->alignment('center')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Updated At')
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
                            ->title('Article updated')
                            ->body('The article has been updated successfully.')
                    ),
                Tables\Actions\DeleteAction::make()
                    ->button()
                    ->color('danger')
                    ->size('lg')
                    ->successNotification(
                        Notification::make()
                            ->success()
                            ->title('Article deleted')
                            ->body('The article has been deleted successfully.')
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
