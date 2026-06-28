<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $items = Cart::where('user_id', $request->user()->id)
            ->with(['product', 'package'])
            ->latest()
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $items,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => 'nullable|exists:products,id',
            'package_id' => 'nullable|exists:packages,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $validated['user_id'] = $request->user()->id;

        $existing = Cart::where('user_id', $request->user()->id)
            ->where(function ($q) use ($validated) {
                if (isset($validated['product_id'])) {
                    $q->where('product_id', $validated['product_id']);
                }
                if (isset($validated['package_id'])) {
                    $q->where('package_id', $validated['package_id']);
                }
            })->first();

        if ($existing) {
            $existing->increment('quantity', $validated['quantity']);
            return response()->json(['status' => 'success', 'data' => $existing->fresh()->load(['product', 'package'])]);
        }

        $item = Cart::create($validated);
        $item->load(['product', 'package']);

        return response()->json(['status' => 'success', 'data' => $item], 201);
    }

    public function update(Request $request, Cart $cart): JsonResponse
    {
        if ($cart->user_id !== $request->user()->id) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate(['quantity' => 'required|integer|min:1']);
        $cart->update($validated);

        return response()->json(['status' => 'success', 'data' => $cart->fresh()->load(['product', 'package'])]);
    }

    public function destroy(Request $request, Cart $cart): JsonResponse
    {
        if ($cart->user_id !== $request->user()->id) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
        }

        $cart->delete();

        return response()->json(['status' => 'success', 'message' => 'Item removed from cart']);
    }
}
