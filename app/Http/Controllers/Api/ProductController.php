<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = Product::with(['category', 'reviews']);

            if ($request->filled('category_id')) {
                $query->where('category_id', $request->category_id);
            }

            if ($request->filled('theme')) {
                $query->where('theme', 'like', '%'.$request->theme.'%');
            }

            if ($request->filled('min_price')) {
                $query->where('price', '>=', $request->min_price);
            }

            if ($request->filled('max_price')) {
                $query->where('price', '<=', $request->max_price);
            }

            if ($request->filled('search')) {
                $searchTerm = $request->search;
                $query->where(function ($q) use ($searchTerm): void {
                    $q->where('name', 'like', '%'.$searchTerm.'%')
                        ->orWhere('description', 'like', '%'.$searchTerm.'%');
                });
            }

            $sortBy = $request->get('sort_by', 'created_at');
            $sortDirection = $request->get('sort_direction', 'desc');
            $allowedSortFields = ['name', 'price', 'created_at', 'discount_price'];
            if (! in_array($sortBy, $allowedSortFields)) {
                $sortBy = 'created_at';
            }
            if (! in_array(strtolower($sortDirection), ['asc', 'desc'])) {
                $sortDirection = 'desc';
            }

            $query->orderBy($sortBy, $sortDirection);
            $products = $query->paginate($request->get('per_page', 10), ['*']);

            return response()->json([
                'status' => 'success',
                'data' => $products->items(),
                'pagination' => [
                    'current_page' => $products->currentPage(),
                    'last_page' => $products->lastPage(),
                    'per_page' => $products->perPage(),
                    'total' => $products->total(),
                    'has_more_pages' => $products->hasMorePages(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => __('Gagal mengambil produk'),
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $product = Product::with([
                'category:id,name,description',
                'reviews' => function ($query): void {
                    $query->with('user:id,full_name,avatar_url')->latest()->limit(5);
                },
            ])->findOrFail($id, ['*']);

            return response()->json([
                'status' => 'success',
                'data' => $product,
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => __('Produk tidak ditemukan'),
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => __('Gagal mengambil detail produk'),
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function featured(Request $request)
    {
        $products = Product::with(['category', 'reviews'])
            ->where('is_featured', true)
            ->paginate($request->get('per_page', 10), ['*']);

        return response()->json([
            'status' => 'success',
            'data' => $products->items(),
            'pagination' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
                'has_more_pages' => $products->hasMorePages(),
            ],
        ]);
    }

    public function onSale(Request $request)
    {
        $products = Product::with(['category', 'reviews'])
            ->whereNotNull('discount_price')
            ->where('discount_price', '<', 'price')
            ->paginate($request->get('per_page', 10), ['*']);

        return response()->json([
            'status' => 'success',
            'data' => $products->items(),
            'pagination' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
                'has_more_pages' => $products->hasMorePages(),
            ],
        ]);
    }
}
