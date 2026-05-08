<?php

namespace App\Filament\User\Resources;

use App\Enums\DiscountType;
use App\Enums\OrderPaymentStatus;
use App\Enums\OrderStatus;
use App\Filament\User\Pages\MessagesPage;
use App\Filament\User\Resources\PackageResource\Pages\ManagePackages;
use App\Filament\User\Resources\PackageResource\Pages\ViewPackage;
use App\Models\Cart;
use App\Models\Order;
use App\Models\Package;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\Voucher;
use App\Models\Wishlist;
use App\Providers\NativeServiceProvider;
use App\Services\CBIRService;
use App\Services\ChatService;
use App\Services\MidtransService;
use emmanpbarrameda\FilamentTakePictureField\Forms\Components\TakePicture;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Infolists;
use Filament\Infolists\Components\Actions;
use Filament\Infolists\Components\Actions\Action;
use Filament\Infolists\Components\Group;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Enums\ActionSize;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\HtmlString;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Symfony\Component\HttpFoundation\File\File;

class PackageResource extends Resource
{
    protected static ?string $model = Package::class;

    protected static ?string $navigationIcon = 'ri-gift-line';

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'description', 'category.name', 'weddingOrganizer.name', 'price', 'discount_price'];
    }

    public static function getGlobalSearchResultTitle(Model $record): string
    {
        return __($record->name).($record->stock <= 0 ? ' ('.__('Layanan Habis').')' : '');
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        $price = $record->discount_price > 0 ? $record->discount_price : $record->price;

        return [
            __('Kategori') => __($record->category?->name ?? '-'),
            __('Harga') => 'Rp '.number_format($price, 0, ',', '.'),
            __('Stok') => $record->stock.' '.__('Paket'),
            __('Rating') => number_format($record->reviews()->avg('rating') ?: 5, 1).' ⭐',
        ];
    }

    public static function getGlobalSearchResultUrl(Model $record): ?string
    {
        return static::getUrl('view', ['record' => $record]);
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
        return __('Katalog Paket Dekorasi Bunga');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Katalog Paket Dekorasi Bunga');
    }

    public static function getModelLabel(): string
    {
        return __('Katalog Paket Dekorasi Bunga');
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordUrl(fn ($record) => static::getUrl('view', ['record' => $record]))
            ->poll(NativeServiceProvider::isNativeMobile() ? null : '30s')
            // ->headerActions([
            //     Tables\Actions\Action::make('visual_search')
            //         ->label(__('Pencarian Paket Dekorasi Bunga'))
            //         ->icon('heroicon-o-camera')
            //         ->color('primary')
            //         ->slideOver()
            //         ->modalWidth('full')
            //         ->modalHeading(__('Dekorasi Bunga Pernikahan (AI)'))
            //         ->modalDescription(__('Ubah cara Anda mencari layanan. Unggah foto atau ambil gambar langsung untuk menemukan dekorasi terbaik dari Dekorasi Bunga Pernikahan.'))
            //         ->action(fn () => null)
            //         ->modalSubmitActionLabel(__('Tampilkan di Katalog Utama'))
            //         ->modalCancelActionLabel(__('Tutup'))
            //         ->extraModalWindowAttributes(['class' => 'bg-gray-50/50 backdrop-blur-3xl'])
            //         ->form([
            //             Forms\Components\Section::make('')
            //                 ->compact()
            //                 ->schema([
            //                     Forms\Components\TextInput::make('search')
            //                         ->label(__('Dekorasi Bunga Pernikahan (AI)'))
            //                         ->placeholder(__('Ketik untuk mencari...'))
            //                         ->prefixIcon('heroicon-m-magnifying-glass')
            //                         ->prefixIconColor('gray')
            //                         ->live(debounce: 500)
            //                         ->afterStateUpdated(function (Component $livewire, $state, Forms\Set $set) {
            //                             if (empty($state)) {
            //                                 session()->forget(['cbir_mixed_results', 'cbir_package_results_ids', 'cbir_search_time', 'cbir_context']);
            //                                 $set('status_message', null);
            //                                 $livewire->dispatch('refresh_catalog');

            //                                 return;
            //                             }

            //                             $packages = Package::query()
            //                                 ->where('name', 'like', "%{$state}%")
            //                                 ->orWhere('description', 'like', "%{$state}%")
            //                                 ->orWhereHas('category', fn ($q) => $q->where('name', 'like', "%{$state}%"))
            //                                 ->with(['weddingOrganizer', 'category'])
            //                                 ->limit(20)
            //                                 ->get();

            //                             if ($packages->isEmpty()) {
            //                                 session()->forget(['cbir_mixed_results', 'cbir_package_results_ids', 'cbir_search_time', 'cbir_context']);
            //                                 $set('status_message', __('Tidak ada paket yang cocok untuk pencarian teks.'));
            //                                 $livewire->dispatch('refresh_catalog');

            //                                 return;
            //                             }

            //                             $mixedResults = $packages->map(function ($model) {
            //                                 return [
            //                                     'type' => 'package',
            //                                     'similarity' => 100,
            //                                     'data' => array_merge($model->toArray(), [
            //                                         'image_url' => $model->image_url,
            //                                         'wedding_organizer' => $model->weddingOrganizer?->toArray(),
            //                                     ]),
            //                                 ];
            //                             })->all();

            //                             session()->put('cbir_mixed_results', $mixedResults);
            //                             session()->put('cbir_package_results_ids', collect($mixedResults)->pluck('data.id')->all());
            //                             session()->put('cbir_search_time', 0);
            //                             session()->put('cbir_context', 'package');

            //                             $set('status_message', __('Berhasil menemukan :count hasil teks!', ['count' => count($mixedResults)]));
            //                             $livewire->dispatch('refresh_catalog');
            //                         })
            //                         ->extraAttributes(['class' => 'rounded-3xl shadow-sm border-gray-200 focus:ring-primary-500'])
            //                         ->suffixActions([
            //                             Forms\Components\Actions\Action::make('toggle_camera_search')
            //                                 ->icon('heroicon-o-camera')
            //                                 ->color('gray')
            //                                 ->tooltip(__('Ambil Foto'))
            //                                 ->action(fn (Forms\Set $set, Forms\Get $get) => $set('show_camera', ! $get('show_camera'))),
            //                             Forms\Components\Actions\Action::make('toggle_gallery_search')
            //                                 ->icon('heroicon-o-photo')
            //                                 ->color('gray')
            //                                 ->tooltip(__('Pilih Galeri'))
            //                                 ->action(fn (Forms\Set $set, Forms\Get $get) => $set('show_upload', ! $get('show_upload'))),
            //                         ]),
            //                 ]),

            //             Forms\Components\Grid::make(1)
            //                 ->schema([
            //                     TakePicture::make('camera_image')
            //                         ->hiddenLabel()
            //                         ->visible(fn (Forms\Get $get) => $get('show_camera'))
            //                         ->live()
            //                         ->disk('public')
            //                         ->directory('cbir-camera')
            //                         ->afterStateUpdated(function (Component $livewire, $state, Forms\Set $set, CBIRService $cbirService, Forms\Get $get) {
            //                             if (! $state) {
            //                                 return;
            //                             }
            //                             $filePath = storage_path('app/public/'.$state);
            //                             if (! file_exists($filePath)) {
            //                                 return;
            //                             }
            //                             $file = new File($filePath);
            //                             $response = $cbirService->searchByImage($file, 20);

            //                             if (isset($response['error']) || ! ($response['success'] ?? false)) {
            //                                 $set('status_message', $response['message'] ?? __('Server AI Offline.'));

            //                                 return;
            //                             }

            //                             $results = $response['results'] ?? [];
            //                             Log::info('CBIR Camera Raw Results:', ['results' => $results]);
            //                             if (! empty($results)) {
            //                                 $searchTime = $response['query_time_seconds'] ?? 0;
            //                                 $mixedResults = static::buildCbirMixedResults($results);

            //                                 session()->put('cbir_mixed_results', $mixedResults);
            //                                 session()->put('cbir_package_results_ids', collect($mixedResults)->where('type', 'package')->pluck('data.id')->all());
            //                                 session()->put('cbir_search_time', $searchTime);
            //                                 session()->put('cbir_context', 'package');

            //                                 $topScore = number_format(($mixedResults[0]['similarity'] ?? 0), 1);
            //                                 $set('status_message', __('Berhasil menemukan :count hasil! Akurasi: :score%', ['count' => count($mixedResults), 'score' => $topScore]));
            //                                 $livewire->dispatch('refresh_catalog');
            //                             } else {
            //                                 session()->forget(['cbir_mixed_results', 'cbir_package_results_ids', 'cbir_search_time', 'cbir_context']);
            //                                 $set('status_message', __('Tidak ada dekorasi yang cocok.'));
            //                                 $livewire->dispatch('refresh_catalog');
            //                             }
            //                         }),

            //                     Forms\Components\FileUpload::make('search_image')
            //                         ->hiddenLabel()
            //                         ->image()
            //                         ->imageEditor()
            //                         ->visible(fn (Forms\Get $get) => $get('show_upload'))
            //                         ->directory('cbir-queries')
            //                         ->live()
            //                         // ->extraAttributes(['class' => 'rounded-3xl border-2 border-dashed border-primary-200 bg-primary-50/30'])
            //                         ->afterStateUpdated(function (Component $livewire, $state, Forms\Set $set, CBIRService $cbirService, Forms\Get $get) {
            //                             if (! $state) {
            //                                 return;
            //                             }

            //                             $fileObj = is_array($state) ? reset($state) : $state;
            //                             if ($fileObj instanceof TemporaryUploadedFile) {
            //                                 $filePath = $fileObj->getRealPath();
            //                             } else {
            //                                 $filePath = storage_path('app/public/'.$fileObj);
            //                             }

            //                             if (! file_exists($filePath)) {
            //                                 $set('status_message', __('Gagal membaca file upload. Silakan coba lagi.'));

            //                                 return;
            //                             }

            //                             $file = new File($filePath);
            //                             $response = $cbirService->searchByImage($file, 20);

            //                             if (isset($response['error']) || ! ($response['success'] ?? false)) {
            //                                 $set('status_message', $response['message'] ?? __('Server AI Offline.'));

            //                                 return;
            //                             }

            //                             $results = $response['results'] ?? [];
            //                             Log::info('CBIR Upload Raw Results:', ['results' => $results]);
            //                             if (! empty($results)) {
            //                                 $searchTime = $response['query_time_seconds'] ?? 0;
            //                                 $mixedResults = static::buildCbirMixedResults($results);

            //                                 session()->put('cbir_mixed_results', $mixedResults);
            //                                 session()->put('cbir_package_results_ids', collect($mixedResults)->where('type', 'package')->pluck('data.id')->all());
            //                                 session()->put('cbir_search_time', $searchTime);
            //                                 session()->put('cbir_context', 'package');

            //                                 $topScore = number_format(($mixedResults[0]['similarity'] ?? 0), 1);
            //                                 $set('status_message', __('Berhasil menemukan :count hasil! Akurasi: :score%', ['count' => count($mixedResults), 'score' => $topScore]));
            //                                 $livewire->dispatch('refresh_catalog');
            //                             } else {
            //                                 session()->forget(['cbir_mixed_results', 'cbir_package_results_ids', 'cbir_search_time', 'cbir_context']);
            //                                 $set('status_message', __('Dekorasi tidak ditemukan.'));
            //                                 $livewire->dispatch('refresh_catalog');
            //                             }
            //                         }),

            //                     Forms\Components\Placeholder::make('status_message')
            //                         ->label('')
            //                         ->content(fn (Forms\Get $get) => new HtmlString(
            //                             '<div class="text-sm">'.e($get('status_message')).'</div>'
            //                         ))
            //                         ->visible(fn (Forms\Get $get) => (bool) $get('status_message'))
            //                         ->extraAttributes(['class' => 'text-center p-3 bg-gray-900/80 dark:bg-gray-800 rounded-xl text-white font-medium shadow-md']),

            //                     // ── CBIR Results Preview ──
            //                     Forms\Components\View::make('filament.user.components.cbir-results-preview')
            //                         ->visible(fn () => ! empty(session('cbir_mixed_results'))),
            //                 ]),
            //         ]),
            //     Tables\Actions\Action::make('clear_visual_search')
            //         ->label(__('Reset'))
            //         ->icon('heroicon-o-x-circle')
            //         ->color('danger')
            //         ->action(function (Component $livewire) {
            //             session()->forget(['cbir_mixed_results', 'cbir_package_results_ids', 'cbir_search_time', 'cbir_context']);
            //             $livewire->dispatch('refresh_catalog');
            //         })
            //         ->visible(fn () => session()->has('cbir_mixed_results')),
            // ])
            ->emptyStateHeading(__('Belum ada paket tersedia'))
            ->emptyStateDescription(function () {
                if (session()->has('cbir_package_results_ids')) {
                    return new HtmlString((string) __('Tidak ada paket yang cocok dengan foto Anda. Silakan coba foto lain.'));
                }

                return new HtmlString((string) __('Temukan paket impianmu di sini!'));
            })
            ->emptyStateActions([
                Tables\Actions\Action::make('reset_search')
                    ->label(__('Tampilkan Semua'))
                    ->action(function (Component $livewire) {
                        session()->forget(['cbir_mixed_results', 'cbir_package_results_ids', 'cbir_search_time', 'cbir_context']);
                        $livewire->dispatch('refresh_catalog');
                    })
                    ->visible(fn () => session()->has('cbir_package_results_ids')),
            ])
            ->content(view('filament.user.components.package-catalog-grid'))
            ->filters([
                SelectFilter::make('category_id')
                    ->searchable()
                    ->label(__('Kategori'))
                    ->relationship('category', 'name')
                    ->preload(),
                SelectFilter::make('has_discount')
                    ->searchable()
                    ->label(__('Diskon'))
                    ->options([
                        'yes' => __('Ada Diskon'),
                        'no' => __('Tanpa Diskon'),
                    ])
                    ->query(fn (Builder $query, array $data) => match ($data['value'] ?? null) {
                        'yes' => $query->where('discount_price', '>', 0),
                        'no' => $query->where(fn ($q) => $q->whereNull('discount_price')->orWhere('discount_price', 0)),
                        default => $query,
                    }),
                SelectFilter::make('min_rating')
                    ->searchable()
                    ->label(__('Rating Minimum'))
                    ->options([
                        '5' => '⭐⭐⭐⭐⭐ 5 '.__('Bintang'),
                        '4' => '⭐⭐⭐⭐ 4+ '.__('Bintang'),
                        '3' => '⭐⭐⭐ 3+ '.__('Bintang'),
                        '2' => '⭐⭐ 2+ '.__('Bintang'),
                        '1' => '⭐ 1+ '.__('Bintang'),
                    ])
                    ->query(fn (Builder $query, array $data) => filled($data['value'])
                        ? $query->withAvg('reviews', 'rating')->having('reviews_avg_rating', '>=', (int) $data['value'])
                        : $query
                    ),

                SelectFilter::make('sort_by')
                    ->searchable()
                    ->label(__('Urutkan'))
                    ->options([
                        'latest' => __('Terbaru'),
                        'price_asc' => __('Harga: Terendah'),
                        'price_desc' => __('Harga: Tertinggi'),
                        'rating_desc' => __('Rating Tertinggi'),
                        'most_ordered' => __('Paling Banyak Dipesan'),
                    ])
                    ->query(fn (Builder $query, array $data) => match ($data['value'] ?? null) {
                        'price_asc' => $query->reorder('price', 'asc'),
                        'price_desc' => $query->reorder('price', 'desc'),
                        'latest' => $query->reorder('created_at', 'desc'),
                        'rating_desc' => $query->withAvg('reviews', 'rating')->reorder('reviews_avg_rating', 'desc'),
                        'most_ordered' => $query->withCount('orders')->reorder('orders_count', 'desc'),
                        default => $query,
                    }),
            ], layout: FiltersLayout::AboveContentCollapsible)
            ->filtersTriggerAction(
                fn (Tables\Actions\Action $action) => $action
                    ->icon('heroicon-m-funnel')
                    ->label(__('Filter'))
                    ->color(fn ($livewire) => count($livewire->getTable()->getFilterIndicators()) > 0 ? 'primary' : 'gray')
                    ->badge(fn ($livewire) => count($livewire->getTable()->getFilterIndicators()) > 0 ? count($livewire->getTable()->getFilterIndicators()) : null)
            )
            // ->actions([
            //     Tables\Actions\Action::make('view_detail')
            //         ->label(__('Lihat Detail'))
            //         ->color('warning')
            //         ->button()
            //         ->size('sm')
            //         ->url(fn ($record) => static::getUrl('view', ['record' => $record])),

            //     Tables\Actions\Action::make('buy_now')
            //         ->label(__('Beli'))
            //         ->button()
            //         ->color('success')
            //         ->icon('heroicon-m-bolt')
            //         ->size('sm')
            //         ->extraAttributes(['class' => 'flex-1 justify-center rounded-lg shadow-sm font-bold'])
            //         ->disabled(fn (Package $record) => $record->stock <= 0)
            //         ->slideOver()
            //         ->modalWidth('2xl')
            //         ->modalHeading(__('Checkout Layanan'))
            //         ->steps(fn (Package $record) => static::getCheckoutWizardSteps($record))
            //         ->action(function (Package $record, array $data, Component $livewire) {
            //             return static::handleCheckout($record, $data, $livewire);
            //         }),

            //     Tables\Actions\Action::make('add_to_cart')
            //         ->label('')
            //         ->button()
            //         ->size('sm')
            //         ->icon('heroicon-o-shopping-cart')
            //         ->color('warning')
            //         ->extraAttributes(['class' => 'justify-center rounded-lg shadow-sm'])
            //         ->action(function ($record) {
            //             Cart::updateOrCreate([
            //                 'user_id' => auth()->id(),
            //                 'package_id' => $record->id,
            //             ], [
            //                 'quantity' => DB::raw('quantity + 1'),
            //             ]);

            //             Notification::make()
            //                 ->title(__('Berhasil masuk keranjang'))
            //                 ->success()
            //                 ->icon('heroicon-o-shopping-cart')
            //                 ->send();
            //         })
            //         ->tooltip(__('Masukkan ke Keranjang')),

            //     Tables\Actions\Action::make('toggle_wishlist')
            //         ->label('')
            //         ->button()
            //         ->size('sm')
            //         ->icon(fn ($record) => $record->is_wishlisted ? 'heroicon-s-heart' : 'heroicon-o-heart')
            //         ->color(fn ($record) => $record->is_wishlisted ? 'danger' : 'gray')
            //         ->extraAttributes(['class' => 'justify-center rounded-lg shadow-sm'])
            //         ->action(fn ($record, Component $livewire) => $livewire->dispatch('toggle_wishlist', id: $record->id))
            //         ->tooltip(__('Simpan Favorit')),
            // ])
            ->actionsAlignment('center')
            ->extraAttributes([
                'class' => 'filament-table-actions-container !flex !flex-row !gap-1 !p-3 !bg-gray-50/50 dark:!bg-white/5 !border-0',
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
                                Group::make([
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
                                Group::make([
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
                                    Group::make([
                                        Infolists\Components\TextEntry::make('final_price')
                                            ->label('')
                                            ->formatStateUsing(fn ($state) => 'Rp '.number_format($state, 2, ',', '.'))
                                            ->size('4xl')
                                            ->weight('black')
                                            ->color('success')
                                            ->extraAttributes(['class' => 'drop-shadow-sm']),

                                        Infolists\Components\TextEntry::make('price')
                                            ->label('')
                                            ->formatStateUsing(fn ($record) => $record->discount_price > 0 ? 'Rp '.number_format($record->price, 2, ',', '.') : '')
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
                                    Actions::make([
                                        Action::make('buy_now_detail')
                                            ->label(fn ($record) => $record->stock > 0 ? __('Pesan Sekarang') : __('Layanan Habis'))
                                            ->icon(fn ($record) => $record->stock > 0 ? 'gmdi-shopping-cart-checkout-o' : 'heroicon-m-x-circle')
                                            ->button()
                                            ->color(fn ($record) => $record->stock > 0 ? 'success' : 'danger')
                                            ->outlined(fn ($record) => $record->stock > 0)
                                            ->disabled(fn ($record) => $record->stock <= 0)
                                            ->size(ActionSize::Large)
                                            ->extraAttributes(['class' => 'w-full py-3 text-lg rounded-xl shadow-sm transition-all'])
                                            ->slideOver()
                                            ->modalWidth('full')
                                            ->modalHeading(__('Checkout Layanan'))
                                            ->steps(fn ($record) => static::getCheckoutWizardSteps($record))
                                            ->action(function ($record, array $data, Component $livewire) {
                                                return static::handleCheckout($record, $data, $livewire);
                                            }),

                                        Action::make('add_to_cart_detail')
                                            ->label(__('Masukkan ke Keranjang'))
                                            ->icon('heroicon-m-shopping-cart')
                                            ->button()
                                            ->color('warning')
                                            ->outlined()
                                            ->size(ActionSize::Large)
                                            ->extraAttributes(['class' => 'w-full py-3 text-lg rounded-xl shadow-sm transition-all'])
                                            ->form([
                                                Forms\Components\TextInput::make('quantity')
                                                    ->label(__('Jumlah yang ingin dibeli'))
                                                    ->numeric()
                                                    ->required()
                                                    ->default(1)
                                                    ->minValue(1)
                                                    ->maxValue(fn ($record) => $record->stock)
                                                    ->suffix(__('Paket')),
                                            ])
                                            ->action(function ($record, array $data) {
                                                Cart::updateOrCreate([
                                                    'user_id' => auth()->id(),
                                                    'package_id' => $record->id,
                                                ], [
                                                    'quantity' => DB::raw('quantity + '.$data['quantity']),
                                                ]);

                                                Notification::make()
                                                    ->title(__('Berhasil masuk keranjang'))
                                                    ->body(__('Berhasil menambahkan :count paket ke keranjang.', ['count' => $data['quantity']]))
                                                    ->success()
                                                    ->icon('heroicon-o-shopping-cart')
                                                    ->send();
                                            })
                                            ->visible(fn ($record) => $record->stock > 0),
                                    ])->fullWidth()->extraAttributes(['class' => '!mb-0']),

                                    // SECONDARY: CHAT & WISHLIST
                                    Actions::make([
                                        Action::make('chat_admin')
                                            ->label(__('Chat Admin'))
                                            ->icon('heroicon-m-chat-bubble-left-right')
                                            ->button()
                                            ->color('info')
                                            ->outlined()
                                            ->size(ActionSize::Large)
                                            ->extraAttributes(['class' => 'w-full flex-1 rounded-xl py-3 text-lg shadow-sm transition-all'])
                                            ->action(function ($record) {
                                                $inbox = ChatService::getOrCreateInboxWithAdmin(auth()->id());
                                                ChatService::sendContextMessage($inbox, [
                                                    'type' => 'package',
                                                    'id' => $record->id,
                                                    'name' => $record->name,
                                                    'price' => $record->price,
                                                    'image' => $record->getFirstMediaUrl('package_image') ?: $record->image_url,
                                                    'url' => PackageResource::getUrl('view', ['record' => $record->id]),
                                                ]);

                                                return redirect(MessagesPage::getUrl(['id' => $inbox->id]));
                                            }),

                                        Action::make('wishlist_detail')
                                            ->label(fn ($record) => $record->is_wishlisted ? __('Hapus dari Favorit') : __('Tambah ke Favorit'))
                                            ->icon(fn ($record) => $record->is_wishlisted ? 'heroicon-s-heart' : 'heroicon-o-heart')
                                            ->button()
                                            ->color(fn ($record) => $record->is_wishlisted ? 'danger' : 'gray')
                                            ->outlined(fn ($record) => ! $record->is_wishlisted)
                                            ->size(ActionSize::Large)
                                            ->extraAttributes(['class' => 'w-full flex-1 rounded-xl py-3 text-lg shadow-sm transition-all duration-300'])
                                            ->action(function ($record) {
                                                $userId = Filament::auth()->id();
                                                $deleted = Wishlist::query()->where('user_id', $userId)
                                                    ->where('package_id', $record->id)
                                                    ->delete();

                                                if ($deleted) {
                                                    Notification::make()
                                                        ->title(__('Dihapus dari Favorit'))
                                                        ->warning()
                                                        ->icon('heroicon-o-heart')
                                                        ->send();
                                                } else {
                                                    Wishlist::create([
                                                        'user_id' => $userId,
                                                        'package_id' => $record->id,
                                                    ]);
                                                    Notification::make()
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
                            ->url(fn ($record) => $record->article_id ? ArticleResource::getUrl('index').'?tableFilters[id][value]='.$record->article_id : null),
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
            'index' => ManagePackages::route('/'),
            'view' => ViewPackage::route('/{record}'),
        ];
    }

    public static function getCheckoutWizardSteps(Package $package): array
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
                            Forms\Components\TimePicker::make('booking_time')
                                ->label(__('Waktu Pelaksanaan'))
                                ->required()
                                ->native(false)
                                ->prefixIcon('heroicon-o-clock')
                                ->columnSpanFull(),
                            Forms\Components\TextInput::make('quantity')
                                ->label(__('Jumlah yang ingin dibeli'))
                                ->numeric()
                                ->required()
                                ->default(1)
                                ->minValue(1)
                                ->maxValue(fn ($record) => $record->stock)
                                ->suffix(__('Paket'))
                                ->columnSpanFull(),
                            Forms\Components\Textarea::make('notes')
                                ->label(__('Alamat Lokasi'))
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
                                ->required(),
                            Forms\Components\TextInput::make('whatsapp')
                                ->label(__('Nomor WhatsApp'))
                                ->default(fn () => auth()->user()?->whatsapp ?: auth()->user()?->phone)
                                ->tel()
                                ->required()
                                ->helperText(__('Notifikasi pembayaran akan dikirim ke nomor ini.')),
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
                                    $user = Filament::auth()->user();
                                    if (! $user) {
                                        return [];
                                    }

                                    $vouchers = Voucher::query()->where('is_active', true)
                                        ->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()))
                                        ->whereHas('users', fn ($q) => $q->where('users.id', $user->id)->whereNull('user_vouchers.used_at'))
                                        ->get()
                                        ->filter(fn (Voucher $v) => $v->isValidFor($package->final_price));

                                    return $vouchers->mapWithKeys(function (Voucher $v) {
                                        $amount = $v->discount_type === DiscountType::PERCENTAGE
                                            ? number_format($v->discount_amount, 2, ',', '.').'%'
                                            : 'Rp '.number_format($v->discount_amount, 2, ',', '.');

                                        return [$v->id => $v->code.__(' - Diskon ').$amount];
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
                                    $voucher = Voucher::find($state);
                                    if ($voucher && $voucher->isValidFor($package->final_price)) {
                                        $discount = $voucher->calculateDiscount($package->final_price);
                                        $set('voucher_discount', $discount);
                                        $set('_voucher_info', 'valid:'.$voucher->id.':'.$discount.':'.$voucher->description);
                                    } else {
                                        $set('voucher_id', null);
                                        $set('voucher_discount', 0);
                                        $set('_voucher_info', 'invalid');
                                    }
                                })
                                ->hint(fn (Forms\Get $get) => match (true) {
                                    str_starts_with((string) $get('_voucher_info'), 'valid:') => __('Voucher Berhasil Dipasang!'),
                                    $get('_voucher_info') === 'invalid' => __('Voucher tidak valid'),
                                    default => null,
                                })
                                ->hintIcon(fn (Forms\Get $get) => match (true) {
                                    str_starts_with((string) $get('_voucher_info'), 'valid:') => 'heroicon-m-check-circle',
                                    $get('_voucher_info') === 'invalid' => 'heroicon-m-x-circle',
                                    default => null,
                                })
                                ->hintColor(fn (Forms\Get $get) => str_starts_with((string) $get('_voucher_info'), 'valid:') ? 'success' : 'danger')
                                ->helperText(__('Hanya voucher yang memenuhi syarat minimum belanja yang akan muncul di sini. Jika kosong, silakan ke menu Voucher untuk Klaim.')),

                            Forms\Components\Hidden::make('voucher_discount')->default(0),
                            Forms\Components\Hidden::make('_voucher_info'),

                            Forms\Components\Placeholder::make('_discount_preview')
                                ->hiddenLabel()
                                ->visible(fn (Forms\Get $get) => str_starts_with((string) $get('_voucher_info'), 'valid:'))
                                ->content(function (Forms\Get $get) use ($package) {
                                    $discount = (float) $get('voucher_discount');
                                    $final = $package->final_price - $discount;

                                    return new HtmlString(
                                        '<div class="flex flex-col gap-2 p-4 bg-success-50 dark:bg-success-950 rounded-xl border border-success-200 dark:border-success-800">'.
                                            '<div class="flex justify-between text-sm">'.
                                                '<span class="text-gray-600 dark:text-gray-400">'.__('Harga Paket').'</span>'.
                                                '<span class="font-semibold">Rp '.number_format($package->final_price, 2, ',', '.').'</span>'.
                                            '</div>'.
                                            '<div class="flex justify-between text-sm text-success-600 dark:text-success-400">'.
                                                '<span class="flex products-center gap-1"><svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 6v.75m0 3v.75m0 3v.75m0 3V18m-9-5.25h5.25M7.5 15h3M3.375 5.25c-.621 0-1.125.504-1.125 1.125v3.026a2.999 2.999 0 0 1 0 5.198v3.026c0 .621.504 1.125 1.125 1.125h17.25c.621 0 1.125-.504 1.125-1.125v-3.026a2.999 2.999 0 0 1 0-5.198V6.375c0-.621-.504-1.125-1.125-1.125H3.375Z" /></svg> '.__('Diskon Voucher').'</span>'.
                                                '<span class="font-bold">- Rp '.number_format($discount, 2, ',', '.').'</span>'.
                                            '</div>'.
                                            '<div class="flex justify-between text-base font-bold border-t border-success-300 dark:border-success-700 pt-2">'.
                                                '<span>'.__('Total Bayar').'</span>'.
                                                '<span class="text-success-700 dark:text-success-300">Rp '.number_format(max(0, $final), 2, ',', '.').'</span>'.
                                            '</div>'.
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
                                ->content('Rp '.number_format($package->final_price, 0, ',', '.'))
                                ->extraAttributes(['class' => 'text-primary-600 dark:text-primary-400 font-bold text-2xl']),
                        ]),
                ]),
        ];
    }

    public static function handleCheckout(Package $package, array $data, ?Component $livewire = null): mixed
    {
        $user = Filament::auth()->user();
        if (! $user) {
            return null;
        }

        // Update user whatsapp if changed
        if (($data['whatsapp'] ?? '') !== ($user->whatsapp ?? '')) {
            $user->update(['whatsapp' => $data['whatsapp']]);
        }

        // Stock Check
        if ($package->stock < $data['quantity']) {
            Notification::make()
                ->title(__('Stok Tidak Cukup'))
                ->body(__('Mohon maaf, stok tersedia hanya :count.', ['count' => $package->stock]))
                ->danger()
                ->send();

            return null;
        }

        // Decrease Stock
        $package->decrement('stock', $data['quantity']);

        // Voucher discount
        $voucherId = $data['voucher_id'] ?? null;
        $voucherDiscount = (float) ($data['voucher_discount'] ?? 0);
        $totalBeforeVoucher = $package->final_price * (int) $data['quantity'];
        $finalPrice = max(0, $totalBeforeVoucher - $voucherDiscount);

        // Default statuses
        $orderStatus = OrderStatus::PENDING;
        $orderPaymentStatus = OrderPaymentStatus::PENDING;

        // Create Order
        $order = Order::create([
            'user_id' => $user->id,
            'package_id' => $package->id,
            'order_number' => 'ORD-'.strtoupper(str()->random(8)),
            'total_price' => $finalPrice,
            'status' => $orderStatus,
            'payment_status' => $orderPaymentStatus,
            'booking_date' => $data['booking_date'],
            'booking_time' => $data['booking_time'] ?? null,
            'notes' => $data['notes'],
            'quantity' => $data['quantity'],
        ]);

        // Send message to Admin Panel Chat
        try {
            $inbox = ChatService::getOrCreateInboxWithAdmin($user->id);
            ChatService::sendOrderMessage($inbox, $order);
        } catch (\Exception $e) {
            Log::error('Failed to send order message: '.$e->getMessage());
        }

        // Link voucher if used
        if ($voucherId) {
            $user->vouchers()->updateExistingPivot($voucherId, [
                'order_id' => $order->id,
            ]);
        }

        // Process Type
        $reference = 'TRX-'.time().'-'.strtoupper(str()->random(4));

        $transaction = Transaction::create([
            'user_id' => $user->id,
            'order_id' => $order->id,
            'type' => 'order',
            'reference_number' => $reference,
            'amount' => $finalPrice,
            'admin_fee' => 0,
            'total_amount' => $finalPrice,
            'payment_gateway' => 'midtrans',
            'status' => 'pending',
            'notes' => null,
        ]);

        // Process Checkout Transaction entirely through Midtrans
        try {
            $midtrans = new MidtransService;
            $transactionCount = $midtrans->createTransactionSnap($transaction);

            if ($livewire) {
                $livewire->dispatch('open-midtrans-snap', token: $transactionCount->snap_token);

                return null;
            }

            return redirect($transactionCount->payment_url);
        } catch (\Exception $e) {
            Log::error('[Midtrans] Checkout Redirect failed', [
                'reference' => $transaction->reference_number,
                'error' => $e->getMessage(),
            ]);

            Notification::make()
                ->title(__('Gagal Membuat Pembayaran'))
                ->body(__('Midtrans error: '.$e->getMessage().'. Transaksi Anda tersimpan, silakan ulangi pembayaran di "Pesanan Saya".'))
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
        $pkgIds = collect($results)->where('type', 'package')->pluck('owner_id')->all();
        $prodIds = collect($results)->where('type', 'product')->pluck('owner_id')->all();

        $packages = Package::query()->whereIn('id', $pkgIds, 'and', false)->with('weddingOrganizer', 'category')->get()->keyBy('id');
        $products = Product::query()->whereIn('id', $prodIds, 'and', false)->with('weddingOrganizer', 'category')->get()->keyBy('id');

        // Jika CBIR hanya return packages, tambahkan products dari DB sebagai fallback global
        if (empty($prodIds) && ! empty($pkgIds)) {
            // Ambil semua active products dan masukkan dengan similarity 0
            $allProducts = Product::query()
                ->where('is_active', true)
                ->with('weddingOrganizer', 'category')
                ->get();

            foreach ($allProducts as $product) {
                $results[] = [
                    'type' => 'product',
                    'owner_id' => $product->id,
                    'score' => 0,
                ];
                $products->put($product->id, $product);
            }
        }

        // Jika CBIR hanya return products, tambahkan packages dari DB sebagai fallback global
        if (empty($pkgIds) && ! empty($prodIds)) {
            $allPackages = Package::query()
                ->with('weddingOrganizer', 'category')
                ->get();

            foreach ($allPackages as $package) {
                $results[] = [
                    'type' => 'package',
                    'owner_id' => $package->id,
                    'score' => 0,
                ];
                $packages->put($package->id, $package);
            }
        }

        return collect($results)
            ->unique(fn ($res) => ($res['type'] ?? 'package').'-'.($res['owner_id'] ?? 0))
            ->map(function ($res) use ($packages, $products) {
                $type = $res['type'] ?? 'package';
                $model = $type === 'product' ? $products->get($res['owner_id']) : $packages->get($res['owner_id']);
                if (! $model) {
                    return null;
                }

                return [
                    'type' => $type,
                    'similarity' => ($res['score'] ?? 0) * 100,
                    'data' => array_merge($model->toArray(), [
                        'image_url' => $model->image_url,
                        'wedding_organizer' => $model->weddingOrganizer?->toArray(),
                        'category' => $model->category?->toArray(),
                    ]),
                ];
            })
            ->filter()
            ->sortByDesc('similarity')
            ->values()
            ->all();
    }
}
