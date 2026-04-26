<?php

namespace App\Filament\User\Widgets;

use App\Models\Transaction;
use Filament\Facades\Filament;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class UnifiedHistoryWidget extends BaseWidget
{
    protected static ?int $sort = 10;

    protected int|string|array $columnSpan = 'full';

    protected function getTableHeading(): string|Htmlable|null
    {
        return __('Riwayat Aktivitas Terakhir');
    }

    public function table(Table $table): Table
    {
        $userId = Filament::auth()->id();

        $unified = Transaction::where('user_id', $userId)
            ->select(['id', 'total_amount as amount', 'reference_number as ref', 'status', 'created_at', 'type']);

        if (Schema::hasTable('withdrawals')) {
            $withdrawals = DB::table('withdrawals')
                ->select([DB::raw('id + 1000000 as id'), 'amount', 'reference_number as ref', 'status', 'created_at', DB::raw("'withdrawal' as type")])
                ->where('user_id', $userId);
            $unified = $unified->unionAll($withdrawals);
        }

        return $table
            ->query(
                Transaction::query()
                    ->fromSub($unified, 'unified_history')
                    ->orderByDesc('created_at')
            )
            ->columns([
                Tables\Columns\TextColumn::make('ref')
                    ->label(__('ID Transaksi'))
                    ->weight('bold')
                    ->color('gray')
                    ->icon(fn ($record) => match ($record->type) {
                        'topup' => 'heroicon-m-arrow-down-left',
                        'withdrawal' => 'heroicon-m-arrow-up-right',
                        'order' => 'heroicon-m-shopping-bag',
                        default => 'heroicon-m-ticket',
                    })
                    ->iconColor(fn ($record) => match ($record->type) {
                        'topup' => 'success',
                        'withdrawal' => 'danger',
                        'order' => 'info',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('type')
                    ->label(__('Tipe'))
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'topup' => __('Deposit'),
                        'withdrawal' => __('Tarik'),
                        'order' => __('Beli'),
                        default => $state,
                    })
                    ->color(fn ($state) => match ($state) {
                        'topup' => 'success',
                        'withdrawal' => 'danger',
                        'order' => 'info',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('amount')
                    ->label(__('Nominal'))
                    ->formatStateUsing(fn ($state, $record) => ($record->type === 'topup' ? '+' : '-').' Rp '.number_format($state, 0, ',', '.'))
                    ->color(fn ($record) => $record->type === 'topup' ? 'success' : 'danger')
                    ->weight('black'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(function ($state) {
                        $val = $state instanceof \BackedEnum ? $state->value : (string) $state;

                        return match ($val) {
                            'pending' => __('Proses'),
                            'success', 'completed', 'approved', 'paid' => __('Selesai'),
                            'failed', 'rejected', 'cancelled' => __('Gagal'),
                            default => ucfirst($val),
                        };
                    })
                    ->color(function ($state) {
                        $val = $state instanceof \BackedEnum ? $state->value : (string) $state;

                        return match ($val) {
                            'pending' => 'warning',
                            'success', 'completed' => 'success',
                            'failed', 'rejected' => 'danger',
                            default => 'gray',
                        };
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('Waktu'))
                    ->dateTime('d/m/y H:i')
                    ->color('gray')
                    ->size('xs'),
            ])
            ->actions([
                Tables\Actions\Action::make('details')
                    ->label(__('Lihat'))
                    ->icon('heroicon-m-eye')
                    ->button()
                    ->size('xs')
                    ->color('gray')
                    ->url(function ($record) {
                        $actualId = $record->id;
                        if ($record->type === 'withdrawal') {
                            $actualId -= 1000000;
                        }

                        return match ($record->type) {
                            'topup', 'order' => route('filament.user.resources.transactions.index', ['tableFilters[id][value]' => $actualId]),
                            'withdrawal' => route('filament.user.resources.transactions.index'), // Fallback if no specific withdrawal resource
                            default => '#',
                        };
                    }),
            ]);
    }
}
