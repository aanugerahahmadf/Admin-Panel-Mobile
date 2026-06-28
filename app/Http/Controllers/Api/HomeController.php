<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Package;
use App\Models\User;
use App\Models\Voucher;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class HomeController extends Controller
{
    public function index()
    {
        try {
            /** @var User|null $user */
            $user = Auth::user();

            $categories = Category::query()->get(['*']);

            $featuredPackages = Package::with(['media', 'category'])
                ->where('is_featured', true)
                ->limit(6)
                ->get(['*']);

            $vouchers = Voucher::where('is_active', true)->where('expires_at', '>', now())->limit(5)->get(['*']);
            $flashSale = Package::with(['media'])
                ->whereNotNull('discount_price')
                ->limit(5)
                ->get(['*']);

            $upcomingBookings = [];
            $unreadNotifications = 0;
            $unreadMessages = 0;

            if ($user instanceof User) {
                $upcomingBookings = $user->orders()
                    ->with(['package.media'])
                    ->latest()
                    ->limit(5)
                    ->get(['*'])
                    ->map(fn ($o) => $this->formatOrder($o));

                $unreadNotifications = $user->unreadNotifications->count();
                $unreadMessages = DB::table('fm_messages')
                    ->join('fm_inboxes', 'fm_messages.inbox_id', '=', 'fm_inboxes.id')
                    ->where('fm_inboxes.user_ids', 'like', "%\"{$user->id}\"%")
                    ->whereNull('fm_messages.read_at')
                    ->where('fm_messages.user_id', '!=', $user->id)
                    ->count();
            }

            return response()->json([
                'status' => 'success',
                'data' => [
                    'categories' => $categories,
                    'featured_packages' => $featuredPackages,
                    'vouchers' => $vouchers,
                    'flash_sale' => $flashSale,
                    'upcoming_bookings' => $upcomingBookings,
                    'unread_notifications' => $unreadNotifications,
                    'unread_messages' => $unreadMessages,
                ],
            ]);
        } catch (\Throwable $e) {
            Log::error('Home API Error: '.$e->getMessage(), ['trace' => $e->getTraceAsString()]);

            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    private function formatOrder($order): array
    {
        $pkg = $order->package;

        return [
            'id' => $order->id,
            'user_id' => $order->user_id,
            'package_id' => $order->package_id,
            'event_date' => $order->booking_date instanceof Carbon ? $order->booking_date->format('Y-m-d') : $order->booking_date,
            'status' => $order->status,
            'total_price' => $order->total_price,
            'location_address' => $order->notes ?? 'Venue TBD',
            'notes' => $order->notes,
            'package' => $pkg ? [
                'id' => $pkg->id,
                'name' => $pkg->name,
                'price' => $pkg->price,
                'image_url' => $pkg->image_url,
            ] : null,
        ];
    }
}
