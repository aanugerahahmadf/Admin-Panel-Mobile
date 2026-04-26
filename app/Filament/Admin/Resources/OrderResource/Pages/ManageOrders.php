<?php

namespace App\Filament\Admin\Resources\OrderResource\Pages;

use App\Filament\Admin\Exports\OrderExporter;
use App\Filament\Admin\Resources\OrderResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ManageRecords;

/**
 * @property-read \App\Filament\Resources\OrderResource $resource
 */
class ManageOrders extends ManageRecords

{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ExportAction::make()
                ->exporter(OrderExporter::class)
                ->label(__('Ekspor Data'))
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success'),
            Actions\CreateAction::make()
                ->label(__('Tambah Order'))
                ->icon('heroicon-o-plus')
                ->successNotification(
                    Notification::make()
                        ->success()
                        ->title(__('Order Ditambahkan'))
                        ->body(__('Order baru telah berhasil ditambahkan.'))
                ),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => \Filament\Resources\Components\Tab::make(__('Semua'))
                ->badge(fn() => \App\Models\Order::count()),
            'pending' => \Filament\Resources\Components\Tab::make(\App\Enums\OrderStatus::PENDING->getLabel())
                ->icon(\App\Enums\OrderStatus::PENDING->getIcon())
                ->modifyQueryUsing(fn ($query) => $query->where('status', \App\Enums\OrderStatus::PENDING))
                ->badge(fn() => \App\Models\Order::where('status', \App\Enums\OrderStatus::PENDING)->count())
                ->badgeColor(\App\Enums\OrderStatus::PENDING->getColor()),
            'confirmed' => \Filament\Resources\Components\Tab::make(\App\Enums\OrderStatus::CONFIRMED->getLabel())
                ->icon(\App\Enums\OrderStatus::CONFIRMED->getIcon())
                ->modifyQueryUsing(fn ($query) => $query->where('status', \App\Enums\OrderStatus::CONFIRMED))
                ->badge(fn() => \App\Models\Order::where('status', \App\Enums\OrderStatus::CONFIRMED)->count())
                ->badgeColor(\App\Enums\OrderStatus::CONFIRMED->getColor()),
            'completed' => \Filament\Resources\Components\Tab::make(\App\Enums\OrderStatus::COMPLETED->getLabel())
                ->icon(\App\Enums\OrderStatus::COMPLETED->getIcon())
                ->modifyQueryUsing(fn ($query) => $query->where('status', \App\Enums\OrderStatus::COMPLETED))
                ->badge(fn() => \App\Models\Order::where('status', \App\Enums\OrderStatus::COMPLETED)->count())
                ->badgeColor(\App\Enums\OrderStatus::COMPLETED->getColor()),
            'cancelled' => \Filament\Resources\Components\Tab::make(\App\Enums\OrderStatus::CANCELLED->getLabel())
                ->icon(\App\Enums\OrderStatus::CANCELLED->getIcon())
                ->modifyQueryUsing(fn ($query) => $query->where('status', \App\Enums\OrderStatus::CANCELLED))
                ->badge(fn() => \App\Models\Order::where('status', \App\Enums\OrderStatus::CANCELLED)->count())
                ->badgeColor(\App\Enums\OrderStatus::CANCELLED->getColor()),
        ];
    }
}
