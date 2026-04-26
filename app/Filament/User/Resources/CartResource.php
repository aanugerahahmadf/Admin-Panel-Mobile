<?php

namespace App\Filament\User\Resources;

use App\Filament\User\Resources\CartResource\Pages;
use App\Models\Cart;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;
use Livewire\Component;

class CartResource extends Resource
{
    protected static ?string $model = Cart::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';

    public static function getNavigationLabel(): string
    {
        return __('Keranjang');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Keranjang');
    }

    public static function getModelLabel(): string
    {
        return __('Keranjang');
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['product.name', 'package.name'];
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

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('quantity')
                    ->numeric()
                    ->required()
                    ->minValue(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->description(new HtmlString('<style>.fi-ta-ctn, .fi-ta-content, .fi-ta-header-toolbar, .fi-ta-pagination { background-color: transparent !important; box-shadow: none !important; border-color: transparent !important; }</style>'))
            ->emptyStateHeading(__('Keranjang Kosong'))
            ->emptyStateDescription(__('Mulai belanja dan temukan dekorasi impian Anda sekarang!'))
            ->emptyStateIcon('heroicon-o-shopping-cart')
            ->emptyStateActions([
                Tables\Actions\Action::make('explore')
                    ->label(__('Mulai Belanja'))
                    ->url(ProductResource::getUrl())
                    ->button()
                    ->color('primary')
                    ->size('lg'),
            ])
            ->columns([
                Tables\Columns\ImageColumn::make('item.image_url')
                    ->label(__('Foto'))
                    ->circular(),
                Tables\Columns\TextColumn::make('item.name')
                    ->label(__('Nama Item'))
                    ->searchable()
                    ->sortable()
                    ->description(fn (Cart $record): string => $record->product_id ? __('Produk') : __('Paket')),
                Tables\Columns\TextColumn::make('quantity')
                    ->label(__('Jumlah'))
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('subtotal')
                    ->label(__('Subtotal'))
                    ->money('IDR')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\DeleteAction::make()
                    ->label(__('Hapus'))
                    ->icon('heroicon-o-trash')
                    ->button()
                    ->color('danger')
                    ->size('sm')
                    ->extraAttributes(['class' => 'flex-1 justify-center rounded-lg shadow-sm font-bold']),
                Tables\Actions\Action::make('checkout')
                    ->label(__('Beli'))
                    ->button()
                    ->color('primary')
                    ->size('sm')
                    ->icon('heroicon-m-shopping-cart')
                    ->extraAttributes(['class' => 'flex-1 justify-center rounded-lg shadow-sm font-bold'])
                    ->slideOver()
                    ->modalHeading(fn (Cart $record) => $record->product_id ? __('Checkout Produk') : __('Checkout Layanan'))
                    ->steps(fn (Cart $record) => $record->product_id
                        ? ProductResource::getCheckoutWizardSteps($record->product)
                        : PackageResource::getCheckoutWizardSteps($record->package)
                    )
                    ->action(function (Cart $record, array $data, Component $livewire) {
                        if ($record->product_id) {
                            $response = ProductResource::handleCheckout($record->product, $data, $livewire);
                        } else {
                            $response = PackageResource::handleCheckout($record->package, $data, $livewire);
                        }

                        // Remove from cart after successful checkout
                        $record->delete();

                        return $response;
                    }),
            ])
            ->actionsAlignment('center')
            ->extraAttributes([
                'class' => 'filament-table-actions-container !flex !flex-row !gap-1 !p-3 !bg-gray-50/50 dark:!bg-white/5 !border-t dark:!border-gray-800',
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->headerActions([
                Tables\Actions\Action::make('checkout_all')
                    ->label(__('Lanjut ke Pembayaran'))
                    ->color('success')
                    ->icon('heroicon-m-credit-card')
                    ->action(function () {
                        // For now, just a placeholder or logic to convert cart to order
                        Notification::make()
                            ->title(__('Fitur Checkout Massal Sedang Dikembangkan'))
                            ->info()
                            ->send();
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageCarts::route('/'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('user_id', auth()->id());
    }
}
