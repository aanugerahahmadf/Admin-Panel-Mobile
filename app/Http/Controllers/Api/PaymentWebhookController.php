<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Services\MidtransService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Midtrans\Config;

class PaymentWebhookController extends Controller
{
    public function __construct()
    {
        Config::$serverKey    = config('midtrans.server_key');
        Config::$isProduction = (bool) config('midtrans.is_production');
        Config::$isSanitized  = (bool) config('midtrans.is_sanitized');
        Config::$is3ds        = (bool) config('midtrans.is_3ds');
    }

    /**
     * Handle Midtrans payment notification (webhook).
     *
     * Midtrans sends POST with JSON body containing:
     * - order_id, transaction_status, status_code, gross_amount, signature_key, etc.
     *
     * Docs: https://docs.midtrans.com/en/after-payment/http-notification
     */
    public function notificationHandler(Request $request)
    {
        try {
            $payload = $request->all();
            Log::info('[Midtrans Webhook] Notification received', $payload);

            $orderId        = $payload['order_id'] ?? null;
            $transactionId  = $payload['transaction_id'] ?? null;
            $statusCode     = $payload['status_code'] ?? null;
            $grossAmount    = $payload['gross_amount'] ?? null;
            $serverKey      = config('midtrans.server_key');
            $transactionStatus = $payload['transaction_status'] ?? null;
            $fraudStatus    = $payload['fraud_status'] ?? null;
            $paymentType    = $payload['payment_type'] ?? null;

            if (! $orderId) {
                Log::warning('[Midtrans Webhook] Missing order_id');

                return response()->json(['status' => 'error', 'message' => 'Missing order_id'], 400);
            }

            // Verify signature key
            $signature = $payload['signature_key'] ?? '';
            $expectedSignature = hash('sha512', $orderId.$statusCode.$grossAmount.$serverKey);

            if ($signature !== $expectedSignature) {
                Log::warning('[Midtrans Webhook] Invalid signature', [
                    'expected' => $expectedSignature,
                    'received' => $signature,
                ]);

                return response()->json(['status' => 'error', 'message' => 'Invalid signature'], 403);
            }

            // Find transaction by reference_number (order_id sent to Midtrans)
            $transaction = Transaction::where('reference_number', $orderId)
                ->with('order')
                ->first();

            if (! $transaction) {
                Log::warning('[Midtrans Webhook] Transaction not found', ['order_id' => $orderId]);

                return response()->json(['status' => 'error', 'message' => 'Transaction not found'], 404);
            }

            // Update payment method from notification
            if ($paymentType) {
                $transaction->update(['payment_method' => $paymentType]);
            }

            // Map Midtrans transaction status to local status
            match ($transactionStatus) {
                'capture', 'settlement' => $this->handleSuccess($transaction, $transactionId),
                'pending'               => $this->handlePending($transaction),
                'deny', 'cancel'        => $this->handleFailed($transaction, $transactionStatus),
                'expire'                => $this->handleExpired($transaction),
                'refund', 'partial_refund' => $this->handleRefund($transaction),
                default => Log::warning('[Midtrans Webhook] Unknown transaction_status', [
                    'status' => $transactionStatus,
                    'transaction_id' => $transaction->id,
                ]),
            };

            // Always return 200 OK to acknowledge receipt
            return response()->json(['status' => 'ok'], 200);

        } catch (\Throwable $e) {
            Log::error('[Midtrans Webhook] Exception: '.$e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json(['status' => 'error', 'message' => 'Internal server error'], 500);
        }
    }

    private function handleSuccess(Transaction $transaction, ?string $transactionId = null): void
    {
        $updates = ['status' => 'success'];

        if ($transactionId) {
            $updates['metadata'] = array_merge($transaction->metadata ?? [], [
                'midtrans_transaction_id' => $transactionId,
            ]);
        }

        $transaction->update($updates);
        $transaction->markAsSuccess();

        Log::info('[Midtrans Webhook] Payment success', [
            'transaction_id' => $transaction->id,
            'reference' => $transaction->reference_number,
            'amount' => $transaction->total_amount,
        ]);
    }

    private function handlePending(Transaction $transaction): void
    {
        $transaction->update(['status' => 'pending']);

        Log::info('[Midtrans Webhook] Payment pending', [
            'transaction_id' => $transaction->id,
            'reference' => $transaction->reference_number,
        ]);
    }

    private function handleFailed(Transaction $transaction, string $reason): void
    {
        $transaction->markAsFailed("Midtrans: {$reason}");

        Log::info('[Midtrans Webhook] Payment failed', [
            'transaction_id' => $transaction->id,
            'reason' => $reason,
        ]);
    }

    private function handleExpired(Transaction $transaction): void
    {
        $transaction->update(['status' => 'expired']);

        if ($transaction->order) {
            $transaction->order->update([
                'payment_status' => 'failed',
            ]);
        }

        Log::info('[Midtrans Webhook] Payment expired', [
            'transaction_id' => $transaction->id,
            'reference' => $transaction->reference_number,
        ]);
    }

    private function handleRefund(Transaction $transaction): void
    {
        $transaction->update(['status' => 'refunded']);

        if ($transaction->order) {
            $transaction->order->update([
                'payment_status' => 'refunded',
            ]);
        }

        Log::info('[Midtrans Webhook] Payment refunded', [
            'transaction_id' => $transaction->id,
            'reference' => $transaction->reference_number,
        ]);
    }
}
