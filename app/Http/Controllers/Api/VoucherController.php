<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Voucher;
use Illuminate\Http\Request;

class VoucherController extends Controller
{
    public function index()
    {
        $locale = app()->getLocale();
        $vouchers = Voucher::where('is_active', true)
            ->where(function ($q): void {
                $q->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })->get();

        $vouchers->transform(function ($voucher) use ($locale) {
            $voucher->description = $voucher->trans('description', $locale);
            return $voucher;
        });

        return response()->json([
            'status' => 'success',
            'data' => $vouchers,
        ]);
    }

    public function claim(Request $request, Voucher $voucher): JsonResponse
    {
        $locale = app()->getLocale();
        $user = $request->user();

        if (! $voucher->is_active || ($voucher->expires_at && $voucher->expires_at->isPast())) {
            return response()->json([
                'status' => 'error',
                'message' => __('Voucher tidak valid atau sudah kadaluarsa'),
            ], 400);
        }

        if ($voucher->max_uses && $voucher->uses_count >= $voucher->max_uses) {
            return response()->json([
                'status' => 'error',
                'message' => __('Voucher sudah habis digunakan'),
            ], 400);
        }

        $voucher->assignToUser($user->id);

        $voucher->description = $voucher->trans('description', $locale);

        return response()->json([
            'status' => 'success',
            'message' => __('Voucher berhasil diklaim'),
            'data' => $voucher,
        ]);
    }

    public function validateVoucher(Request $request)
    {
        $locale = app()->getLocale();

        $request->validate([
            'code' => 'required|string',
            'amount' => 'required|numeric',
        ]);

        $voucher = Voucher::where('code', $request->code)
            ->where('is_active', true)
            ->where(function ($q): void {
                $q->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })->first(['*']);

        if (! $voucher) {
            return response()->json([
                'status' => 'error',
                'message' => __('Voucher tidak valid atau sudah kadaluarsa'),
            ], 404);
        }

        if ($request->amount < $voucher->min_purchase) {
            return response()->json([
                'status' => 'error',
                'message' => __('Minimum pembelian untuk voucher ini adalah Rp').' '.number_format($voucher->min_purchase, 0, ',', '.'),
            ], 400);
        }

        $voucher->description = $voucher->trans('description', $locale);

        return response()->json([
            'status' => 'success',
            'data' => $voucher,
        ]);
    }
}
