<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Builder;

class StatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        return Cache::remember('stats_overview_data', now()->addMinutes(5), function () {
        // Helper to get trend data for the last 10 days
        $getTrend = function ($model) {
            /** @var Builder $query */
            $query = $model::query()->where('created_at', '>=', now()->subDays(10));

            $data = $query->selectRaw('date(created_at) as date, count(*) as count')
                ->groupBy('date')
                ->pluck('count', 'date')
                ->toArray();

            return collect(range(9, 0))
                ->map(fn ($days) => $data[now()->subDays($days)->format('Y-m-d')] ?? 0)
                ->toArray();
        };

        $userCounts = $getTrend(User::class);
        $orderCounts = $getTrend(Order::class);

        // Calculate Revenue Trend (Last 10 days vs previous 10 days)
        /** @var Builder $query */
        $query = Order::where('payment_status', 'paid');

        $revenueData = $query->where('created_at', '>=', now()->subDays(10))
            ->selectRaw('date(created_at) as date, sum(total_price) as sum')
            ->groupBy('date')
            ->pluck('sum', 'date')
            ->toArray();

        $revenueCounts = collect(range(9, 0))
            ->map(fn ($days) => (float) ($revenueData[now()->subDays($days)->format('Y-m-d')] ?? 0))
            ->toArray();

        /** @var Builder $revenueQuery */
        $revenueQuery = Order::where('payment_status', 'paid');
        $totalRevenue = $revenueQuery->sum('total_price');

        /** @var Builder $monthRevenueQuery */
        $monthRevenueQuery = Order::where('payment_status', 'paid');
        $thisMonthRevenue = $monthRevenueQuery->where('created_at', '>=', now()->startOfMonth())
            ->sum('total_price');

        // Growth indicators
        /** @var Builder $newUserQuery */
        $newUserQuery = User::where('created_at', '>=', now()->subDays(7));
        $newUserCount = $newUserQuery->count();

        /** @var Builder $newOrderQuery */
        $newOrderQuery = Order::where('created_at', '>=', now()->subDays(7));
        $newOrderCount = $newOrderQuery->count();

        return [
            Stat::make(__('Total Pengguna'), (string) User::query()->count())
                ->description($newUserCount.' '.__('baru minggu ini'))
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->chart($userCounts)
                ->color('success')
                ->extraAttributes([
                    'class' => 'cursor-pointer hover:shadow-lg transition-all duration-300',
                    'onclick' => "window.location.href='" . route('filament.admin.resources.users.index') . "'",
                ]),

            Stat::make(__('Total Pesanan'), (string) Order::query()->count())
                ->description($newOrderCount.' '.__('baru minggu ini'))
                ->descriptionIcon('heroicon-m-shopping-bag')
                ->chart($orderCounts)
                ->color('info')
                ->extraAttributes([
                    'class' => 'cursor-pointer hover:shadow-lg transition-all duration-300',
                    'onclick' => "window.location.href='" . route('filament.admin.resources.orders.index') . "'",
                ]),

            Stat::make(__('Total Pendapatan'), 'IDR '.number_format($totalRevenue, 0, ',', '.'))
                ->description('IDR '.number_format($thisMonthRevenue, 0, ',', '.').' '.__('bulan ini'))
                ->descriptionIcon($thisMonthRevenue > 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-banknotes')
                ->chart($revenueCounts)
                ->color('success')
                ->extraAttributes([
                    'class' => 'cursor-pointer hover:shadow-lg transition-all duration-300',
                    'onclick' => "window.location.href='" . route('filament.admin.resources.transactions.index') . "'",
                ]),
        ];
        });
    }
}
