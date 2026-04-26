<?php

namespace App\Filament\User\Resources;

use App\Filament\User\Resources\ArticleResource\Pages;
use App\Models\Article;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\TextEntry\TextEntrySize;
use Filament\Infolists\Components\ViewEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;

class ArticleResource extends Resource
{
    protected static ?string $model = Article::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    public static function getNavigationGroup(): ?string
    {
        return __('Belanja & Jelajahi');
    }

    public static function getNavigationLabel(): string
    {
        return __('Tips & Inspiration');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Tips & Inspiration');
    }

    public static function getModelLabel(): string
    {
        return __('Tips & Inspiration');
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getModel()::count();
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return static::getNavigationLabel();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->label(__('Judul'))
                    ->readonly(),
                Forms\Components\RichEditor::make('content')
                    ->label(__('Konten'))
                    ->disabled()
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->description(new HtmlString('<style>.fi-ta-ctn, .fi-ta-content, .fi-ta-header-toolbar, .fi-ta-pagination { background-color: transparent !important; box-shadow: none !important; border-color: transparent !important; }</style>'))
            ->emptyStateHeading(__('Belum ada artikel'))
            ->contentGrid([
                'sm' => 1,
                'md' => 2,
                'lg' => 3,
                'xl' => 4,
            ])
            ->columns([
                Tables\Columns\Layout\Stack::make([
                    // Cover Image Section with Video Overlay
                    Tables\Columns\Layout\Stack::make([
                        Tables\Columns\ImageColumn::make('image_url')
                            ->label('')
                            ->height('12rem')
                            ->width('100%')
                            ->alignment('center')
                            ->getStateUsing(fn ($record) => $record->image_url)
                            ->extraAttributes([
                                'class' => 'w-full h-full overflow-hidden bg-white/5 flex products-center justify-center rounded-t-xl shadow-inner',
                            ])
                            ->extraImgAttributes([
                                'class' => 'w-full h-full object-cover object-center transition-transform duration-500 group-hover:scale-110 blur-0',
                            ]),

                        // Video Indicator (Premium Play Icon)
                        Tables\Columns\TextColumn::make('video_indicator')
                            ->label('')
                            ->getStateUsing(fn () => '')
                            ->icon('heroicon-s-play-circle')
                            ->iconColor('warning')
                            ->extraAttributes([
                                'class' => 'absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 drop-shadow-2xl scale-[2.5] opacity-70 group-hover:opacity-100 group-hover:scale-[3] transition-all duration-300',
                            ])
                            ->visible(fn ($record) => $record && ((bool) $record->video_url || $record->hasMedia('videos'))),
                    ])
                        ->extraAttributes(['class' => 'relative overflow-hidden rounded-t-xl'])
                        ->visible(fn ($record) => $record && $record->image_url),

                    // Text content block
                    Tables\Columns\Layout\Stack::make([
                        // Category Badge - Now clearly at the top of content section
                        Tables\Columns\TextColumn::make('category.name')
                            ->formatStateUsing(fn ($state) => __($state))
                            ->badge()
                            ->color('info')
                            ->size('xs')
                            ->extraAttributes(['class' => 'mb-2']),

                        Tables\Columns\TextColumn::make('title')
                            ->formatStateUsing(fn ($state) => __($state))
                            ->searchable()
                            ->weight(FontWeight::Bold)
                            ->size('sm')
                            ->lineClamp(2),

                        Tables\Columns\TextColumn::make('excerpt')
                            ->formatStateUsing(fn ($state) => __($state))
                            ->size('xs')
                            ->color('gray')
                            ->lineClamp(2)
                            ->wrap(),

                        Tables\Columns\Layout\Split::make([
                            Tables\Columns\TextColumn::make('author.full_name')
                                ->size('xs')
                                ->color('gray')
                                ->icon('heroicon-o-user')
                                ->grow(false),

                            Tables\Columns\TextColumn::make('published_at')
                                ->date('d M Y')
                                ->size('xs')
                                ->color('gray')
                                ->icon('heroicon-o-calendar')
                                ->alignEnd(),
                        ])->extraAttributes(['class' => 'mt-4 pt-4 border-t border-gray-100 dark:border-gray-800']),
                    ])->extraAttributes(['class' => 'p-4']),
                ])->extraAttributes([
                    'class' => 'bg-white dark:bg-gray-900 rounded-xl shadow-sm hover:shadow-xl border border-gray-100 dark:border-gray-800 transition-all duration-300 group overflow-hidden cursor-pointer',
                ]),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category_id')
                    ->label(__('Kategori'))
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label(__('Baca Artikel'))
                    ->button()
                    ->color('warning')
                    ->size('sm')
                    ->icon('heroicon-m-book-open')
                    ->extraAttributes(['class' => 'flex-1 justify-center rounded-lg shadow-sm font-bold'])
                    ->slideOver()
                    ->modalWidth('2xl')
                    ->modalHeading(__('Membaca Artikel')),
            ])
            ->actionsAlignment('center')
            ->extraAttributes([
                'class' => 'filament-table-actions-container !flex !flex-row !gap-1 !p-3 !bg-gray-50/50 dark:!bg-white/5 !border-t dark:!border-gray-800',
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['author', 'category', 'packages'])
            ->where('is_published', true)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make()
                    ->schema([
                        // Main Visual
                        ImageEntry::make('image_url')
                            ->hiddenLabel()
                            ->height('20rem')
                            ->width('100%')
                            ->extraImgAttributes(['class' => 'object-contain rounded-2xl mb-4 shadow-lg mx-auto bg-gray-50/30 dark:bg-gray-800/30']),

                        Grid::make(2)
                            ->schema([
                                TextEntry::make('category.name')
                                    ->formatStateUsing(fn ($state) => __($state))
                                    ->label(__('Kategori'))
                                    ->badge()
                                    ->color('info'),
                                TextEntry::make('author.full_name')
                                    ->label(__('Penulis'))
                                    ->icon('heroicon-o-user')
                                    ->color('gray'),
                            ]),

                        TextEntry::make('title')
                            ->formatStateUsing(fn ($state) => __($state))
                            ->hiddenLabel()
                            ->weight(FontWeight::Bold)
                            ->size(TextEntrySize::Large)
                            ->extraAttributes(['class' => 'mt-4']),

                        TextEntry::make('published_at')
                            ->hiddenLabel()
                            ->date('d F Y')
                            ->icon('heroicon-o-calendar')
                            ->color('gray'),

                        TextEntry::make('excerpt')
                            ->formatStateUsing(fn ($state) => __($state))
                            ->label(__('Ringkasan'))
                            ->color('gray')
                            ->extraAttributes(['class' => 'bg-gray-50 dark:bg-white/5 p-4 rounded-xl border-l-4 border-warning-500 mt-4']),
                    ])
                    ->extraAttributes(['class' => 'border-0 shadow-none p-0']),

                // Content Section
                Section::make()
                    ->schema([
                        TextEntry::make('content')
                            ->formatStateUsing(fn ($state) => __($state))
                            ->hiddenLabel()
                            ->html()
                            ->prose()
                            ->columnSpanFull(),
                    ])
                    ->extraAttributes(['class' => 'border-0 shadow-none mt-4']),

                // Video Section (If Available)
                Section::make(__('Video Pendukung'))
                    ->icon('heroicon-o-play-circle')
                    ->visible(fn ($record) => (bool) $record->getMediaVideoUrlAttribute())
                    ->schema([
                        ViewEntry::make('video_player')
                            ->view('filament.user.article-video-player')
                            ->columnSpanFull(),
                    ]),

                Section::make(__('Paket Layanan Terkait'))
                    ->icon('heroicon-o-shopping-bag')
                    ->visible(fn ($record) => $record->packages()->exists())
                    ->schema([
                        RepeatableEntry::make('packages')
                            ->hiddenLabel()
                            ->schema([
                                TextEntry::make('name')
                                    ->weight(FontWeight::Bold)
                                    ->color('primary')
                                    ->url(fn ($record) => $record ? PackageResource::getUrl('index').'?tableFilters[id][value]='.$record->id : null),
                                TextEntry::make('final_price')
                                    ->label(__('Mulai dari'))
                                    ->formatStateUsing(fn ($state) => 'Rp '.number_format($state, 2, ',', '.'))
                                    ->color('gray'),
                            ])
                            ->grid(2)
                            ->columnSpanFull(),
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
