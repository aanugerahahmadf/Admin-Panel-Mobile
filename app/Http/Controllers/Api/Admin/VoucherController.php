<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Voucher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VoucherController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Voucher::query();

            if ($request->filled('search')) {
                $s = $request->search;
                $query->where('code', 'like', "%{$s}%");
            }
            if ($request->filled('is_active')) {
                $query->where('is_active', $request->boolean('is_active'));
            }

            $vouchers = $query->orderBy('created_at', 'desc')
                ->paginate($request->get('per_page', 20));

            return response()->json([
                'status' => 'success',
                'data' => $vouchers->items(),
                'pagination' => [
                    'current_page' => $vouchers->currentPage(),
                    'last_page' => $vouchers->lastPage(),
                    'per_page' => $vouchers->perPage(),
                    'total' => $vouchers->total(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => __('Gagal mengambil voucher'), 'error' => $e->getMessage()], 500);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $voucher = Voucher::with('users:id,full_name,email')->findOrFail($id);
            return response()->json(['status' => 'success', 'data' => $voucher]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => __('Voucher tidak ditemukan')], 404);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'code' => 'required|string|max:50|unique:vouchers,code',
                'description' => 'nullable|string',
                'discount_amount' => 'required|numeric|min:0',
                'discount_type' => 'required|string|in:fixed,percentage',
                'min_purchase' => 'numeric|min:0',
                'expires_at' => 'nullable|date',
                'is_active' => 'boolean',
                'is_global' => 'boolean',
                'max_uses' => 'nullable|integer|min:0',
                'user_ids' => 'nullable|array',
                'user_ids.*' => 'integer|exists:users,id',
            ]);

            $voucher = Voucher::create($validated);

            if (! $voucher->is_global && ! empty($validated['user_ids'])) {
                $voucher->users()->attach($validated['user_ids'], ['claimed_at' => now()]);
            }

            return response()->json([
                'status' => 'success',
                'message' => __('Voucher berhasil dibuat'),
                'data' => ['id' => $voucher->id],
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['status' => 'error', 'message' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => __('Gagal membuat voucher'), 'error' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $voucher = Voucher::findOrFail($id);

            $validated = $request->validate([
                'code' => 'sometimes|string|max:50|unique:vouchers,code,' . $voucher->id,
                'description' => 'nullable|string',
                'discount_amount' => 'sometimes|numeric|min:0',
                'discount_type' => 'sometimes|string|in:fixed,percentage',
                'min_purchase' => 'numeric|min:0',
                'expires_at' => 'nullable|date',
                'is_active' => 'boolean',
                'is_global' => 'boolean',
                'max_uses' => 'nullable|integer|min:0',
                'user_ids' => 'nullable|array',
                'user_ids.*' => 'integer|exists:users,id',
            ]);

            $voucher->update($validated);

            if ($request->has('is_global')) {
                if ($voucher->is_global) {
                    $voucher->users()->detach();
                } elseif ($request->has('user_ids')) {
                    $voucher->users()->sync($validated['user_ids'] ?? []);
                }
            } elseif ($request->has('user_ids')) {
                $voucher->users()->sync($validated['user_ids']);
            }

            return response()->json(['status' => 'success', 'message' => __('Voucher berhasil diperbarui')]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['status' => 'error', 'message' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => __('Gagal memperbarui voucher'), 'error' => $e->getMessage()], 500);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $voucher = Voucher::findOrFail($id);
            $voucher->delete();
            return response()->json(['status' => 'success', 'message' => __('Voucher berhasil dihapus')]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => __('Gagal menghapus voucher')], 500);
        }
    }
}
