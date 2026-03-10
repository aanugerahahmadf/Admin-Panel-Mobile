<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentMethodResource\Pages;
use App\Models\PaymentMethod;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class PaymentMethodResource extends Resource
{
    protected static ?string $model = PaymentMethod::class;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';

    protected static ?string $navigationLabel = 'Metode Pembayaran';

    protected static ?string $navigationGroup = 'Transactions';

    protected static ?int $navigationSort = 5;

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
        return 'Total Metode Pembayaran Aktif';
    }

    public static function form(\Filament\Forms\Form $form): \Filament\Forms\Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make(3)
                    ->schema([
                        Forms\Components\Section::make('Konfigurasi Dasar')
                            ->description('Tentukan identitas dan tipe metode pembayaran ini.')
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label('Nama Metode')
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder('e.g., Bank BCA, GoPay, OVO')
                                    ->prefixIcon('heroicon-m-identification')
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn (string $operation, $state, Forms\Set $set) => $operation === 'create' ? $set('code', Str::slug($state)) : null),

                                Forms\Components\TextInput::make('code')
                                    ->label('Kode Unik')
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(255)
                                    ->placeholder('e.g., bca, gopay')
                                    ->prefixIcon('heroicon-m-key'),

                                Forms\Components\ToggleButtons::make('type')
                                    ->label('Tipe Pembayaran')
                                    ->options([
                                        'bank_transfer' => 'Transfer Bank',
                                        'ewallet' => 'E-Wallet',
                                        'qris' => 'QRIS',
                                        'cod' => 'Cash On Delivery (COD)',
                                        'wallet' => 'Saldo Dompet (Topup)',
                                    ])
                                    ->icons([
                                        'bank_transfer' => 'heroicon-m-building-library',
                                        'ewallet' => 'heroicon-m-wallet',
                                        'qris' => 'heroicon-m-qr-code',
                                        'cod' => 'heroicon-m-banknotes',
                                        'wallet' => 'heroicon-m-credit-card',
                                    ])
                                    ->colors([
                                        'bank_transfer' => 'info',
                                        'ewallet' => 'warning',
                                        'qris' => 'success',
                                        'cod' => 'gray',
                                        'wallet' => 'primary',
                                    ])
                                    ->default('bank_transfer')
                                    ->required()
                                    ->inline()
                                    ->live(),
                            ])
                            ->columnSpan(2),

                        Forms\Components\Section::make('Status & Biaya')
                            ->description('Atur status aktif dan biaya admin.')
                            ->schema([
                                Forms\Components\Toggle::make('is_active')
                                    ->label('Status Aktif')
                                    ->default(true)
                                    ->helperText('Hanya metode aktif yang muncul di aplikasi.')
                                    ->onColor('success')
                                    ->offColor('danger'),

                                Forms\Components\TextInput::make('fee')
                                    ->label('Biaya Admin')
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->default(0)
                                    ->placeholder('0'),
                            ])
                            ->columnSpan(1),
                    ]),

                Forms\Components\Section::make('Detail Konten & Instruksi')
                    ->description('Lengkapi informasi rekening atau gambar QRIS.')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('account_number')
                                    ->label(fn (Forms\Get $get) => $get('type') === 'ewallet' ? 'Nomor E-Wallet / HP' : 'Nomor Rekening')
                                    ->required(fn (Forms\Get $get) => in_array($get('type'), ['bank_transfer', 'ewallet']))
                                    ->placeholder('Masukkan nomor rekening atau HP...')
                                    ->visible(fn (Forms\Get $get) => in_array($get('type'), ['bank_transfer', 'ewallet']))
                                    ->prefixIcon('heroicon-m-hashtag'),

                                Forms\Components\TextInput::make('account_holder')
                                    ->label('Nama Pemilik Rekening')
                                    ->required(fn (Forms\Get $get) => $get('type') === 'bank_transfer')
                                    ->placeholder('e.g., PT Devi Make Up Wedding Organizer')
                                    ->visible(fn (Forms\Get $get) => $get('type') === 'bank_transfer')
                                    ->prefixIcon('heroicon-m-user'),
                            ]),

                        Forms\Components\FileUpload::make('qris_image')
                            ->label('Upload Gambar QRIS')
                            ->image()
                            ->directory('payment-qris')
                            ->visibility('public')
                            ->imageEditor()
                            ->visible(fn (Forms\Get $get) => $get('type') === 'qris')
                            ->required(fn (Forms\Get $get) => $get('type') === 'qris')
                            ->columnSpanFull(),

                        Forms\Components\FileUpload::make('icon')
                            ->label('Ikon / Logo')
                            ->image()
                            ->directory('payment-icons')
                            ->visibility('public')
                            ->maxSize(102400000) // 100GB
                            ->columnSpanFull(),

                        Forms\Components\RichEditor::make('instructions')
                            ->label('Instruksi Pembayaran')
                            ->placeholder('Tuliskan langkah-langkah pembayaran di sini...')
                            ->columnSpanFull(),
                    ])
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->mobileCards()
            ->mobileCardFeatured('name', 'blue')
            ->columns([
                Tables\Columns\ImageColumn::make('icon')
                    ->label('Logo')
                    ->circular(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Metode')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('type')
                    ->label('Tipe')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'bank_transfer' => 'Bank',
                        'ewallet' => 'E-Wallet',
                        'qris' => 'QRIS',
                        'cod' => 'COD',
                        'wallet' => 'Saldo',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'bank_transfer' => 'info',
                        'ewallet' => 'warning',
                        'qris' => 'success',
                        'cod' => 'gray',
                        'wallet' => 'primary',
                        default => 'gray',
                    })
                    ->alignment('center'),
                Tables\Columns\TextColumn::make('account_number')
                    ->label('Nomor Rekening/HP')
                    ->searchable()
                    ->copyable()
                    ->alignment('center'),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean()
                    ->alignment('center'),
                Tables\Columns\TextColumn::make('fee')
                    ->label('Admin Fee')
                    ->money('IDR')
                    ->sortable()
                    ->alignment('right'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'bank_transfer' => 'Bank',
                        'ewallet' => 'E-Wallet',
                        'qris' => 'QRIS',
                        'cod' => 'COD',
                        'wallet' => 'Saldo',
                    ]),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status Aktif'),
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
                            ->title('Metode Diperbarui')
                            ->body('Metode pembayaran telah berhasil diperbarui.')
                    ),
                Tables\Actions\DeleteAction::make()
                    ->button()
                    ->color('danger')
                    ->size('lg')
                    ->successNotification(
                        Notification::make()
                            ->success()
                            ->title('Metode Dihapus')
                            ->body('Metode pembayaran telah berhasil dihapus.')
                    ),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\ExportBulkAction::make()
                        ->exporter(\App\Filament\Exports\PaymentMethodExporter::class)
                        ->label('Ekspor yang Dipilih')
                        ->icon('heroicon-o-arrow-down-tray'),
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManagePaymentMethods::route('/'),
        ];
    }
}
