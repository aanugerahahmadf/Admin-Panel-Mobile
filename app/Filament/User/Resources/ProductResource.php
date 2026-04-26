<?php

namespace App\Filament\User\Resources;

use App\Filament\User\Resources\ProductResource\Pages;
use App\Models\Product;
use App\Models\Order;
use App\Models\Transaction;
use App\Services\MidtransService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Support\Enums\FontWeight;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'ri-flower-line';

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
        return __('Katalog Bunga');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Katalog Bunga');
    }

    public static function getModelLabel(): string
    {
        return __('Katalog Bunga');
    }

    public static function table(Table $table): Table
    {
        return $table
            ->description(new \Illuminate\Support\HtmlString('<style>.fi-ta-ctn, .fi-ta-content, .fi-ta-header-toolbar, .fi-ta-pagination { background-color: transparent !important; box-shadow: none !important; border-color: transparent !important; }</style>'))
            ->poll('5s')
            ->headerActions([
                Tables\Actions\Action::make('visual_search')
                    ->label(__('Pencarian Produk (AI)'))
                    ->icon('heroicon-o-sparkles')
                    ->color('primary')
                    ->slideOver()
                    ->modalWidth('xl')
                    ->modalHeading(__('Pencarian Visual Cerdas'))
                    ->modalDescription(__('Temukan dekorasi impian Anda dengan mudah. Unggah foto atau ambil gambar langsung untuk melihat koleksi terbaik dari Weeding Flower Decoration.'))
                    ->action(fn() => null)
                    ->modalSubmitActionLabel(__('Tampilkan di Katalog Utama'))
                    ->modalCancelActionLabel(__('Tutup'))
                    ->extraModalWindowAttributes(['class' => 'bg-gray-50/50 backdrop-blur-3xl'])
                    ->form([
                        Forms\Components\Section::make()
                            ->compact()
                            ->schema([
                                Forms\Components\TextInput::make('search')
                                    ->label(__('Cari Visual'))
                                    ->placeholder(__('Ambil foto atau pilih dari galeri...'))
                                    ->prefixIcon('heroicon-m-magnifying-glass')
                                    ->prefixIconColor('gray')
                                    ->live()
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
                                    ->afterStateUpdated(function (\Livewire\Component $livewire, $state, Forms\Set $set, \App\Services\CBIRService $cbirService) {
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
                                        if (!empty($results)) {
                                            $searchTime = $response['query_time_seconds'] ?? 0;
                                            $mixedResults = \App\Filament\User\Resources\PackageResource::buildCbirMixedResults($results);

                                            session()->put('cbir_mixed_results', $mixedResults);
                                            session()->put('cbir_product_results_ids', collect($mixedResults)->where('type', 'product')->pluck('data.id')->all());
                                            session()->put('cbir_search_time', $searchTime);
                                            
                                            $topScore = number_format(($mixedResults[0]['similarity'] ?? 0), 1);
                                            $set('status_message', __('Berhasil menemukan :count hasil! Akurasi: :score%', ['count' => count($mixedResults), 'score' => $topScore]));
                                            $livewire->dispatch('refresh_items');
                                            $livewire->dispatch('refresh_catalog');
                                        } else {
                                            session()->forget(['cbir_mixed_results', 'cbir_product_results_ids', 'cbir_search_time']);
                                            $set('status_message', __('Tidak ada product yang cocok.'));
                                            $livewire->dispatch('refresh_items');
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
                                    ->afterStateUpdated(function (\Livewire\Component $livewire, $state, Forms\Set $set, \App\Services\CBIRService $cbirService) {
                                        if (!$state) return;
                                        $fileObj = is_array($state) ? reset($state) : $state;
                                        $filePath = $fileObj instanceof \Livewire\Features\SupportFileUploads\TemporaryUploadedFile 
                                            ? $fileObj->getRealPath() 
                                            : storage_path('app/public/' . $fileObj);

                                        if (!file_exists($filePath)) return;
                                        
                                        $file = new \Symfony\Component\HttpFoundation\File\File($filePath);
                                        $response = $cbirService->searchByImage($file, 20);
                                        
                                        if (isset($response['error']) || !($response['success'] ?? false)) {
                                            $set('status_message', $response['message'] ?? __('Server AI Offline.'));
                                            return;
                                        }

                                        $results = $response['results'] ?? [];
                                        if (!empty($results)) {
                                            $searchTime = $response['query_time_seconds'] ?? 0;
                                            $mixedResults = \App\Filament\User\Resources\PackageResource::buildCbirMixedResults($results);

                                            session()->put('cbir_mixed_results', $mixedResults);
                                            session()->put('cbir_product_results_ids', collect($mixedResults)->where('type', 'product')->pluck('data.id')->all());
                                            session()->put('cbir_search_time', $searchTime);
                                            
                                            $topScore = number_format(($mixedResults[0]['similarity'] ?? 0), 1);
                                            $set('status_message', __('Berhasil menemukan :count hasil! Akurasi: :score%', ['count' => count($mixedResults), 'score' => $topScore]));
                                            $livewire->dispatch('refresh_items');
                                            $livewire->dispatch('refresh_catalog');
                                        } else {
                                            session()->forget(['cbir_mixed_results', 'cbir_product_results_ids', 'cbir_search_time']);
                                            $set('status_message', __('Product tidak ditemukan.'));
                                            $livewire->dispatch('refresh_items');
                                            $livewire->dispatch('refresh_catalog');
                                        }
                                    }),

                                Forms\Components\Placeholder::make('status_message')
                                    ->label('')
                                    ->content(fn(Forms\Get $get) => new \Illuminate\Support\HtmlString(
                                        '<div class="text-sm">' . e($get('status_message')) . '</div>'
                                    ))
                                    ->visible(fn(Forms\Get $get) => (bool) $get('status_message'))
                                    ->extraAttributes(['class' => 'text-center p-3 bg-primary-600 rounded-xl text-white font-medium shadow-md']),

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
                        session()->forget(['cbir_mixed_results', 'cbir_product_results_ids', 'cbir_search_time']);
                        $livewire->dispatch('refresh_items');
                    })
                    ->visible(fn() => session()->has('cbir_mixed_results')),
            ])
            ->emptyStateHeading(__('Belum ada product tersedia'))
            ->emptyStateDescription(function() {
                if (session()->has('cbir_product_results_ids')) {
                    return new \Illuminate\Support\HtmlString((string)__('Tidak ada product yang cocok dengan foto Anda. Silakan coba foto lain.'));
                }
                return new \Illuminate\Support\HtmlString((string)__('Temukan product impianmu di sini!'));
            })
            ->emptyStateActions([
                Tables\Actions\Action::make('reset_search')
                    ->label(__('Tampilkan Semua'))
                    ->action(function (\Livewire\Component $livewire) {
                        session()->forget(['cbir_mixed_results', 'cbir_product_results_ids', 'cbir_search_time']);
                        $livewire->dispatch('refresh_items');
                    })
                    ->visible(fn() => session()->has('cbir_product_results_ids')),
            ])
            ->columns([
                Tables\Columns\Layout\Stack::make([
                    Tables\Columns\Layout\Stack::make([
                        Tables\Columns\ImageColumn::make('image_url')
                            ->label('')
                            ->height('200px')
                            ->width('100%')
                            ->extraImgAttributes([
                                'class' => 'aspect-square object-cover rounded-t-2xl transition-all duration-500 group-hover:scale-110',
                                'style' => 'height: 200px !important; width: 100%;'
                            ]),
                        
                        Tables\Columns\TextColumn::make('discount_pct')
                            ->state(fn($record) => $record?->discount_price > 0 ? '-' . round((($record->price - $record->discount_price) / $record->price) * 100) . '%' : null)
                            ->extraAttributes([
                                'class' => 'absolute top-2 right-2 font-black px-2 py-1 rounded shadow-lg z-10 transform scale-100 group-hover/img-overlay:scale-110 transition-transform duration-300',
                                'style' => 'background-color: #dc2626 !important; color: #ffffff !important; width: fit-content; font-size: 0.8rem; line-height: 1; pointer-events: none; visibility: visible !important;'
                            ])
                            ->visible(fn($record) => $record?->discount_price > 0),
                    ])->extraAttributes(['class' => 'relative overflow-hidden group/img-overlay']),
                    Tables\Columns\Layout\Stack::make([
                        Tables\Columns\TextColumn::make('category.name')
                            ->badge()
                            ->color('info')
                            ->size('xs'),
                        Tables\Columns\TextColumn::make('name')
                            ->weight('bold')
                            ->size('lg')
                            ->lineClamp(2),
                        Tables\Columns\Layout\Stack::make([
                            Tables\Columns\TextColumn::make('final_price')
                                ->formatStateUsing(fn ($state) => 'Rp ' . number_format($state, 2, ',', '.'))
                                ->weight('black')
                                ->color('primary')
                                ->size('md'),
                            Tables\Columns\TextColumn::make('price')
                                ->formatStateUsing(fn ($state, $record) => $record?->discount_price > 0 ? 'Rp ' . number_format($state, 2, ',', '.') : '')
                                ->size('xs')
                                ->color('danger')
                                ->extraAttributes(['class' => 'line-through opacity-60'])
                                ->visible(fn ($record) => $record?->discount_price > 0),
                        ])->space(0.5),
                        Tables\Columns\TextColumn::make('stock')
                            ->formatStateUsing(fn ($state) => $state > 0 ? $state . ' ' . __('Tersedia') : __('Habis'))
                            ->size('xs')
                            ->color(fn ($state) => $state <= 0 ? 'danger' : ($state <= 3 ? 'warning' : 'gray')),
                    ])->space(2)->extraAttributes(['class' => 'p-4 flex-1 flex flex-col']),
                ])->extraAttributes([
                    'class' => 'bg-white dark:bg-gray-900 rounded-2xl shadow-sm hover:shadow-xl transition-all duration-300 border border-transparent dark:border-white/10 overflow-hidden h-full'
                ]),
            ])
            ->contentGrid([
                'md' => 2,
                'xl' => 3,
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->relationship('category', 'name'),
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
                    ->query(function (\Illuminate\Database\Eloquent\Builder $query, array $data) {
                        if (! ($data['sort_by'] ?? null)) return $query;
                        return match ($data['sort_by']) {
                            'price_asc' => $query->reorder('price', 'asc'),
                            'price_desc' => $query->reorder('price', 'desc'),
                            'latest' => $query->reorder('created_at', 'desc'),
                            default => $query,
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
                    ->disabled(fn (\App\Models\Product $record) => $record->stock <= 0)
                    ->slideOver()
                    ->modalWidth('2xl')
                    ->modalHeading(__('Checkout Produk'))
                    ->steps(fn (\App\Models\Product $record) => static::getCheckoutWizardSteps($record))
                    ->action(function (\App\Models\Product $record, array $data, \Livewire\Component $livewire) {
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
                            'product_id' => $record->id,
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
            ]);
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

                                    // PRODUCT NAME
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
                                            ->formatStateUsing(fn ($record) => $record?->discount_price > 0 ? 'Rp ' . number_format($record->price, 2, ',', '.') : '')
                                            ->size('lg')
                                            ->color('gray')
                                            ->extraAttributes(['class' => 'line-through opacity-50 ml-4'])
                                            ->visible(fn ($record) => $record?->discount_price > 0),
                                    ])->extraAttributes(['class' => 'flex products-baseline mb-6']),

                                    // DESCRIPTION
                                    Infolists\Components\Section::make(__('Tentang Product Ini'))
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
                                            ->label(fn ($record) => $record->stock > 0 ? __('Pesan Sekarang') : __('Stok Habis'))
                                            ->icon(fn ($record) => $record->stock > 0 ? 'heroicon-m-bolt' : 'heroicon-m-x-circle')
                                            ->button()
                                            ->color(fn ($record) => $record->stock > 0 ? 'success' : 'danger')
                                            ->disabled(fn ($record) => $record->stock <= 0)
                                            ->size(\Filament\Support\Enums\ActionSize::Large)
                                            ->extraAttributes(['class' => 'w-full py-3 text-lg rounded-xl shadow-sm transition-all'])
                                            ->slideOver()
                                            ->modalWidth('2xl')
                                            ->modalHeading(__('Checkout Product'))
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
                                                    'product_id' => $record->id,
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
                                                    'type' => 'product',
                                                    'id' => $record->id,
                                                    'name' => $record->name,
                                                    'price' => $record->final_price,
                                                    'image' => $record->getFirstMediaUrl('product_image') ?: $record->image_url,
                                                    'url' => \App\Filament\User\Resources\ProductResource::getUrl('view', ['record' => $record->id]),
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
                                                    ->where('product_id', $record->id)
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
                                                        'product_id' => $record->id,
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
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageProducts::route('/'),
            'view' => Pages\ViewProduct::route('/{record}'),
        ];
    }

    public static function handleCheckout(\App\Models\Product $product, array $data, ?\Livewire\Component $livewire = null): mixed
    {
        $user = \Filament\Facades\Filament::auth()->user();
        if (!$user) return null;

        // Update user phone if changed
        if ($data['phone'] !== $user->phone) {
            $user->update(['phone' => $data['phone']]);
        }

        // Stock Check
        if ($product->stock <= 0) {
            \Filament\Notifications\Notification::make()
                ->title(__('Stok Habis'))
                ->body(__('Mohon maaf, product ini sudah tidak tersedia.'))
                ->danger()
                ->send();
            return null;
        }

        // Decrease Stock
        $product->decrement('stock');

        $finalPrice = (float) $product->final_price;

        // Default statuses
        $orderStatus = \App\Enums\OrderStatus::PENDING;
        $orderPaymentStatus = \App\Enums\OrderPaymentStatus::PENDING;

        // Create Order
        $order = \App\Models\Order::create([
            'user_id'        => $user->id,
            'product_id'        => $product->id,
            'order_number'   => 'ORD-ITM-' . strtoupper(str()->random(8)),
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

        // Process Transaction
        $reference = 'TRX-ITM-' . time() . '-' . strtoupper(str()->random(4));

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

        // Process via Midtrans
        try {
            $midtrans = new \App\Services\MidtransService();
            $transactionCount = $midtrans->createTransactionSnap($transaction);
            
            if ($livewire) {
                $livewire->dispatch('open-midtrans-snap', token: $transactionCount->snap_token);
                return null;
            }
            
            return redirect($transactionCount->payment_url);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('[Midtrans] Product Checkout Redirect failed', [
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

    public static function getCheckoutWizardSteps(\App\Models\Product $product): array
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
                                ->default(auth()->user()?->name)
                                ->disabled(),
                            Forms\Components\TextInput::make('phone')
                                ->label(__('Nomor WhatsApp'))
                                ->default(auth()->user()?->phone)
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
                                ->options(function () use ($product) {
                                    $user = \Filament\Facades\Filament::auth()->user();
                                    if (!$user) return [];

                                    return \App\Models\Voucher::where('is_active', true)
                                        ->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()))
                                        ->where(function ($q) use ($user) {
                                            $q->where('is_global', true)
                                              ->orWhereHas('users', fn($u) => $u->where('users.id', $user->id));
                                        })
                                        ->get()
                                        ->pluck('name', 'id');
                                })
                                ->live()
                                ->afterStateUpdated(function ($state, Forms\Set $set) {
                                    $set('applied_voucher', $state);
                                }),
                        ]),
                ]),
        ];
    }

    /**
     * Format similarity score to specific percentage steps.
     */
    public static function formatSimilarityPct(float $score): int
    {
        $pct = (int) (round($score * 100 / 5) * 5);
        if ($pct === 30) $pct = 35;
        return min(100, max(0, $pct));
    }
}
