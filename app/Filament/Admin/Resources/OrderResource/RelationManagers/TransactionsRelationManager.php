<?php

namespace App\Filament\Admin\Resources\OrderResource\RelationManagers;

use App\Models\Transaction;
use App\Services\MidtransService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class TransactionsRelationManager extends RelationManager
{
    protected static string $relationship = 'transactions';

    protected static ?string $recordTitleAttribute = 'reference_number';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('Riwayat Transaksi');
    }

    public static function getModelLabel(): string
    {
        return __('Transaksi');
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('reference_number')
                    ->label(__('Nomor Referensi'))
                    ->readOnly(),
                Forms\Components\TextInput::make('amount')
                    ->label(__('Jumlah'))
                    ->numeric()
                    ->prefix('Rp')
                    ->readOnly(),
                Forms\Components\Select::make('status')
                    ->label(__('Status'))
                    ->options([
                        'pending' => __('Tertunda'),
                        'success' => __('Berhasil'),
                        'failed' => __('Gagal'),
                        'expired' => __('Kedaluwarsa'),
                        'cancelled' => __('Dibatalkan'),
                    ])
                    ->required(),
                Forms\Components\TextInput::make('payment_method')
                    ->label(__('Metode Pembayaran'))
                    ->readOnly(),
                Forms\Components\DateTimePicker::make('paid_at')
                    ->label(__('Waktu Pembayaran'))
                    ->readOnly(),
                Forms\Components\Textarea::make('notes')
                    ->label(__('Catatan / Log'))
                    ->columnSpanFull()
                    ->readOnly(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('reference_number')
                    ->label(__('Ref'))
                    ->searchable()
                    ->copyable(),
                Tables\Columns\TextColumn::make('amount')
                    ->label(__('Jumlah'))
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('payment_method')
                    ->label(__('Metode'))
                    ->formatStateUsing(fn ($state) => strtoupper(str_replace('_', ' ', $state ?? '-'))),
                Tables\Columns\TextColumn::make('status')
                    ->label(__('Status'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'success' => 'success',
                        'failed' => 'danger',
                        'expired' => 'danger',
                        'cancelled' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'pending' => __('Tertunda'),
                        'success' => __('Berhasil'),
                        'failed' => __('Gagal'),
                        'expired' => __('Kedaluwarsa'),
                        'cancelled' => __('Dibatalkan'),
                        default => ucfirst($state),
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('Dibuat'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => __('Tertunda'),
                        'success' => __('Berhasil'),
                        'failed' => __('Gagal'),
                        'expired' => __('Kedaluwarsa'),
                        'cancelled' => __('Dibatalkan'),
                    ]),
            ])
            ->headerActions([
                // No manual creation, only via checkout
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\Action::make('check_midtrans_status')
                    ->label(__('Cek Status Midtrans'))
                    ->icon('heroicon-o-arrow-path')
                    ->color('info')
                    ->action(function (Transaction $record) {
                        try {
                            $midtrans = new MidtransService;
                            $status = $midtrans->getTransactionStatus($record->reference_number);

                            if ($status) {
                                // Status will be automatically updated in markAsSuccess/markAsFailed if changed
                                Notification::make()
                                    ->success()
                                    ->title(__('Status Diperbarui'))
                                    ->body(__('Status transaksi saat ini: ').$record->status)
                                    ->send();
                            } else {
                                Notification::make()
                                    ->warning()
                                    ->title(__('Status Tidak Berubah'))
                                    ->body(__('Tidak ada pembaruan status dari Midtrans.'))
                                    ->send();
                            }
                        } catch (\Exception $e) {
                            Notification::make()
                                ->danger()
                                ->title(__('Gagal Mengecek Status'))
                                ->body($e->getMessage())
                                ->send();
                        }
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
