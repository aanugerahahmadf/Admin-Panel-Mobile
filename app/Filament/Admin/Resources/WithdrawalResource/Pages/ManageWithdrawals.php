<?php

namespace App\Filament\Admin\Resources\WithdrawalResource\Pages;

use App\Enums\WithdrawalStatus;
use App\Filament\Admin\Exports\WithdrawalExporter;
use App\Filament\Admin\Resources\WithdrawalResource;
use App\Models\Withdrawal;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ManageRecords;
use Illuminate\Database\Eloquent\Builder;

/**
 * @property-read \App\Filament\Resources\WithdrawalResource $resource
 */
class ManageWithdrawals extends ManageRecords
{
    protected static string $resource = WithdrawalResource::class;

    public function getTitle(): string
    {
        return static::$title ?? static::getResource()::getTitleCasePluralModelLabel();
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make(__('Semua'))
                ->icon('heroicon-m-list-bullet')
                ->badge(fn () => Withdrawal::count()),
            'pending' => Tab::make(__('Perlu Persetujuan'))
                ->icon('heroicon-m-clock')
                ->badge(fn () => Withdrawal::where('status', WithdrawalStatus::PENDING)->count())
                ->badgeColor('warning')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', WithdrawalStatus::PENDING)),
            'completed' => Tab::make(__('Tercairkan'))
                ->icon('heroicon-m-check-badge')
                ->badge(fn () => Withdrawal::where('status', WithdrawalStatus::COMPLETED)->count())
                ->badgeColor('success')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', WithdrawalStatus::COMPLETED)),
            'rejected' => Tab::make(__('Ditolak'))
                ->icon('heroicon-m-x-circle')
                ->badge(fn () => Withdrawal::where('status', WithdrawalStatus::REJECTED)->count())
                ->badgeColor('danger')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', WithdrawalStatus::REJECTED)),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\ExportAction::make()
                ->exporter(WithdrawalExporter::class)
                ->label(__('Ekspor Data'))
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success'),
            Actions\CreateAction::make()
                ->label(__('Tambah Penarikan'))
                ->icon('heroicon-o-plus')
                ->successNotification(
                    Notification::make()
                        ->success()
                        ->title(__('Penarikan Ditambahkan'))
                        ->body(__('Data penarikan baru telah berhasil ditambahkan.'))
                ),
        ];
    }
}
