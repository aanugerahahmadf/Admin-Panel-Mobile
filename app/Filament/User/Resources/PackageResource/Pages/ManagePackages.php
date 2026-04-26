<?php

namespace App\Filament\User\Resources\PackageResource\Pages;

use App\Filament\User\Resources\PackageResource;
use Filament\Resources\Pages\ManageRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ManagePackages extends ManageRecords
{
    protected static string $resource = PackageResource::class;

    public function getTabs(): array
    {
        $cbirCount = session()->has('cbir_package_results_ids') ? count(session('cbir_package_results_ids')) : null;

        return [
            'all' => Tab::make(__('Semua Layanan'))
                ->icon('heroicon-m-squares-2x2')
                ->badge(fn() => $cbirCount ?? \App\Models\Package::count())
                ->badgeColor($cbirCount ? 'primary' : 'gray'),
            'wishlist' => Tab::make(__('Favorit Saya'))
                ->icon('heroicon-m-heart')
                ->badge(fn() => \App\Models\Package::whereHas('wishlists', fn ($q) => $q->where('user_id', \Filament\Facades\Filament::auth()->id()))->count())
                ->badgeColor('danger')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereHas('wishlists', fn ($q) => $q->where('user_id', \Filament\Facades\Filament::auth()->id()))),
            'orders' => Tab::make(__('Pesanan Saya'))
                ->icon('heroicon-m-shopping-bag')
                ->badge(fn() => \App\Models\Package::whereHas('orders', fn ($q) => $q->where('user_id', \Filament\Facades\Filament::auth()->id()))->count())
                ->badgeColor('info')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereHas('orders', fn ($q) => $q->where('user_id', \Filament\Facades\Filament::auth()->id()))),
            'payments' => Tab::make(__('Konfirmasi Bayar'))
                ->icon('heroicon-m-credit-card')
                ->badge(fn() => \App\Models\Package::whereHas('orders', fn ($q) => $q->where('user_id', \Filament\Facades\Filament::auth()->id())->whereIn('status', [\App\Enums\OrderStatus::PENDING]))->count())
                ->badgeColor('warning')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereHas('orders', fn ($q) => $q->where('user_id', \Filament\Facades\Filament::auth()->id())->whereIn('status', [\App\Enums\OrderStatus::PENDING]))),
            'history' => Tab::make(__('Riwayat'))
                ->icon('heroicon-m-clock')
                ->badge(fn() => \App\Models\Package::whereHas('orders', fn ($q) => $q->where('user_id', \Filament\Facades\Filament::auth()->id())->whereIn('status', [\App\Enums\OrderStatus::COMPLETED, \App\Enums\OrderStatus::CANCELLED]))->count())
                ->badgeColor('gray')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereHas('orders', fn ($q) => $q->where('user_id', \Filament\Facades\Filament::auth()->id())->whereIn('status', [\App\Enums\OrderStatus::COMPLETED, \App\Enums\OrderStatus::CANCELLED]))),
        ];
    }

    protected function modifyQueryUsing(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        // Handle direct ID from preview link
        if ($id = request()->query('cbir_id')) {
            session()->put('cbir_package_results_ids', [(int)$id]);
        }

        if ($ids = session()->get('cbir_package_results_ids')) {
            return $query->whereIn('id', $ids)
                ->orderByRaw('FIELD(id, ' . implode(',', $ids) . ')');
        }
        return $query;
    }

    public function bookNow($id)
    {
        // Set the session filter to only this package
        session()->put('cbir_package_results_ids', [(int)$id]);
        
        \Filament\Notifications\Notification::make()
            ->title(__('Menuju halaman pemesanan...'))
            ->success()
            ->send();

        return redirect()->to(PackageResource::getUrl('index') . "?tableAction=buy_now&tableActionRecord={$id}");
    }

    public function toggleWishlist($id)
    {
        $user = \Filament\Facades\Filament::auth()->user();
        $wishlist = \App\Models\Wishlist::where('user_id', $user->id)
            ->where('package_id', $id)
            ->first();

        if ($wishlist) {
            $wishlist->delete();
            $msg = __('Dihapus dari Favorit');
            \Filament\Notifications\Notification::make()->title($msg)->warning()->send();
        } else {
            \App\Models\Wishlist::create([
                'user_id' => $user->id,
                'package_id' => $id,
            ]);
            $msg = __('Berhasil disimpan ke Favorit!');
            \Filament\Notifications\Notification::make()->title($msg)->success()->icon('heroicon-s-heart')->iconColor('danger')->send();
        }

        // Refresh session results to update heart icon
        $results = session('cbir_mixed_results', []);
        foreach($results as &$res) {
            if (($res['type'] ?? '') === 'package' && ($res['data']['id'] ?? 0) == $id) {
                $res['data']['is_wishlisted'] = !$wishlist;
            }
        }
        session()->put('cbir_mixed_results', $results);
    }

    public function addToCart($id)
    {
        $user = \Filament\Facades\Filament::auth()->user();
        
        \App\Models\Cart::updateOrCreate([
            'user_id' => $user->id,
            'package_id' => $id,
        ], [
            'quantity' => \Illuminate\Support\Facades\DB::raw('quantity + 1')
        ]);
        
        \Filament\Notifications\Notification::make()
            ->title(__('Berhasil masuk keranjang'))
            ->success()
            ->icon('heroicon-o-shopping-cart')
            ->send();
    }

    public function clearVisualSearch()
    {
        session()->forget(['cbir_mixed_results', 'cbir_package_results_ids', 'cbir_search_time']);
        $this->dispatch('refresh_catalog');
    }

    protected function getListeners(): array
    {
        return [
            'refresh_catalog' => '$refresh',
            'book_now' => 'bookNow',
            'toggle_wishlist' => 'toggleWishlist',
            'clear_visual_search' => 'clearVisualSearch',
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            // Removed for table integration
        ];
    }
}
