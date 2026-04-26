<?php

namespace App\Filament\User\Resources\OrderResource\Pages;

use App\Filament\User\Resources\OrderResource;
use Filament\Resources\Pages\ManageRecords;

class ManageOrders extends ManageRecords
{
    protected static string $resource = OrderResource::class;

    public function getTabs(): array
    {
        return [
            'all' => \Filament\Resources\Components\Tab::make(__('Semua'))
                ->badge(fn() => \App\Models\Order::where('user_id', \Filament\Facades\Filament::auth()->id())->count()),
            'pending' => \Filament\Resources\Components\Tab::make(\App\Enums\OrderStatus::PENDING->getLabel())
                ->icon(\App\Enums\OrderStatus::PENDING->getIcon())
                ->modifyQueryUsing(fn ($query) => $query->where('status', \App\Enums\OrderStatus::PENDING))
                ->badge(fn() => \App\Models\Order::where('user_id', \Filament\Facades\Filament::auth()->id())->where('status', \App\Enums\OrderStatus::PENDING)->count())
                ->badgeColor(\App\Enums\OrderStatus::PENDING->getColor()),
            'confirmed' => \Filament\Resources\Components\Tab::make(\App\Enums\OrderStatus::CONFIRMED->getLabel())
                ->icon(\App\Enums\OrderStatus::CONFIRMED->getIcon())
                ->modifyQueryUsing(fn ($query) => $query->where('status', \App\Enums\OrderStatus::CONFIRMED))
                ->badge(fn() => \App\Models\Order::where('user_id', \Filament\Facades\Filament::auth()->id())->where('status', \App\Enums\OrderStatus::CONFIRMED)->count())
                ->badgeColor(\App\Enums\OrderStatus::CONFIRMED->getColor()),
            'completed' => \Filament\Resources\Components\Tab::make(\App\Enums\OrderStatus::COMPLETED->getLabel())
                ->icon(\App\Enums\OrderStatus::COMPLETED->getIcon())
                ->modifyQueryUsing(fn ($query) => $query->where('status', \App\Enums\OrderStatus::COMPLETED))
                ->badge(fn() => \App\Models\Order::where('user_id', \Filament\Facades\Filament::auth()->id())->where('status', \App\Enums\OrderStatus::COMPLETED)->count())
                ->badgeColor(\App\Enums\OrderStatus::COMPLETED->getColor()),
            'cancelled' => \Filament\Resources\Components\Tab::make(\App\Enums\OrderStatus::CANCELLED->getLabel())
                ->icon(\App\Enums\OrderStatus::CANCELLED->getIcon())
                ->modifyQueryUsing(fn ($query) => $query->where('status', \App\Enums\OrderStatus::CANCELLED))
                ->badge(fn() => \App\Models\Order::where('user_id', \Filament\Facades\Filament::auth()->id())->where('status', \App\Enums\OrderStatus::CANCELLED)->count())
                ->badgeColor(\App\Enums\OrderStatus::CANCELLED->getColor()),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            // No creation action for user
        ];
    }
}
