<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Help;
use App\Models\History;
use App\Models\Order;
use App\Models\Package;
use App\Models\PrivacyPolicy;
use App\Models\Product;
use App\Models\Review;
use App\Models\TermsOfService;
use App\Models\Voucher;
use App\Models\WeddingDecorationPolicy;
use App\Services\CBIRService;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function byText(Request $request)
    {
        $request->validate([
            'query' => 'required|string|min:1',
        ]);

        $query = $request->input('query');

        $packages = Package::with(['media', 'category'])
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                    ->orWhere('description', 'like', "%{$query}%");
            })
            ->get();

        $products = Product::with(['media', 'category'])
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                    ->orWhere('description', 'like', "%{$query}%");
            })
            ->get();

        $categories = Category::where(function ($q) use ($query) {
            $q->where('name', 'like', "%{$query}%")
                ->orWhere('description', 'like', "%{$query}%");
        })->get();

        $vouchers = Voucher::where('is_active', true)
            ->where(function ($q) use ($query) {
                $q->where('code', 'like', "%{$query}%")
                    ->orWhere('description', 'like', "%{$query}%");
            })->get();

        $orders = Order::where('user_id', $request->user()->id)
            ->where(function ($q) use ($query) {
                $q->where('order_number', 'like', "%{$query}%")
                    ->orWhere('notes', 'like', "%{$query}%");
            })
            ->with('package:id,name')
            ->get();

        $reviews = Review::where('user_id', $request->user()->id)
            ->where('comment', 'like', "%{$query}%")
            ->with('package:id,name')
            ->get();

        $terms = TermsOfService::where(function ($q) use ($query) {
            $q->where('title', 'like', "%{$query}%")
                ->orWhere('content', 'like', "%{$query}%");
        })->get()->map(fn ($t) => [
            '_type' => 'terms',
            'id' => $t->id,
            'name' => is_string($t->content)
                ? substr($t->content, 0, 120)
                : (is_array($t->content) ? ($t->content['text'] ?? $t->title) : $t->title),
            'title' => $t->title,
        ]);

        $privacy = PrivacyPolicy::where(function ($q) use ($query) {
            $q->where('title', 'like', "%{$query}%")
                ->orWhere('content', 'like', "%{$query}%");
        })->get()->map(fn ($p) => [
            '_type' => 'privacy',
            'id' => $p->id,
            'name' => is_string($p->content)
                ? substr($p->content, 0, 120)
                : (is_array($p->content) ? ($p->content['text'] ?? $p->title) : $p->title),
            'title' => $p->title,
        ]);

        $helps = Help::where(function ($q) use ($query) {
            $q->where('title', 'like', "%{$query}%")
                ->orWhere('subtitle', 'like', "%{$query}%");
        })->get()->map(fn ($h) => [
            '_type' => 'helps',
            'id' => $h->id,
            'name' => $h->subtitle ?? $h->title,
            'title' => $h->title,
        ]);

        $weddingPolicy = WeddingDecorationPolicy::where(function ($q) use ($query) {
            $q->where('title', 'like', "%{$query}%")
                ->orWhere('content', 'like', "%{$query}%");
        })->get()->map(fn ($w) => [
            '_type' => 'wedding_policy',
            'id' => $w->id,
            'name' => is_string($w->content)
                ? substr($w->content, 0, 120)
                : (is_array($w->content) ? ($w->content['text'] ?? $w->title) : $w->title),
            'title' => $w->title,
        ]);

        $histories = History::where('user_id', $request->user()->id)
            ->where(function ($q) use ($query) {
                $q->where('reference_number', 'like', "%{$query}%")
                    ->orWhere('type', 'like', "%{$query}%")
                    ->orWhere('status', 'like', "%{$query}%")
                    ->orWhere('notes', 'like', "%{$query}%")
                    ->orWhere('info', 'like', "%{$query}%");
            })
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => [
                'packages' => $packages,
                'products' => $products,
                'categories' => $categories,
                'vouchers' => $vouchers,
                'orders' => $orders,
                'reviews' => $reviews,
                'terms' => $terms,
                'privacy' => $privacy,
                'helps' => $helps,
                'wedding_policy' => $weddingPolicy,
                'histories' => $histories,
            ],
        ]);
    }

    public function byImage(Request $request, CBIRService $cbirService)
    {
        $request->validate([
            'image' => 'required|image|max:10240',
        ]);

        $targetCount = (int) $request->input('top_k', 20);
        $apiResponse = $cbirService->searchByImage($request->file('image'), $targetCount);

        if (isset($apiResponse['error']) || ! ($apiResponse['success'] ?? false)) {
            return response()->json([
                'status' => 'error',
                'data' => [],
                'message' => $apiResponse['message'] ?? __('Pencarian gambar gagal.'),
            ]);
        }

        $results = $apiResponse['results'] ?? [];
        $mixedResults = [];
        $seen = [];

        foreach ($results as $r) {
            $type = $r['type'] ?? 'product';
            $id = $r['owner_id'] ?? null;

            if (! $id) {
                continue;
            }

            $key = "{$type}_{$id}";
            if (isset($seen[$key])) {
                continue;
            }

            $model = $type === 'package'
                ? Package::with(['category', 'media'])->find($id)
                : Product::with(['category', 'media'])->find($id);

            if (! $model) {
                continue;
            }

            $mixedResults[] = [
                'type' => $type,
                'similarity' => max(0, (float) ($r['similarity'] ?? 0)),
                'score' => max(0, (float) ($r['score'] ?? 0)),
                'data' => array_merge($model->toArray(), [
                    'image_url' => $model->image_url,
                    'category' => $model->category?->toArray(),
                    'average_rating' => (float) number_format($model->reviews()->avg('rating') ?: 0, 1),
                ]),
            ];

            $seen[$key] = true;
        }

        usort($mixedResults, fn ($a, $b) => ($b['similarity'] ?? 0) <=> ($a['similarity'] ?? 0));

        $total = count($mixedResults);
        if ($total > 1) {
            $step = 100 / ($total - 1);
            foreach ($mixedResults as $idx => &$r) {
                $r['similarity'] = $idx === 0 ? 100.0 : max(0, round(100 - ($idx * $step), 1));
            }
            unset($r);
        } elseif ($total === 1) {
            $mixedResults[0]['similarity'] = 100.0;
        }

        return response()->json([
            'status' => 'success',
            'data' => $mixedResults,
        ]);
    }
}
