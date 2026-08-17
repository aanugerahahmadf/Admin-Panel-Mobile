<?php

namespace App\Http\Controllers\Api;

use App\Models\Transaction;
use App\Services\BriService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BriVaWebhookController extends Controller
{
    public function notification(Request $request, BriService $bri): JsonResponse
    {
        $rawBody = $request->getContent();

        Log::info('[BRI-VA] Notifikasi pembayaran diterima', [
            'signature' => $request->header('X-SIGNATURE'),
            'timestamp' => $request->header('X-TIMESTAMP'),
            'body' => $rawBody,
        ]);

        $payload = json_decode($rawBody, true);
        if (! is_array($payload)) {
            return $this->ack('Invalid JSON body');
        }

        // 1. Verifikasi signature (gagal bila SNAP belum aktif / key tak terkonfigurasi)
        if ($bri->snapEnabled()) {
            if (! $bri->verifyVaNotification(
                (string) $request->header('X-TIMESTAMP', ''),
                (string) $request->header('X-SIGNATURE', ''),
                $rawBody
            )) {
                return $this->ack('Invalid signature');
            }
        }

        // 2. Temukan transaksi berdasarkan nomor VA (BRIVA) atau referensi (QRIS)
        $vaNumber = (string) ($payload['virtualAccountNo'] ?? '');
        $reference = (string) ($payload['originalPartnerReferenceNo'] ?? $payload['partnerReferenceNo'] ?? '');
        $paidAmount = (float) ($payload['paidAmount']['value'] ?? $payload['amount']['value'] ?? $payload['totalAmount']['value'] ?? 0);
        $status = (string) ($payload['additionalInfo']['transactionStatus'] ?? $payload['transactionStatus'] ?? $payload['paymentFlagStatus'] ?? 'PAID');

        $transaction = null;
        if ($vaNumber !== '') {
            $transaction = Transaction::where('virtual_account_no', $vaNumber)
                ->whereIn('status', ['pending', 'processing'])
                ->latest()
                ->first();
        } elseif ($reference !== '') {
            $transaction = Transaction::where('reference_number', $reference)
                ->whereIn('status', ['pending', 'processing'])
                ->latest()
                ->first();
        }

        if (! $transaction) {
            Log::warning('[BRI-VA] Transaksi (VA '.$vaNumber.' / ref '.$reference.') tidak ditemukan atau sudah dibayar.');

            return $this->ack('Transaction not found');
        }

        $totalAmount = (float) $transaction->total_amount;
        $tolerance = (float) config('bri.amount_tolerance', 500);
        if ($paidAmount > 0 && abs($paidAmount - $totalAmount) > $tolerance) {
            Log::warning('[BRI-VA] Nominal tidak cocok untuk VA '.$vaNumber.'. Diterima: '.$paidAmount.', diharapkan: '.$totalAmount);

            return $this->ack('Amount mismatch');
        }

        if (strtoupper($status) === 'PAID' || $status === '00' || $paidAmount > 0) {
            $isQris = isset($payload['originalPartnerReferenceNo']) || $reference !== '';
            $transaction->markAsSuccess();
            $transaction->update([
                'payment_method' => $transaction->payment_method ?? ($isQris ? 'QRIS' : 'Virtual Account BRI'),
                'notes' => $isQris
                    ? __('Pembayaran QRIS otomatis terkonfirmasi')
                    : __('Pembayaran Virtual Account BRI otomatis terkonfirmasi'),
                'metadata' => array_merge($transaction->metadata ?? [], [
                    'bri_va_notification' => $payload,
                ]),
            ]);

            if ($transaction->order) {
                app(OrderController::class)->sendPaymentNotifications($transaction->order);
            }

            Log::info('[BRI] Transaksi #'.$transaction->id.' ditandai LUNAS via notifikasi '.($isQris ? 'QRIS' : 'BRI-VA').'.');
        }

        // BRI mengharapkan HTTP 200 agar tidak mengirim ulang.
        return $this->ack('ok');
    }

    private function ack(string $message): JsonResponse
    {
        return response()->json([
            'responseCode' => '2009700',
            'responseMessage' => 'Successful',
            'info' => $message,
        ], 200);
    }
}