<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Transaction;
use Illuminate\Support\Facades\Log;
use Midtrans\Config;
use Midtrans\Snap;

class MidtransService
{
    public function __construct()
    {
        Config::$serverKey    = config('midtrans.server_key');
        Config::$clientKey    = config('midtrans.client_key');
        Config::$isProduction = (bool) config('midtrans.is_production');
        Config::$isSanitized  = (bool) config('midtrans.is_sanitized');
        Config::$is3ds        = (bool) config('midtrans.is_3ds');

        // Disable SSL verification on local/development to avoid Windows CA bundle issues.
        // Never set this to false in production.
        if (! config('midtrans.is_production') && app()->isLocal()) {
            Config::$curlOptions = [
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false,
            ];
        }
    }

    /**
     * Create a Midtrans transaction and return the snap token.
     * Also persists snap_token and payment_url on the given Transaction model.
     */
    public function createSnapToken(Transaction $transaction): ?string
    {
        $order = $transaction->order;
        $user  = $transaction->user;

        if (! $order || ! $user) {
            Log::error('[Midtrans] Missing order or user for transaction #'.$transaction->id);

            return null;
        }

        $params = [
            'transaction_details' => [
                'order_id'     => $transaction->reference_number,
                'gross_amount' => (int) round($transaction->total_amount),
            ],
            'customer_details' => [
                'first_name' => $user->name,
                'email'      => $user->email,
                'phone'      => $user->whatsapp ?? $user->phone ?? '',
            ],
            'item_details' => $this->buildItemDetails($order, $transaction),
        ];

        try {
            $snapToken = Snap::getSnapToken($params);

            $paymentUrl = config('midtrans.is_production')
                ? 'https://app.midtrans.com/snap/v2/vtweb/'.$snapToken
                : 'https://app.sandbox.midtrans.com/snap/v2/vtweb/'.$snapToken;

            $transaction->update([
                'snap_token'  => $snapToken,
                'payment_url' => $paymentUrl,
            ]);

            return $snapToken;
        } catch (\Throwable $e) {
            Log::error('[Midtrans] Failed to get snap token: '.$e->getMessage(), [
                'transaction_id' => $transaction->id,
                'reference'      => $transaction->reference_number,
            ]);

            return null;
        }
    }

    private function buildItemDetails(Order $order, Transaction $transaction): array
    {
        $items = [];

        $name  = $order->package?->name ?? $order->product?->name ?? 'Pesanan #'.$order->order_number;
        $price = (int) round($transaction->total_amount / max(1, $order->quantity ?? 1));
        $qty   = $order->quantity ?? 1;

        $items[] = [
            'id'       => 'ORDER-'.$order->id,
            'price'    => $price,
            'quantity' => $qty,
            'name'     => mb_substr($name, 0, 50),
        ];

        return $items;
    }
}
