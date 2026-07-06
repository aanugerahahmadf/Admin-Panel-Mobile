<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Package;
use App\Models\Product;
use App\Models\User;
use App\Models\Voucher;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function index(): JsonResponse
    {
        try {
            $totalUsers = User::count();
            $totalPackages = Package::count();
            $totalProducts = Product::count();
            $totalOrders = Order::count();
            $totalVouchers = Voucher::count();
            $totalCategories = \App\Models\Category::count();
            $totalRevenue = Order::where('payment_status', 'paid')->sum('total_price');
            $recentOrders = Order::with('user:id,full_name,email')
                ->latest()
                ->take(5)
                ->get()
                ->map(fn ($o) => [
                    'id' => $o->id,
                    'order_number' => $o->order_number,
                    'user_name' => $o->user?->full_name,
                    'total_price' => $o->total_price,
                    'status' => $o->status->value,
                    'created_at' => $o->created_at?->toISOString(),
                ]);

            $ordersByStatus = [
                'pending' => Order::where('status', 'pending')->count(),
                'confirmed' => Order::where('status', 'confirmed')->count(),
                'processing' => Order::where('status', 'processing')->count(),
                'completed' => Order::where('status', 'completed')->count(),
                'cancelled' => Order::where('status', 'cancelled')->count(),
            ];

            return response()->json([
                'status' => 'success',
                'data' => [
                    'total_users' => $totalUsers,
                    'total_packages' => $totalPackages,
                    'total_products' => $totalProducts,
                    'total_categories' => $totalCategories,
                    'total_orders' => $totalOrders,
                    'total_vouchers' => $totalVouchers,
                    'total_revenue' => $totalRevenue,
                    'recent_orders' => $recentOrders,
                    'orders_by_status' => $ordersByStatus,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => __('Gagal memuat dashboard'),
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
