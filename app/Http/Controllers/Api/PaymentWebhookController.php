<?php

namespace App\Http\Controllers\Api;

use App\Services\MidtransService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentWebhookController extends Controller
{
    public function notification(Request $request, MidtransService $midtrans): JsonResponse
    {
        Log::info('[Midtrans] Webhook diterima', $request->all());

        $handled = $midtrans->handleNotification($request->all());

        if (! $handled) {
            Log::warning('[Midtrans] Webhook tidak diproses (order_id tidak dikenali).', $request->all());
        }

        // Midtrans mengharapkan 200 agar tidak mengirim ulang.
        return response()->json(['status' => 'success']);
    }
}