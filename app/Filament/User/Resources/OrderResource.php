<?php

namespace App\Filament\User\Resources;

use App\Enums\OrderPaymentStatus;
use App\Enums\OrderStatus;
use App\Helpers\NativeNotificationHelper;
use App\Filament\User\Resources\OrderResource\Pages\ManageOrders;
use App\Models\Order;
use App\Models\Transaction;
use App\Services\MidtransService;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;
use Livewire\Component;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    public static function getGloballySearchableAttributes(): array
    {
        return ['order_number', 'package.name', 'package.weddingOrganizer.name'];
    }

    public static function getNavigationGroup(): ?string
    {
        return __('Transaksi & Aktivitas');
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getModel()::where('user_id', Filament::auth()->id())->count();
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return static::getNavigationLabel();
    }

    public static function getNavigationLabel(): string
    {
        return __('Pesanan Saya');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Pesanan Saya');
    }

    public static function getModelLabel(): string
    {
        return __('Pesanan Saya');
    }

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make(__('Edit Pesanan'))
                    ->description(__('Anda hanya dapat merubah catatan dan tanggal acara.'))
                    ->schema([
                        Forms\Components\DatePicker::make('booking_date')
                            ->label(__('Tanggal Acara'))
                            ->required()
                            ->native(false)
                            ->minDate(now()->addDays(7))
                            ->prefixIcon('heroicon-o-calendar-days'),
                        Forms\Components\Textarea::make('notes')
                            ->label(__('Catatan Khusus'))
                            ->rows(4)
                            ->required(),
                    ]),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('user_id', Filament::auth()->id());
    }

    public static function table(Table $table): Table
    {
        return $table
            ->poll(\App\Providers\NativeServiceProvider::isNativeMobile() ? null : '30s')
            ->emptyStateHeading(__('Belum ada pesanan'))
            ->emptyStateDescription(__('Wujudkan acara impianmu dengan paket terbaik dari kami. Mulai pesan sekarang!'))
            ->emptyStateIcon('heroicon-o-shopping-bag')
            ->emptyStateActions([
                Tables\Actions\Action::make('shop_products')
                    ->label(__('Belanja Bunga'))
                    ->url(ProductResource::getUrl())
                    ->button()
                    ->color('info')
                    ->size('lg')
                    ->icon('ri-flower-line'),
                Tables\Actions\Action::make('book_package')
                    ->label(__('Pesan Paket Dekorasi'))
                    ->url(PackageResource::getUrl())
                    ->button()
                    ->color('primary')
                    ->size('lg')
                    ->icon('ri-gift-line'),
            ])
            ->contentGrid([
                'default' => 2,
                'md' => 3,
                'lg' => 4,
                'xl' => 5,
            ])
            ->columns([
                Tables\Columns\Layout\Stack::make([
                    // Image Section with Status Overlay
                    Tables\Columns\Layout\Stack::make([
                        Tables\Columns\ImageColumn::make('package.image_url')
                            ->label('')
                            ->height('140px')
                            ->width('100%')
                            ->extraAttributes(['class' => 'w-full flex justify-center products-center bg-gray-50 dark:bg-gray-800 rounded-t-2xl overflow-hidden'])
                            ->extraImgAttributes([
                                'class' => 'aspect-video object-cover transition-all duration-500 group-hover:scale-110 !mx-auto',
                                'style' => 'height: 140px; width: 100%;',
                            ]),

                    ])->extraAttributes(['class' => 'relative overflow-hidden group/img-overlay']),

                    Tables\Columns\Layout\Stack::make([
                        // Category Badge
                        Tables\Columns\TextColumn::make('package.category.name')
                            ->formatStateUsing(fn ($state) => __($state))
                            ->badge()
                            ->color('warning')
                            ->size('xs')
                            ->alignCenter()
                            ->extraAttributes(['class' => 'mt-1 mb-1']),

                        // Store Info
                        Tables\Columns\TextColumn::make('package.weddingOrganizer.name')
                            ->color('gray')
                            ->size('xs')
                            ->weight('bold')
                            ->alignCenter(),

                        // Package Name
                        Tables\Columns\TextColumn::make('package.name')
                            ->formatStateUsing(fn ($state) => __($state))
                            ->weight('bold')
                            ->size('xs')
                            ->lineClamp(1)
                            ->color('info')
                            ->alignCenter()
                            ->extraAttributes(['class' => 'mt-1']),

                        // Order ID
                        Tables\Columns\TextColumn::make('order_number')
                            ->prefix('#')
                            ->size('xs')
                            ->color('gray')
                            ->weight('medium')
                            ->alignCenter(),

                        // Booking Date & Total Price
                        Tables\Columns\Layout\Stack::make([
                            Tables\Columns\TextColumn::make('booking_date')
                                ->label(__('Tanggal:'))
                                ->date('d M Y')
                                ->icon('heroicon-m-calendar-days')
                                ->size('xs')
                                ->color('primary')
                                ->alignCenter(),
                            Tables\Columns\TextColumn::make('payment_status')
                                ->badge()
                                ->size('xs')
                                ->alignCenter(),
                            Tables\Columns\TextColumn::make('total_price')
                                ->formatStateUsing(fn ($state) => 'Rp '.number_format($state, 2, ',', '.'))
                                ->weight('black')
                                ->size('xs')
                                ->color('primary')
                                ->alignCenter(),
                        ])->space(2)->extraAttributes(['class' => 'mt-3']),

                        // Rating Stats
                        Tables\Columns\TextColumn::make('avg_rating')
                            ->state(fn ($record) => $record->package ? number_format($record->package->reviews()->avg('rating') ?: 0, 1) : '5.0')
                            ->icon('heroicon-m-star')
                            ->iconColor('warning')
                            ->size('xs')
                            ->color('gray')
                            ->weight('bold')
                            ->alignCenter()
                            ->extraAttributes(['class' => 'pt-3 mt-auto']),
                    ])->space(1)->extraAttributes(['class' => 'p-2.5 flex-1 flex flex-col']),
                ])->extraAttributes([
                    'class' => 'bg-white dark:bg-gray-900 rounded-2xl shadow-sm hover:shadow-xl transition-all duration-300 overflow-hidden group border border-transparent dark:border-white/10 flex flex-col',
                ]),
            ])

            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->searchable()
                    ->options(OrderStatus::class)
                    ->label(__('Status Pesanan')),
                Tables\Filters\Filter::make('id')
                    ->form([
                        Forms\Components\TextInput::make('value')
                            ->label(__('ID')),
                    ])
                    ->query(fn (Builder $query, array $data) => $query->when($data['value'], fn ($q, $id) => $q->where('id', $id)))
                    ->hidden(),
            ], layout: \Filament\Tables\Enums\FiltersLayout::AboveContentCollapsible)
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->hiddenLabel()
                    ->tooltip(__('Detail'))
                    ->icon('heroicon-m-eye')
                    ->button()
                    ->size('sm')
                    ->color('gray')
                    ->slideOver()
                    ->modalWidth('2xl')
                    ->extraAttributes(['class' => 'order-3 !rounded-lg justify-center gap-0']),

                Tables\Actions\EditAction::make()
                    ->hiddenLabel()
                    ->tooltip(__('Ubah'))
                    ->icon('heroicon-m-pencil-square')
                    ->button()
                    ->size('sm')
                    ->color('warning')
                    ->extraAttributes(['class' => 'order-4 !rounded-lg justify-center gap-0'])
                    ->visible(fn ($record) => in_array($record?->status, [
                        OrderStatus::PENDING,
                        OrderStatus::CONFIRMED,
                        OrderStatus::COMPLETED,
                    ]))
                    ->after(fn () => NativeNotificationHelper::success(__('Pesanan berhasil diperbarui.'))),

                Tables\Actions\DeleteAction::make()
                    ->hiddenLabel()
                    ->tooltip(__('Batal'))
                    ->icon('heroicon-m-trash')
                    ->button()
                    ->size('sm')
                    ->color('danger')
                    ->extraAttributes(['class' => 'order-5 !rounded-lg justify-center gap-0'])
                    ->visible(fn ($record) => in_array($record?->status, [
                        OrderStatus::PENDING,
                        OrderStatus::CONFIRMED,
                        OrderStatus::COMPLETED,
                    ])),

                Tables\Actions\Action::make('pay_midtrans')
                    ->hiddenLabel()
                    ->tooltip(__('Bayar Sekarang'))
                    ->button()
                    ->color('primary')
                    ->size('sm')
                    ->icon('heroicon-m-credit-card')
                    ->extraAttributes(['class' => 'flex-1 !rounded-lg shadow-sm font-bold order-1 justify-center gap-0'])
                    ->visible(fn ($record) => in_array($record?->payment_status, [
                        OrderPaymentStatus::UNPAID,
                        OrderPaymentStatus::FAILED,
                        OrderPaymentStatus::PENDING,
                    ]))
                    ->action(function (Order $record, Component $livewire) {
                        try {
                            // Selalu buat transaksi dengan ref baru biar Midtrans nggak komplain "Duplicate Order ID"
                            $reference = 'PAY-'.strtoupper(str()->random(5)).'-'.$record->id;
                            $transaction = Transaction::create([
                                'user_id' => $record->user_id,
                                'order_id' => $record->id,
                                'type' => 'order',
                                'reference_number' => $reference,
                                'amount' => $record->total_price,
                                'admin_fee' => 0,
                                'total_amount' => $record->total_price,
                                'payment_gateway' => 'midtrans',
                                'status' => 'pending',
                                'notes' => __('Pembayaran via Midtrans untuk Pesanan #').$record->order_number,
                            ]);
                            $record->update(['payment_status' => OrderPaymentStatus::PENDING]);

                            $midtrans = new MidtransService;
                            if (! $transaction->snap_token) {
                                $transaction = $midtrans->createTransactionSnap($transaction);
                            }
                            $livewire->dispatch('open-midtrans-snap', token: $transaction->snap_token);
                        } catch (\Exception $e) {
                            Notification::make()->title(__('Gagal Memuat Pembayaran'))->body($e->getMessage())->danger()->send();
                        }
                    }),

                Tables\Actions\Action::make('refresh_midtrans_status')
                    ->hiddenLabel()
                    ->tooltip(__('Cek Status Pembayaran'))
                    ->icon('heroicon-o-arrow-path')
                    ->color('info')
                    ->button()
                    ->size('sm')
                    ->extraAttributes(['class' => 'flex-1 !rounded-lg shadow-sm font-bold order-2 justify-center gap-0'])
                    ->visible(fn ($record) => $record?->payment_status === OrderPaymentStatus::PENDING)
                    ->action(function (Order $record) {
                        try {
                            $transaction = $record->latestTransaction;
                            if (! $transaction) {
                                return;
                            }
                            $midtrans = new MidtransService;
                            $status = $midtrans->getStatus($midtrans->getMidtransOrderId($transaction));
                            $data = (array) $status;
                            if ($midtrans->isSuccess($data)) {
                                $transaction->markAsSuccess();
                                Notification::make()->title(__('Pembayaran Berhasil!'))->success()->send();
                            } elseif ($midtrans->isFailed($data)) {
                                $transaction->markAsFailed('Midtrans: '.($data['transaction_status'] ?? 'failed'));
                                Notification::make()->title(__('Pembayaran Gagal/Kadaluarsa'))->danger()->send();
                            } else {
                                Notification::make()->title(__('Pembayaran Masih Pending'))->info()->send();
                            }
                        } catch (\Exception $e) {
                            Notification::make()->title(__('Gagal Sinkronisasi'))->body($e->getMessage())->danger()->send();
                        }
                    }),
            ])
            ->actionsAlignment('center')
            ->headerActions([
                Tables\Actions\Action::make('clear_history')
                    ->label(__('Bersihkan Riwayat'))
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->button()
                    ->size('sm')
                    ->requiresConfirmation()
                    ->modalHeading(__('Bersihkan Riwayat Pesanan'))
                    ->modalDescription(__('Pilih jenis pesanan yang ingin Anda hapus secara permanen dari riwayat.'))
                    ->form([
                        Forms\Components\Tabs::make('Delete Options')
                            ->tabs([
                                Forms\Components\Tabs\Tab::make(__('Berdasarkan Status'))
                                    ->icon('heroicon-o-tag')
                                    ->schema([
                                        Forms\Components\Select::make('type')
                                            ->label(__('Hapus Pesanan Berdasarkan Status'))
                                            ->options([
                                                'all' => __('Semua Pesanan'),
                                                'cancelled' => __('Hanya Pesanan Dibatalkan'),
                                                'completed' => __('Hanya Pesanan Selesai'),
                                            ])
                                            ->default('all')
                                            ->native(false),
                                    ]),
                                Forms\Components\Tabs\Tab::make(__('Pilih Pesanan Spesifik'))
                                    ->icon('heroicon-o-list-bullet')
                                    ->schema([
                                        Forms\Components\CheckboxList::make('order_ids')
                                            ->label(__('Pilih Nomor Pesanan'))
                                            ->options(fn () => Order::where('user_id', auth()->id())
                                                ->latest()
                                                ->get()
                                                ->mapWithKeys(fn ($order) => [
                                                    $order->id => "#{$order->order_number} - ".($order->package?->name ?? __('Layanan')),
                                                ]))
                                            ->bulkToggleable()
                                            ->searchable()
                                            ->columns(1),
                                    ]),
                            ])->columnSpanFull(),
                    ])
                    ->action(function (array $data) {
                        $query = Order::query()->where('user_id', auth()->id());
                        
                        if (! empty($data['order_ids'])) {
                            // Jika ada yang dipilih spesifik, hapus yang dipilih saja
                            $query->whereIn('id', $data['order_ids']);
                        } else {
                            // Jika tidak ada yang dipilih spesifik, gunakan pilihan status
                            if ($data['type'] === 'cancelled') {
                                $query->where('status', \App\Enums\OrderStatus::CANCELLED);
                            } elseif ($data['type'] === 'completed') {
                                $query->where('status', \App\Enums\OrderStatus::COMPLETED);
                            }
                        }

                        $query->delete();
                        \Filament\Notifications\Notification::make()
                            ->title(__('Riwayat Berhasil Dibersihkan'))
                            ->success()
                            ->send();
                    })
                    ->visible(fn () => Order::query()->where('user_id', auth()->id())->exists()),
            ])
            ->actionsAlignment('center')
            ->extraAttributes([
                'class' => 'filament-table-actions-container !flex !flex-row !gap-1 !p-3 !bg-gray-50/50 dark:!bg-white/5 !border-t dark:!border-gray-800',
            ]);

    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                // Alert/Status Box
                Infolists\Components\Section::make()
                    ->schema([
                        Infolists\Components\Grid::make(3)->schema([
                            Infolists\Components\TextEntry::make('status')
                                ->label(__('Status Pesanan'))
                                ->badge()
                                ->size(Infolists\Components\TextEntry\TextEntrySize::Large),
                            Infolists\Components\TextEntry::make('payment_status')
                                ->label(__('Status Pembayaran'))
                                ->badge()
                                ->size(Infolists\Components\TextEntry\TextEntrySize::Large),
                            Infolists\Components\TextEntry::make('order_number')
                                ->label(__('No. Pesanan'))
                                ->weight(FontWeight::Bold)
                                ->copyable(),
                        ]),
                    ])
                    ->extraAttributes(['class' => 'bg-gray-50 dark:bg-white/5 border-0 shadow-none rounded-2xl mb-4']),

                // Ordered Product
                Infolists\Components\Section::make(__('Paket Dipesan'))
                    ->icon('heroicon-o-shopping-bag')
                    ->iconColor('primary')
                    ->compact()
                    ->schema([
                        Infolists\Components\Grid::make()->schema([
                            Infolists\Components\ImageEntry::make('package.image_url')
                                ->hiddenLabel()
                                ->height('6rem')
                                ->width('6rem')
                                ->extraImgAttributes(['class' => 'rounded-xl object-cover shadow-sm'])
                                ->grow(false),
                            Infolists\Components\Group::make([
                                Infolists\Components\TextEntry::make('package.name')
                                    ->formatStateUsing(fn ($state) => __($state))
                                    ->hiddenLabel()
                                    ->weight(FontWeight::Bold)
                                    ->size(Infolists\Components\TextEntry\TextEntrySize::Large),
                                Infolists\Components\TextEntry::make('package.weddingOrganizer.name')
                                    ->hiddenLabel()
                                    ->icon('govicon-building')
                                    ->color('gray'),
                                Infolists\Components\TextEntry::make('booking_date')
                                    ->label(__('Untuk Tanggal Acara:'))
                                    ->inlineLabel()
                                    ->date('d F Y')
                                    ->weight(FontWeight::Bold)
                                    ->color('primary'),
                            ])->columnSpan(2),
                        ])->columns(3),
                    ]),

                // Pricing
                Infolists\Components\Section::make(__('Rincian Harga'))
                    ->icon('heroicon-o-currency-dollar')
                    ->iconColor('success')
                    ->compact()
                    ->schema([
                        Infolists\Components\TextEntry::make('total_price')
                            ->label(__('Total Pembayaran'))
                            ->formatStateUsing(fn ($state) => 'Rp '.number_format($state, 2, ',', '.'))
                            ->size(Infolists\Components\TextEntry\TextEntrySize::Large)
                            ->weight(FontWeight::Bold)
                            ->color('primary')
                            ->inlineLabel(),
                    ]),

                // Notes
                Infolists\Components\Section::make(__('Catatan Pemesan'))
                    ->icon('heroicon-o-document-text')
                    ->iconColor('gray')
                    ->compact()
                    ->schema([
                        Infolists\Components\TextEntry::make('notes')
                            ->formatStateUsing(fn ($state) => __($state))
                            ->hiddenLabel()

                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageOrders::route('/'),
        ];
    }
}
