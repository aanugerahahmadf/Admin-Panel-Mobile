<?php

namespace App\Filament\User\Resources;

use App\Models\Package;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Support\Enums\FontWeight;
use Filament\Notifications\Notification;
use App\Models\Order;
use App\Filament\User\Resources\ArticleResource;
use App\Filament\User\Pages\MessagesPage;
use Illuminate\Database\Eloquent\Builder;

class PackageResource extends Resource
{
    protected static ?string $model = Package::class;

    protected static ?string $navigationIcon = 'ri-gift-line';

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'description', 'category.name', 'weddingOrganizer.name'];
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getModel()::count();
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return static::getNavigationLabel();
    }


    public static function getNavigationGroup(): ?string
    {
        return __('Belanja & Jelajahi');
    }

    public static function getNavigationLabel(): string
    {
        return __('Katalog Paket Bunga');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Katalog Paket Bunga');
    }

    public static function getModelLabel(): string
    {
        return __('Katalog Paket Bunga');
    }



    public static function table(Table $table): Table
    {
        return $table
            ->description(new \Illuminate\Support\HtmlString('<style>.fi-ta-ctn, .fi-ta-content, .fi-ta-header-toolbar, .fi-ta-pagination { background-color: transparent !important; box-shadow: none !important; border-color: transparent !important; }</style>'))
            ->poll('5s')
            ->headerActions([
                Tables\Actions\Action::make('visual_search')
                    ->label(__('Dekorasi Bunga Pernikahan (AI)'))
                    ->icon('heroicon-o-sparkles')
                    ->color('primary')
                    ->slideOver()
                    ->modalWidth('xl')
                    ->modalHeading(__('Dekorasi Bunga Pernikahan (AI)'))
                    ->modalDescription(__('Ubah cara Anda mencari layanan. Unggah foto atau ambil gambar langsung untuk menemukan dekorasi terbaik dari Dekorasi Bunga Pernikahan.'))
                    ->action(fn() => null) // empty action just to allow submit to close modal and trigger table reload
                    ->modalSubmitActionLabel(__('Tampilkan di Katalog Utama'))
                    ->modalCancelActionLabel(__('Tutup'))
                    ->extraModalWindowAttributes(['class' => 'bg-gray-50/50 backdrop-blur-3xl'])
                    ->form([
                        Forms\Components\Section::make()
                            ->compact()
                            ->schema([
                                Forms\Components\TextInput::make('search')
                                ->label(__('Dekorasi Bunga Pernikahan (AI)'))
                                    ->prefixIcon('heroicon-m-magnifying-glass')
                                    ->prefixIconColor('gray')
                                    ->live()
                                    ->extraAttributes(['class' => 'rounded-3xl shadow-sm border-gray-200 focus:ring-primary-500'])
                                    ->suffixActions([
                                        Forms\Components\Actions\Action::make('toggle_camera_search')
                                            ->icon('heroicon-o-camera')
                                            ->color('gray')
                                            ->tooltip(__('Ambil Foto'))
                                            ->action(fn(Forms\Set $set, Forms\Get $get) => $set('show_camera', ! $get('show_camera'))),
                                        Forms\Components\Actions\Action::make('toggle_gallery_search')
                                            ->icon('heroicon-o-photo')
                                            ->color('gray')
                                            ->tooltip(__('Pilih Galeri'))
                                            ->action(fn(Forms\Set $set, Forms\Get $get) => $set('show_upload', ! $get('show_upload'))),
                                    ]),
                            ]),

                        Forms\Components\Grid::make(1)
                            ->schema([
                                \emmanpbarrameda\FilamentTakePictureField\Forms\Components\TakePicture::make('camera_image')
                                    ->hiddenLabel()
                                    ->visible(fn(Forms\Get $get) => $get('show_camera'))
                                    ->live()
                                    ->disk('public')
                                    ->directory('cbir-camera')
                                    // ->extraAttributes(['class' => 'rounded-3xl overflow-hidden ring-4 ring-primary-500/20'])
                                    ->afterStateUpdated(function (\Livewire\Component $livewire, $state, Forms\Set $set, \App\Services\CBIRService $cbirService, Forms\Get $get) {
                                        if (!$state) return;
                                        $filePath = storage_path('app/public/' . $state);
                                        if (!file_exists($filePath)) return;
                                        $file = new \Symfony\Component\HttpFoundation\File\File($filePath);
                                        $response = $cbirService->searchByImage($file, 20);
                                        
                                        if (isset($response['error']) || !($response['success'] ?? false)) {
                                            $set('status_message', $response['message'] ?? __('Server AI Offline.'));
                                            return;
                                        }

                                        $results = $response['results'] ?? [];
                                        \Illuminate\Support\Facades\Log::info('CBIR Camera Raw Results:', ['results' => $results]);
                                        if (!empty($results)) {
                                            $searchTime = $response['query_time_seconds'] ?? 0;
                                            $mixedResults = static::buildCbirMixedResults($results);

                                            session()->put('cbir_mixed_results', $mixedResults);
                                            session()->put('cbir_package_results_ids', collect($mixedResults)->where('type', 'package')->pluck('data.id')->all());
                                            session()->put('cbir_search_time', $searchTime);
                                            
                                            $topScore = number_format(($mixedResults[0]['similarity'] ?? 0), 1);
                                            $set('status_message', __('Berhasil menemukan :count hasil! Akurasi: :score%', ['count' => count($mixedResults), 'score' => $topScore]));
                                            $livewire->dispatch('refresh_catalog');
                                        } else {
                                            session()->forget(['cbir_mixed_results', 'cbir_package_results_ids', 'cbir_search_time']);
                                            $set('status_message', __('Tidak ada dekorasi yang cocok.'));
                                            $livewire->dispatch('refresh_catalog');
                                        }
                                    }),

                                Forms\Components\FileUpload::make('search_image')
                                    ->hiddenLabel()
                                    ->image()
                                    ->imageEditor()
                                    ->visible(fn(Forms\Get $get) => $get('show_upload'))
                                    ->directory('cbir-queries')
                                    ->live()
                                    // ->extraAttributes(['class' => 'rounded-3xl border-2 border-dashed border-primary-200 bg-primary-50/30'])
                                    ->afterStateUpdated(function (\Livewire\Component $livewire, $state, Forms\Set $set, \App\Services\CBIRService $cbirService, Forms\Get $get) {
                                        if (!$state) return;
                                        
                                        $fileObj = is_array($state) ? reset($state) : $state;
                                        if ($fileObj instanceof \Livewire\Features\SupportFileUploads\TemporaryUploadedFile) {
                                            $filePath = $fileObj->getRealPath();
                                        } else {
                                            $filePath = storage_path('app/public/' . $fileObj);
                                        }

                                        if (!file_exists($filePath)) {
                                            $set('status_message', __('Gagal membaca file upload. Silakan coba lagi.'));
                                            return;
                                        }
                                        
                                        $file = new \Symfony\Component\HttpFoundation\File\File($filePath);
                                        $response = $cbirService->searchByImage($file, 20);
                                        
                                        if (isset($response['error']) || !($response['success'] ?? false)) {
                                            $set('status_message', $response['message'] ?? __('Server AI Offline.'));
                                            return;
                                        }

                                        $results = $response['results'] ?? [];
                                        \Illuminate\Support\Facades\Log::info('CBIR Upload Raw Results:', ['results' => $results]);
                                        if (!empty($results)) {
                                            $searchTime = $response['query_time_seconds'] ?? 0;
                                            $mixedResults = static::buildCbirMixedResults($results);

                                            session()->put('cbir_mixed_results', $mixedResults);
                                            session()->put('cbir_package_results_ids', collect($mixedResults)->where('type', 'package')->pluck('data.id')->all());
                                            session()->put('cbir_search_time', $searchTime);
                                            
                                            $topScore = number_format(($mixedResults[0]['similarity'] ?? 0), 1);
                                            $set('status_message', __('Berhasil menemukan :count hasil! Akurasi: :score%', ['count' => count($mixedResults), 'score' => $topScore]));
                                            $livewire->dispatch('refresh_catalog');
                                        } else {
                                            session()->forget(['cbir_mixed_results', 'cbir_package_results_ids', 'cbir_search_time']);
                                            $set('status_message', __('Dekorasi tidak ditemukan.'));
                                            $livewire->dispatch('refresh_catalog');
                                        }
                                    }),

                                Forms\Components\Placeholder::make('status_message')
                                    ->label('')
                                    ->content(fn(Forms\Get $get) => new \Illuminate\Support\HtmlString(
                                        '<div class="text-sm">' . e($get('status_message')) . '</div>'
                                    ))
                                    ->visible(fn(Forms\Get $get) => (bool) $get('status_message'))
                                    ->extraAttributes(['class' => 'text-center p-3 bg-gray-900/80 dark:bg-gray-800 rounded-xl text-white font-medium shadow-md']),

                                // ── CBIR Results Preview ──
                                Forms\Components\View::make('filament.user.components.cbir-results-preview')
                                    ->visible(fn() => !empty(session('cbir_mixed_results'))),
                            ]),
                    ]),
                Tables\Actions\Action::make('clear_visual_search')
                    ->label(__('Reset'))
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->action(function (\Livewire\Component $livewire) {
                        session()->forget(['cbir_mixed_results', 'cbir_package_results_ids', 'cbir_search_time']);
                        $livewire->dispatch('refresh_catalog');
                    })
                    ->visible(fn() => session()->has('cbir_mixed_results')),
            ])
            ->emptyStateHeading(__('Belum ada paket tersedia'))
            ->emptyStateDescription(function() {
                if (session()->has('cbir_package_results_ids')) {
                    return new \Illuminate\Support\HtmlString((string)__('Tidak ada paket yang cocok dengan foto Anda. Silakan coba foto lain.'));
                }
                return new \Illuminate\Support\HtmlString((string)__('Temukan paket impianmu di sini!'));
            })
            ->emptyStateActions([
                Tables\Actions\Action::make('reset_search')
                    ->label(__('Tampilkan Semua'))
                    ->action(function (\Livewire\Component $livewire) {
                        session()->forget(['cbir_mixed_results', 'cbir_package_results_ids', 'cbir_search_time']);
                        $livewire->dispatch('refresh_catalog');
                    })
                    ->visible(fn() => session()->has('cbir_package_results_ids')),
            ])
            ->contentGrid([
                'sm' => 1,
                'md' => 2,
                'lg' => 3,
                'xl' => 4,
            ])
            ->columns([
                Tables\Columns\Layout\Stack::make([
                    // Image with absolute discount badge placeholder logic
                    Tables\Columns\Layout\Stack::make([
                        Tables\Columns\ImageColumn::make('image_url')
                            ->label('')
                            ->height('200px')
                            ->width('100%')
                            ->defaultImageUrl(fn ($record) => asset('images/placeholders/image-placeholder.png'))
                            ->url(fn ($record) => static::getUrl('view', ['record' => $record]))
                            ->extraAttributes(['class' => 'w-full flex justify-center products-center bg-gray-50 dark:bg-gray-800 rounded-t-2xl overflow-hidden'])
                            ->extraImgAttributes([
                                'class' => 'aspect-square object-contain transition-all duration-500 group-hover:scale-110 !mx-auto',
                                'style' => 'height: 200px !important; width: 100%; object-fit: cover;',
                                'onerror' => "this.src='" . asset('images/placeholders/image-placeholder.png') . "'",
                            ]),
                        
                        // Discount Badge overlay (top right)
                        Tables\Columns\TextColumn::make('discount_pct')
                            ->state(fn($record) => $record?->discount_price > 0 ? '-' . round((($record->price - $record->discount_price) / $record->price) * 100) . '%' : null)
                            ->extraAttributes([
                                'class' => 'absolute top-2 right-2 font-black px-2 py-1 rounded shadow-lg z-10 transform scale-100 group-hover/img-overlay:scale-110 transition-transform duration-300',
                                'style' => 'background-color: #dc2626 !important; color: #ffffff !important; width: fit-content; font-size: 0.8rem; line-height: 1; pointer-events: none; visibility: visible !important;'
                            ])
                            ->visible(fn($record) => $record?->discount_price > 0),
                    ])->extraAttributes(['class' => 'relative overflow-hidden group/img-overlay']),

                    Tables\Columns\Layout\Stack::make([
                        // Category & Name
                        Tables\Columns\TextColumn::make('category.name')
                            ->formatStateUsing(fn ($state) => __($state))
                            ->badge()
                            ->color('warning')
                            ->size('xs'),
                            
                        Tables\Columns\TextColumn::make('name')
                            ->formatStateUsing(fn ($state) => __($state))
                            ->weight('bold')
                            ->size('sm')
                            ->lineClamp(2)
                            ->extraAttributes(['class' => 'h-[3rem] flex products-center leading-tight overflow-hidden'])
                            ->url(fn ($record) => static::getUrl('view', ['record' => $record])),

                        // Price Row
                        Tables\Columns\Layout\Stack::make([
                            Tables\Columns\TextColumn::make('price_display')
                                ->state(fn ($record) => $record?->discount_price > 0 ? $record->discount_price : $record?->price)
                                ->formatStateUsing(fn ($state) => $state ? 'Rp ' . number_format($state, 2, ',', '.') : '')
                                ->weight('black')
                                ->color('primary')
                                ->size('md'),
                            
                            Tables\Columns\TextColumn::make('original_price')
                                ->state(fn ($record) => $record?->discount_price > 0 ? $record->price : null)
                                ->formatStateUsing(fn ($state) => $state ? 'Rp ' . number_format($state, 2, ',', '.') : '')
                                ->size('xs')
                                ->color('gray')
                                ->extraAttributes(['class' => 'line-through opacity-60'])
                                ->visible(fn ($record) => (bool)($record?->discount_price > 0)),
                        ])->space(0.5)->extraAttributes(['class' => 'mt-1']),

                        // Stats Footer
                        Tables\Columns\Layout\Split::make([
                            Tables\Columns\TextColumn::make('avg_rating')
                                ->state(fn ($record) => $record ? number_format($record->reviews()->avg('rating') ?: 0, 1) : null)
                                ->icon('heroicon-m-star')
                                ->iconColor('warning')
                                ->size('xs')
                                ->color('gray')
                                ->weight('bold'),
                                
                            Tables\Columns\TextColumn::make('stock_display')
                                ->state(fn ($record) => $record ? ($record->stock > 0 ? $record->stock . ' ' . __('Tersedia') : __('Habis')) : null)
                                ->size('xs')
                                ->color(fn ($record) => $record?->stock <= 0 ? 'danger' : ($record?->stock <= 3 ? 'warning' : 'gray'))
                                ->weight(fn ($record) => $record?->stock <= 3 ? 'bold' : 'normal')
                                ->alignEnd()
                                ->extraAttributes(['class' => 'opacity-80']),
                        ])->extraAttributes(['class' => 'pt-3 mt-auto']),

                    ])->space(1)->extraAttributes(['class' => 'p-3 flex-1 flex flex-col']),
                ])->extraAttributes([
                    'class' => 'bg-white dark:bg-gray-900 rounded-2xl shadow-sm hover:shadow-xl transition-all duration-300 group border border-transparent dark:border-white/10 !h-[450px] !min-h-[450px] !max-h-[450px] flex flex-col overflow-hidden'
                ]),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category_id')
                    ->searchable()
                    ->label(__('Kategori'))
                    ->relationship('category', 'name')
                    ->native(false)
                    ->preload(),
                Tables\Filters\Filter::make('sort_by')
                    ->form([
                        Forms\Components\Select::make('sort_by')
                            ->label(__('Urutkan'))
                            ->options([
                                'latest' => __('Terbaru'),
                                'price_asc' => __('Harga: Terendah'),
                                'price_desc' => __('Harga: Tertinggi'),
                            ])
                            ->searchable()
                            ->native(false),
                    ])
                    ->query(function (Builder $query, array $data) {
                        if (! ($data['sort_by'] ?? null)) {
                            return $query;
                        }
                 
                        return match ($data['sort_by']) {
                            'price_asc' => $query->reorder('price', 'asc'),
                            'price_desc' => $query->reorder('price', 'desc'),
                            'latest' => $query->reorder('created_at', 'desc'),
                            default => $query,
                        };
                    })
                    ->indicateUsing(function (array $data): ?string {
                        if (! ($data['sort_by'] ?? null)) {
                            return null;
                        }
                 
                        return __('Urutan') . ': ' . match ($data['sort_by']) {
                            'price_asc' => __('Harga Terendah'),
                            'price_desc' => __('Harga Tertinggi'),
                            'latest' => __('Terbaru'),
                            default => null,
                        };
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('view_detail')
                    ->label(__('Lihat Detail'))
                    ->icon('heroicon-m-eye')
                    ->color('warning')
                    ->button()
                    ->size('sm')
                    ->extraAttributes(['class' => 'flex-1 justify-center rounded-lg shadow-sm font-bold'])
                    ->url(fn ($record) => static::getUrl('view', ['record' => $record])),

                Tables\Actions\Action::make('buy_now')
                    ->label(__('Beli'))
                    ->button()
                    ->color('success')
                    ->icon('heroicon-m-bolt')
                    ->size('sm')
                    ->extraAttributes(['class' => 'flex-1 justify-center rounded-lg shadow-sm font-bold'])
                    ->disabled(fn (\App\Models\Package $record) => $record->stock <= 0)
                    ->slideOver()
                    ->modalWidth('2xl')
                    ->modalHeading(__('Checkout Layanan'))
                    ->steps(fn (\App\Models\Package $record) => static::getCheckoutWizardSteps($record))
                    ->action(function (\App\Models\Package $record, array $data, \Livewire\Component $livewire) {
                        return static::handleCheckout($record, $data, $livewire);
                    }),

                Tables\Actions\Action::make('add_to_cart')
                    ->label('')
                    ->button()
                    ->size('sm')
                    ->icon('heroicon-o-shopping-cart')
                    ->color('warning')
                    ->extraAttributes(['class' => 'justify-center rounded-lg shadow-sm'])
                    ->action(function ($record) {
                        \App\Models\Cart::updateOrCreate([
                            'user_id' => auth()->id(),
                            'package_id' => $record->id,
                        ], [
                            'quantity' => \Illuminate\Support\Facades\DB::raw('quantity + 1')
                        ]);
                        
                        \Filament\Notifications\Notification::make()
                            ->title(__('Berhasil masuk keranjang'))
                            ->success()
                            ->icon('heroicon-o-shopping-cart')
                            ->send();
                    })
                    ->tooltip(__('Masukkan ke Keranjang')),

                Tables\Actions\Action::make('toggle_wishlist')
                    ->label('')
                    ->button()
                    ->size('sm')
                    ->icon(fn ($record) => $record->is_wishlisted ? 'heroicon-s-heart' : 'heroicon-o-heart')
                    ->color(fn ($record) => $record->is_wishlisted ? 'danger' : 'gray')
                    ->extraAttributes(['class' => 'justify-center rounded-lg shadow-sm'])
                    ->action(fn ($record, \Livewire\Component $livewire) => $livewire->dispatch('toggle_wishlist', id: $record->id))
                    ->tooltip(__('Simpan Favorit')),
            ])
            ->actionsAlignment('center')
            ->extraAttributes([
                'class' => 'filament-table-actions-container !flex !flex-row !gap-1 !p-3 !bg-gray-50/50 dark:!bg-white/5 !border-0'
            ])
            ->defaultSort('created_at', 'desc')
            ->persistSortInSession()
            ->persistFiltersInSession();
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make()
                    ->schema([
                        Infolists\Components\Grid::make(12)
                            ->schema([
                                // LEFT: PRODUCT IMAGE
                                \Filament\Infolists\Components\Group::make([
                                    Infolists\Components\ImageEntry::make('image_url')
                                        ->label('')
                                        ->hiddenLabel()
                                        ->alignCenter()
                                        ->height('22rem')
                                        ->extraAttributes(['class' => 'flex products-center justify-center bg-white/5 rounded-3xl overflow-hidden border border-white/10 shadow-inner'])
                                        ->extraImgAttributes([
                                            'class' => 'max-w-full max-h-full object-contain mx-auto transition-transform hover:scale-105 duration-500 p-2',
                                        ]),
                                ])->columnSpan([
                                    'default' => 12,
                                    'md' => 5,
                                ]),

                                // RIGHT: PRODUCT IDENTITY
                                \Filament\Infolists\Components\Group::make([
                                    // CATEGORY BADGE
                                    Infolists\Components\TextEntry::make('category.name')
                                        ->formatStateUsing(fn ($state) => __($state))
                                        ->label('')
                                        ->badge()
                                        ->color('info')
                                        ->icon('heroicon-m-tag')
                                        ->extraAttributes(['class' => 'mb-2']),

                                    // PKG NAME
                                    Infolists\Components\TextEntry::make('name')
                                        ->formatStateUsing(fn ($state) => __($state))
                                        ->label('')
                                        ->hiddenLabel()
                                        ->weight('black')
                                        ->size('4xl')
                                        ->extraAttributes(['class' => 'tracking-tight text-transparent bg-clip-text bg-gradient-to-r from-primary-600 to-primary-400 mb-4 uppercase leading-tight']),

                                    // PRICE DISPLAY
                                    \Filament\Infolists\Components\Group::make([
                                        Infolists\Components\TextEntry::make('final_price')
                                            ->label('')
                                            ->formatStateUsing(fn ($state) => 'Rp ' . number_format($state, 2, ',', '.'))
                                            ->size('4xl')
                                            ->weight('black')
                                            ->color('success')
                                            ->extraAttributes(['class' => 'drop-shadow-sm']),
                                        
                                        Infolists\Components\TextEntry::make('price')
                                            ->label('')
                                            ->formatStateUsing(fn ($record) => $record->discount_price > 0 ? 'Rp ' . number_format($record->price, 2, ',', '.') : '')
                                            ->size('lg')
                                            ->color('gray')
                                            ->extraAttributes(['class' => 'line-through opacity-50 ml-4'])
                                            ->visible(fn ($record) => $record->discount_price > 0),
                                    ])->extraAttributes(['class' => 'flex products-baseline mb-6']),

                                    // DESCRIPTION
                                    Infolists\Components\Section::make(__('Tentang Layanan Ini'))
                                        ->compact()
                                        ->schema([
                                            Infolists\Components\TextEntry::make('description')
                                                ->formatStateUsing(fn ($state) => __($state))
                                                ->label('')
                                                ->html()
                                                ->prose()
                                                ->extraAttributes(['class' => 'text-gray-600 dark:text-gray-300 leading-relaxed text-lg']),
                                        ])->icon('heroicon-o-document-text')->iconColor('primary'),

                                    // PRIMARY CTA: BUY & CART
                                    \Filament\Infolists\Components\Actions::make([
                                        \Filament\Infolists\Components\Actions\Action::make('buy_now_detail')
                                            ->label(fn ($record) => $record->stock > 0 ? __('Pesan Sekarang') : __('Layanan Habis'))
                                            ->icon(fn ($record) => $record->stock > 0 ? 'heroicon-m-bolt' : 'heroicon-m-x-circle')
                                            ->button()
                                            ->color(fn ($record) => $record->stock > 0 ? 'success' : 'danger')
                                            ->disabled(fn ($record) => $record->stock <= 0)
                                            ->size(\Filament\Support\Enums\ActionSize::Large)
                                            ->extraAttributes(['class' => 'w-full py-3 text-lg rounded-xl shadow-sm transition-all'])
                                            ->slideOver()
                                            ->modalWidth('2xl')
                                            ->modalHeading(__('Checkout Layanan'))
                                            ->steps(fn ($record) => static::getCheckoutWizardSteps($record))
                                            ->action(function ($record, array $data, \Livewire\Component $livewire) {
                                                return static::handleCheckout($record, $data, $livewire);
                                            }),

                                        \Filament\Infolists\Components\Actions\Action::make('add_to_cart_detail')
                                            ->label(__('Masukkan ke Keranjang'))
                                            ->icon('heroicon-m-shopping-cart')
                                            ->button()
                                            ->color('warning')
                                            ->outlined()
                                            ->size(\Filament\Support\Enums\ActionSize::Large)
                                            ->extraAttributes(['class' => 'w-full py-3 text-lg rounded-xl shadow-sm transition-all'])
                                            ->action(function ($record) {
                                                \App\Models\Cart::updateOrCreate([
                                                    'user_id' => auth()->id(),
                                                    'package_id' => $record->id,
                                                ], [
                                                    'quantity' => \Illuminate\Support\Facades\DB::raw('quantity + 1')
                                                ]);

                                                \Filament\Notifications\Notification::make()
                                                    ->title(__('Berhasil masuk keranjang'))
                                                    ->success()
                                                    ->icon('heroicon-o-shopping-cart')
                                                    ->send();
                                            })
                                            ->visible(fn ($record) => $record->stock > 0),
                                    ])->fullWidth()->extraAttributes(['class' => '!mb-0']),

                                    // SECONDARY: CHAT & WISHLIST
                                    \Filament\Infolists\Components\Actions::make([
                                        \Filament\Infolists\Components\Actions\Action::make('chat_admin')
                                            ->label(__('Chat Admin'))
                                            ->icon('heroicon-m-chat-bubble-left-right')
                                            ->button()
                                            ->color('info')
                                            ->outlined()
                                            ->size(\Filament\Support\Enums\ActionSize::Large)
                                            ->extraAttributes(['class' => 'w-full flex-1 rounded-xl py-3 text-lg shadow-sm transition-all'])
                                            ->action(function ($record) {
                                                $inbox = \App\Services\ChatService::getOrCreateInboxWithAdmin(auth()->id());
                                                \App\Services\ChatService::sendContextMessage($inbox, [
                                                    'type' => 'package',
                                                    'id' => $record->id,
                                                    'name' => $record->name,
                                                    'price' => $record->price,
                                                    'image' => $record->getFirstMediaUrl('package_image') ?: $record->image_url,
                                                    'url' => \App\Filament\User\Resources\PackageResource::getUrl('view', ['record' => $record->id]),
                                                ]);
                                                return redirect(\App\Filament\User\Pages\MessagesPage::getUrl(['id' => $inbox->id]));
                                            }),

                                        \Filament\Infolists\Components\Actions\Action::make('wishlist_detail')
                                            ->label(fn ($record) => $record->is_wishlisted ? __('Hapus dari Favorit') : __('Tambah ke Favorit'))
                                            ->icon(fn ($record) => $record->is_wishlisted ? 'heroicon-s-heart' : 'heroicon-o-heart')
                                            ->button()
                                            ->color(fn ($record) => $record->is_wishlisted ? 'danger' : 'gray')
                                            ->outlined(fn ($record) => !$record->is_wishlisted)
                                            ->size(\Filament\Support\Enums\ActionSize::Large)
                                            ->extraAttributes(['class' => 'w-full flex-1 rounded-xl py-3 text-lg shadow-sm transition-all duration-300'])
                                            ->action(function ($record) {
                                                $userId = \Filament\Facades\Filament::auth()->id();
                                                $wishlist = \App\Models\Wishlist::where('user_id', $userId)
                                                    ->where('package_id', $record->id)
                                                    ->first();

                                                if ($wishlist) {
                                                    $wishlist->delete();
                                                    \Filament\Notifications\Notification::make()
                                                        ->title(__('Dihapus dari Favorit'))
                                                        ->warning()
                                                        ->icon('heroicon-o-heart')
                                                        ->send();
                                                } else {
                                                    \App\Models\Wishlist::create([
                                                        'user_id' => $userId,
                                                        'package_id' => $record->id,
                                                    ]);
                                                    \Filament\Notifications\Notification::make()
                                                        ->title(__('Disimpan ke Favorit'))
                                                        ->success()
                                                        ->icon('heroicon-s-heart')
                                                        ->iconColor('danger')
                                                        ->send();
                                                }
                                            }),
                                    ])->fullWidth()->extraAttributes(['class' => '!mt-2']),
                                ])->columnSpan([
                                    'default' => 12,
                                    'md' => 7,
                                ]),
                            ])
                        ->extraAttributes(['class' => 'gap-10 p-2']),
                    ])
                    ->extraAttributes(['class' => 'border-none bg-transparent shadow-none']),

                // RELATED ARTICLE (WISDOM & TIPS)
                Infolists\Components\Section::make(__('Wawasan & Tips Terkait'))
                    ->icon('heroicon-o-book-open')
                    ->iconColor('info')
                    ->visible(fn ($record) => $record->article_id !== null)
                    ->schema([
                        Infolists\Components\TextEntry::make('article.title')
                            ->formatStateUsing(fn ($state) => __($state))
                            ->label(__('Judul Artikel'))
                            ->weight(FontWeight::Bold)
                            ->color('info')
                            ->size('lg')
                            ->url(fn ($record) => $record->article_id ? ArticleResource::getUrl('index') . '?tableFilters[id][value]=' . $record->article_id : null),
                        Infolists\Components\TextEntry::make('article.excerpt')
                            ->formatStateUsing(fn ($state) => __($state))
                            ->label('')
                            ->prose()
                            ->extraAttributes(['class' => 'italic opacity-80 mt-2']),
                    ])->compact(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\User\Resources\PackageResource\Pages\ManagePackages::route('/'),
            'view' => \App\Filament\User\Resources\PackageResource\Pages\ViewPackage::route('/{record}'),
        ];
    }

    public static function getCheckoutWizardSteps(\App\Models\Package $package): array
    {
        return [
            Forms\Components\Wizard\Step::make(__('Detail Acara'))
                ->icon('heroicon-o-calendar-days')
                ->schema([
                    Forms\Components\Section::make(__('Pilih Waktu & Kebutuhan'))
                        ->schema([
                            Forms\Components\DatePicker::make('booking_date')
                                ->label(__('Rencana Tanggal Acara'))
                                ->required()
                                ->native(false)
                                ->minDate(now()->addDays(7))
                                ->prefixIcon('heroicon-o-calendar-days')
                                ->columnSpanFull(),
                            Forms\Components\Textarea::make('notes')
                                ->label(__('Catatan Khusus / Alamat Lokasi'))
                                ->rows(4)
                                ->required()
                                ->columnSpanFull(),
                        ]),
                ]),
            Forms\Components\Wizard\Step::make(__('Info Kontak'))
                ->icon('heroicon-o-user-circle')
                ->schema([
                    Forms\Components\Section::make(__('Verifikasi Data Anda'))
                        ->schema([
                            Forms\Components\TextInput::make('customer_name')
                                ->label(__('Nama Lengkap'))
                                ->disabled(),
                            Forms\Components\TextInput::make('phone')
                                ->label(__('Nomor WhatsApp'))
                                ->tel()
                                ->required(),
                        ])->columns(2),
                ]),
            Forms\Components\Wizard\Step::make(__('Voucher & Diskon'))
                ->icon('heroicon-o-ticket')
                ->schema([
                    Forms\Components\Section::make(__('Pilih Voucher Anda'))
                        ->description(__('Gunakan voucher yang telah Anda klaim di menu Voucher.'))
                        ->icon('heroicon-o-ticket')
                        ->schema([
                            Forms\Components\Select::make('voucher_id')
                                ->searchable()
                                ->label(__('Voucher Tersedia'))
                                ->prefixIcon('heroicon-o-ticket')
                                ->options(function () use ($package) {
                                    $user = \Filament\Facades\Filament::auth()->user();
                                    if (!$user) return [];

                                    $vouchers = \App\Models\Voucher::where('is_active', true)
                                        ->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()))
                                        ->whereHas('users', fn ($q) => $q->where('users.id', $user->id)->whereNull('user_vouchers.used_at'))
                                        ->get()
                                        ->filter(fn (\App\Models\Voucher $v) => $v->isValidFor($package->final_price));

                                    return $vouchers->mapWithKeys(function (\App\Models\Voucher $v) {
                                        $amount = $v->discount_type === \App\Enums\DiscountType::PERCENTAGE 
                                            ? number_format($v->discount_amount, 2, ',', '.') . '%' 
                                            : 'Rp ' . number_format($v->discount_amount, 2, ',', '.');
                                        return [$v->id => $v->code . __(' - Diskon ') . $amount];
                                    });
                                })
                                ->searchable()
                                ->preload()
                                ->live()
                                ->afterStateUpdated(function (Forms\Set $set, Forms\Get $get, ?string $state) use ($package) {
                                    if (! $state) {
                                        $set('voucher_discount', 0);
                                        $set('_voucher_info', null);
                                        return;
                                    }
                                    $voucher = \App\Models\Voucher::find($state);
                                    if ($voucher && $voucher->isValidFor($package->final_price)) {
                                        $discount = $voucher->calculateDiscount($package->final_price);
                                        $set('voucher_discount', $discount);
                                        $set('_voucher_info', 'valid:' . $voucher->id . ':' . $discount . ':' . $voucher->description);
                                    } else {
                                        $set('voucher_id', null);
                                        $set('voucher_discount', 0);
                                        $set('_voucher_info', 'invalid');
                                    }
                                })
                                ->hint(fn (Forms\Get $get) => match(true) {
                                    str_starts_with((string)$get('_voucher_info'), 'valid:') => __('Voucher Berhasil Dipasang!'),
                                    $get('_voucher_info') === 'invalid' => __('Voucher tidak valid'),
                                    default => null,
                                })
                                ->hintIcon(fn (Forms\Get $get) => match(true) {
                                    str_starts_with((string)$get('_voucher_info'), 'valid:') => 'heroicon-m-check-circle',
                                    $get('_voucher_info') === 'invalid' => 'heroicon-m-x-circle',
                                    default => null,
                                })
                                ->hintColor(fn (Forms\Get $get) => str_starts_with((string)$get('_voucher_info'), 'valid:') ? 'success' : 'danger')
                                ->helperText(__('Hanya voucher yang memenuhi syarat minimum belanja yang akan muncul di sini. Jika kosong, silakan ke menu Voucher untuk Klaim.')),

                            Forms\Components\Hidden::make('voucher_discount')->default(0),
                            Forms\Components\Hidden::make('_voucher_info'),

                            Forms\Components\Placeholder::make('_discount_preview')
                                ->hiddenLabel()
                                ->visible(fn (Forms\Get $get) => str_starts_with((string)$get('_voucher_info'), 'valid:'))
                                ->content(function (Forms\Get $get) use ($package) {
                                    $discount = (float) $get('voucher_discount');
                                    $final = $package->final_price - $discount;
                                    return new \Illuminate\Support\HtmlString(
                                        '<div class="flex flex-col gap-2 p-4 bg-success-50 dark:bg-success-950 rounded-xl border border-success-200 dark:border-success-800">' .
                                            '<div class="flex justify-between text-sm">' .
                                                '<span class="text-gray-600 dark:text-gray-400">' . __('Harga Paket') . '</span>' .
                                                '<span class="font-semibold">Rp ' . number_format($package->final_price, 2, ',', '.') . '</span>' .
                                            '</div>' .
                                            '<div class="flex justify-between text-sm text-success-600 dark:text-success-400">' .
                                                '<span class="flex products-center gap-1"><svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 6v.75m0 3v.75m0 3v.75m0 3V18m-9-5.25h5.25M7.5 15h3M3.375 5.25c-.621 0-1.125.504-1.125 1.125v3.026a2.999 2.999 0 0 1 0 5.198v3.026c0 .621.504 1.125 1.125 1.125h17.25c.621 0 1.125-.504 1.125-1.125v-3.026a2.999 2.999 0 0 1 0-5.198V6.375c0-.621-.504-1.125-1.125-1.125H3.375Z" /></svg> ' . __('Diskon Voucher') . '</span>' .
                                                '<span class="font-bold">- Rp ' . number_format($discount, 2, ',', '.') . '</span>' .
                                            '</div>' .
                                            '<div class="flex justify-between text-base font-bold border-t border-success-300 dark:border-success-700 pt-2">' .
                                                '<span>' . __('Total Bayar') . '</span>' .
                                                '<span class="text-success-700 dark:text-success-300">Rp ' . number_format(max(0, $final), 2, ',', '.') . '</span>' .
                                            '</div>' .
                                        '</div>'
                                    );
                                }),
                        ]),
                ]),

            Forms\Components\Wizard\Step::make(__('Konfirmasi'))
                ->icon('heroicon-o-check-badge')
                ->schema([
                    Forms\Components\Section::make(__('Ringkasan Pembayaran'))
                        ->schema([
                            Forms\Components\Placeholder::make('pkg_summary')
                                ->label(__('Paket Dekorasi'))
                                ->content($package->name),
                            Forms\Components\Placeholder::make('price_summary')
                                ->label(__('Total Harga'))
                                ->content('Rp ' . number_format($package->final_price, 0, ',', '.'))
                                ->extraAttributes(['class' => 'text-primary-600 dark:text-primary-400 font-bold text-2xl']),
                        ]),
                ]),
        ];
    }

    public static function handleCheckout(\App\Models\Package $package, array $data, ?\Livewire\Component $livewire = null): mixed
    {
        $user = \Filament\Facades\Filament::auth()->user();
        if (!$user) return null;

        // Update user phone if changed
        if ($data['phone'] !== $user->phone) {
            $user->update(['phone' => $data['phone']]);
        }

        // Stock Check
        if ($package->stock <= 0) {
            \Filament\Notifications\Notification::make()
                ->title(__('Stok Habis'))
                ->body(__('Mohon maaf, paket ini sudah tidak tersedia.'))
                ->danger()
                ->send();
            return null;
        }

        // Decrease Stock
        $package->decrement('stock');

        // Voucher discount
        $voucherId = $data['voucher_id'] ?? null;
        $voucherDiscount = (float) ($data['voucher_discount'] ?? 0);
        $finalPrice = max(0, $package->final_price - $voucherDiscount);

        // Default statuses
        $orderStatus = \App\Enums\OrderStatus::PENDING;
        $orderPaymentStatus = \App\Enums\OrderPaymentStatus::PENDING;

        // Create Order
        $order = \App\Models\Order::create([
            'user_id'        => $user->id,
            'package_id'     => $package->id,
            'order_number'   => 'ORD-' . strtoupper(str()->random(8)),
            'total_price'    => $finalPrice,
            'status'         => $orderStatus,
            'payment_status' => $orderPaymentStatus,
            'booking_date'   => $data['booking_date'],
            'notes'          => $data['notes'],
        ]);

        // Send message to Admin Panel Chat
        try {
            $inbox = \App\Services\ChatService::getOrCreateInboxWithAdmin($user->id);
            \App\Services\ChatService::sendOrderMessage($inbox, $order);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to send order message: ' . $e->getMessage());
        }

        // Link voucher if used
        if ($voucherId) {
            $user->vouchers()->updateExistingPivot($voucherId, [
                'order_id' => $order->id,
            ]);
        }

        // Process Type
        $reference = 'TRX-' . time() . '-' . strtoupper(str()->random(4));

        $transaction = \App\Models\Transaction::create([
            'user_id'          => $user->id,
            'order_id'         => $order->id,
            'type'             => 'order',
            'reference_number' => $reference,
            'amount'           => $finalPrice,
            'admin_fee'        => 0,
            'total_amount'     => $finalPrice,
            'payment_gateway'  => 'midtrans',
            'status'           => 'pending',
            'notes'            => null,
        ]);

        // Process Checkout Transaction entirely through Midtrans
        try {
            $midtrans = new \App\Services\MidtransService();
            $transactionCount = $midtrans->createTransactionSnap($transaction);
            
            if ($livewire) {
                $livewire->dispatch('open-midtrans-snap', token: $transactionCount->snap_token);
                return null;
            }
            
            return redirect($transactionCount->payment_url);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('[Midtrans] Checkout Redirect failed', [
                'reference' => $transaction->reference_number,
                'error'     => $e->getMessage(),
            ]);
            
            \Filament\Notifications\Notification::make()
                ->title(__('Gagal Membuat Pembayaran'))
                ->body(__('Midtrans error: ' . $e->getMessage() . '. Transaksi Anda tersimpan, silakan ulangi pembayaran di "Pesanan Saya".'))
                ->danger()
                ->send();
                
            return redirect()->route('filament.user.resources.orders.index');
        }
    }

    /**
     * Format similarity score to specific percentage steps:
     * 0, 5, 10, 15, 20, 25, 35, 40, 45, 50, 55, 60, 65, 70, 75, 80, 85, 90, 95, 100
     */
    public static function formatSimilarityPct(float $score): int
    {
        $pct = (int) (round($score * 100 / 5) * 5);
        
        // Skip 30 as requested (25 -> 35 jump)
        if ($pct === 30) {
            // If raw score is >= 0.3 then round up to 35, else round down to 25?
            // Usually, if it hit 30, it means it was in [27.5, 32.5). 
            // We'll just push it to 35 to follow the list's next available step.
            $pct = 35;
        }
        
        return min(100, max(0, $pct));
    }

    /**
     * Build mixed CBIR results from both packages and products.
     */
    public static function buildCbirMixedResults(array $results): array
    {
        $pkgIds  = collect($results)->where('type', 'package')->pluck('owner_id')->all();
        $prodIds = collect($results)->where('type', 'product')->pluck('owner_id')->all();

        $packages = \App\Models\Package::whereIn('id', $pkgIds)->with('weddingOrganizer', 'category')->get()->keyBy('id');
        $products = \App\Models\Product::whereIn('id', $prodIds)->with('weddingOrganizer', 'category')->get()->keyBy('id');

        return collect($results)
            ->map(function ($res) use ($packages, $products) {
                $type  = $res['type'] ?? 'package';
                $model = $type === 'product' ? $products->get($res['owner_id']) : $packages->get($res['owner_id']);
                if (!$model) return null;

                return [
                    'type'       => $type,
                    'similarity' => ($res['score'] ?? 0) * 100,
                    'data'       => array_merge($model->toArray(), [
                        'image_url'         => $model->image_url,
                        'wedding_organizer' => $model->weddingOrganizer?->toArray(),
                    ]),
                ];
            })
            ->filter()
            ->sortByDesc('similarity')
            ->values()
            ->all();
    }
}
