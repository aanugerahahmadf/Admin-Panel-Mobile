<?php

namespace App\Filament\User\Widgets;

use App\Models\Order;
use App\Models\Wishlist;
use Filament\Facades\Filament;
use Filament\Support\Enums\IconPosition;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;

class StatsOverview extends BaseWidget
{
    protected static ?int $navigationSort = 1;

    protected int|string|array $columnSpan = 'full';

    public function getExtraAttributes(): array
    {
        return [
            'class' => '[&_.fi-wi-stats-overview-stats-ctn]:grid-cols-1 [&_.fi-wi-stats-overview-stats-ctn]:md:grid-cols-2 [&_.fi-wi-stats-overview-stats-ctn]:xl:grid-cols-4',
        ];
    }

    protected function getColumns(): int
    {
        return 4;
    }

    protected function getStats(): array
    {
        $user = Auth::user();
        $name = $user->full_name ?? $user->username ?? __('User');

        return [
            Stat::make(new HtmlString(__('Selamat Datang,').'<style>@media (min-width: 1280px) { .fi-wi-stats-overview-stats-ctn { grid-template-columns: repeat(4, minmax(0, 1fr)) !important; } }</style>'), $name)
                ->description(__('Ayo buat momen spesialmu hari ini!'))
                ->descriptionIcon('heroicon-m-sparkles')
                ->color('primary')
                ->extraAttributes([
                    'class' => 'h-full',
                ]),

            Stat::make(__('Pesanan Saya'), Order::where('user_id', $user->id)->count())
                ->description(__('Lacak transaksi Anda'))
                ->descriptionIcon('heroicon-m-shopping-bag', IconPosition::Before)
                ->color('info')
                ->extraAttributes([
                    'class' => 'cursor-pointer hover:scale-105 transition-transform h-full',
                    'onclick' => "window.location.href='".route('filament.user.resources.orders.index')."'",
                ]),

            Stat::make(__('Favorit'), Wishlist::where('user_id', $user->id)->count())
                ->description(__('Layanan tersimpan'))
                ->descriptionIcon('heroicon-m-heart', IconPosition::Before)
                ->color('danger')
                ->extraAttributes([
                    'class' => 'cursor-pointer hover:scale-105 transition-transform h-full',
                    'onclick' => "window.location.href='".route('filament.user.resources.wishlists.index')."'",
                ]),
            Stat::make(__('Voucher Aktif'), Filament::auth()->user()?->vouchers()->whereNull('user_vouchers.used_at')->count() ?? 0)
                ->description(__('Gunakan diskonmu'))
                ->descriptionIcon('heroicon-m-ticket', IconPosition::Before)
                ->color('warning')
                ->extraAttributes([
                    'class' => 'cursor-pointer hover:scale-105 transition-transform h-full',
                    'onclick' => "window.location.href='".route('filament.user.resources.vouchers.index')."'",
                ]),
        ];
    }
}
