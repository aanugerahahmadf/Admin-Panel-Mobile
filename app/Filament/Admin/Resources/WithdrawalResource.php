<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\WithdrawalResource\Pages;
use App\Models\Withdrawal;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

// Added for Str::random

/**
 * @mixin \Eloquent
 * @property-read \App\Models\Withdrawal $record
 */
class WithdrawalResource extends Resource
{
    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $model = Withdrawal::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?int $navigationSort = 4;

    public static function getModelLabel(): string
    {
        return __('Penarikan Saldo');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Penarikan Saldo');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('Transaksi');
    }

    public static function getNavigationLabel(): string
    {
        return __('Tarik Saldo');
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
        return __('Total Permintaan Penarikan');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make(__('Informasi Penarikan'))
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->label(__('Pengguna'))
                            ->relationship('user', 'full_name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\TextInput::make('reference_number')
                            ->label(__('Nomor Referensi'))
                            ->default('WD-'.strtoupper(Str::random(10)))
                            ->required()
                            ->readOnly(),
                        Forms\Components\TextInput::make('amount')
                            ->label(__('Jumlah'))
                            ->required()
                            ->numeric()
                            ->prefix('Rp')
                            ->readOnly(fn ($record) => $record !== null),
                        Forms\Components\Select::make('status')
                            ->searchable()
                            ->label(__('Status'))
                            ->options(\App\Enums\WithdrawalStatus::class)
                            ->required()
                            ->default(\App\Enums\WithdrawalStatus::PENDING),
                    ])->columns(['sm' => 2]),

                Forms\Components\Section::make(__('Tujuan Transfer'))
                    ->schema([
                        Forms\Components\Select::make('bank_id')
                            ->label(__('Nama Bank / E-Wallet'))
                            ->relationship('bank', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->native(false)
                            ->prefixIcon('heroicon-o-building-library')
                            ->live()
                            ->afterStateUpdated(fn ($state, Forms\Set $set) => $set('bank_name', \App\Models\Bank::find($state)?->name)),
                        
                        Forms\Components\Hidden::make('bank_name'),
                        Forms\Components\TextInput::make('account_number')
                            ->label(__('Nomor Rekening'))
                            ->required(),
                        Forms\Components\TextInput::make('account_holder')
                            ->label(__('Nama Pemilik Rekening'))
                            ->required(),
                    ])->columns(['sm' => 3]),

                Forms\Components\Section::make(__('Catatan'))
                    ->schema([
                        Forms\Components\Textarea::make('notes')
                            ->label(__('Catatan User'))
                            ->readOnly(),
                        Forms\Components\Textarea::make('admin_notes')
                            ->label(__('Catatan Admin')),
                    ])->columns(['sm' => 2]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.full_name')
                    ->label(__('Pelanggan'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('reference_number')
                    ->label(__('Ref'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('amount')
                    ->label(__('Jumlah'))
                    ->money('IDR'),
                Tables\Columns\TextColumn::make('bank_name')
                    ->label(__('Bank'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('account_number')
                    ->label(__('Rekening')),
                Tables\Columns\TextColumn::make('account_holder')
                    ->label(__('Pemilik Rekening'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('Tgl Pengajuan'))
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label(__('Status'))
                    ->options([
                        'pending' => __('Tertunda'),
                        'approved' => __('Disetujui'),
                        'completed' => __('Selesai'),
                        'rejected' => __('Ditolak'),
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('approve')
                    ->label(__('Setujui'))
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (Withdrawal $record) => $record->status === 'pending')
                    ->action(function (Withdrawal $record): void {
                        $record->update(['status' => 'approved']);
                    }),
                Tables\Actions\Action::make('complete')
                    ->label(__('Selesai'))
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (Withdrawal $record) => $record->status === 'approved')
                    ->action(function (Withdrawal $record): void {
                        $record->update(['status' => 'completed']);
                    }),
                Tables\Actions\Action::make('reject')
                    ->label(__('Tolak'))
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (Withdrawal $record) => $record->status === 'pending')
                    ->form([
                        Forms\Components\Textarea::make('admin_notes')
                            ->label(__('Alasan Penolakan'))
                            ->required(),
                    ])
                    ->action(function (Withdrawal $record, array $data): void {
                        // Return balance to user
                        $record->user->increment('balance', $record->amount);
                        $record->update([
                            'status' => 'rejected',
                            'admin_notes' => $data['admin_notes'],
                        ]);
                    }),
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
                            ->title(__('Penarikan diperbarui'))
                            ->body(__('Penarikan telah berhasil diperbarui.'))
                    ),
                Tables\Actions\DeleteAction::make()
                    ->button()
                    ->color('danger')
                    ->size('lg')
                    ->successNotification(
                        Notification::make()
                            ->success()
                            ->title(__('Penarikan dihapus'))
                            ->body(__('Penarikan telah berhasil dihapus.'))
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
            'index' => Pages\ManageWithdrawals::route('/'),
        ];
    }
}
