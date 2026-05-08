<?php

namespace App\Observers;

use App\Enums\OrderPaymentStatus;
use App\Enums\OrderStatus;
use App\Models\History;
use App\Models\Order;
use App\Services\PaymentNotificationService;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class OrderObserver
{
    /**
     * Handle the Order "created" event.
     */
    public function created(Order $order): void
    {
        History::create([
            'user_id' => $order->user_id,
            'type' => 'order',
            'transaction_id' => $order->id,
            'reference_number' => $order->order_number,
            'amount' => $order->total_price,
            'info' => $order->package?->name ?? __('Pemesanan Paket'),
            'status' => $order->status instanceof \BackedEnum ? $order->status->value : $order->status,
            'notes' => $order->notes,
            'created_at' => $order->created_at,
        ]);
    }

    /**
     * Handle the Order "updated" event.
     */
    public function updated(Order $order): void
    {
        // Fitur Otomatis: Auto-Refund jika Order Dibatalkan tapi sudah Dibayar
        if ($order->isDirty('status') && $order->status === OrderStatus::CANCELLED) {
            if (in_array($order->payment_status, [OrderPaymentStatus::PAID, OrderPaymentStatus::PARTIAL])) {
                $user = $order->user;
                if ($user) {
                    $user->increment('balance', $order->total_price);
                    $order->updateQuietly(['payment_status' => OrderPaymentStatus::REFUNDED]);

                    History::create([
                        'user_id' => $order->user_id,
                        'type' => 'balance',
                        'transaction_id' => $order->id,
                        'reference_number' => 'REF-'.$order->order_number,
                        'amount' => $order->total_price,
                        'info' => __('Refund Otomatis (Pembatalan Order #').$order->order_number.')',
                        'status' => 'success',
                    ]);

                    try {
                        Notification::make()
                            ->title(__('Refund Berhasil'))
                            ->body(__('Dana sebesar Rp ').number_format($order->total_price, 2, ',', '.').__(' telah dikembalikan ke saldo Anda karena pembatalan Order #').$order->order_number)
                            ->success()
                            ->sendToDatabase($user);
                    } catch (\Throwable $e) {
                        Log::warning('[OrderObserver] Notification failed: '.$e->getMessage());
                    }
                }
            } else {
                // Belum bayar → set payment_status ke CANCELLED
                $order->updateQuietly(['payment_status' => OrderPaymentStatus::CANCELLED]);
            }
        }

        // 🔔 Notify User: Status Change (Hanya jika status berubah)
        if ($order->isDirty('status')) {
            $user = $order->user;
            if ($user) {
                // 📣 Notifikasi Pembatalan: Inbox + Bell + Email + WhatsApp
                if ($order->status === OrderStatus::CANCELLED) {
                    app(PaymentNotificationService::class)
                        ->sendCancellationNotification($order, $user);
                }

                $statusLabel = $order->status instanceof OrderStatus
                    ? $order->status->getLabel()
                    : (is_string($order->status) ? $order->status : __('Tidak Diketahui'));

                $statusIcon = $order->status instanceof OrderStatus
                    ? $order->status->getIcon()
                    : 'heroicon-o-information-circle';

                try {
                    Notification::make()
                        ->title(__('Update Pesanan #').$order->order_number)
                        ->body(__('Status pesanan Anda kini: ').$statusLabel)
                        ->info()
                        ->icon($statusIcon)
                        ->sendToDatabase($user);
                } catch (\Throwable $e) {
                    Log::warning('[OrderObserver] Status notification failed: '.$e->getMessage());
                }
            }
        }

        // 💰 Notifikasi Otomatis: Update Status Pembayaran (Inbox + Bell + Email + WhatsApp)
        if ($order->isDirty('payment_status')) {
            $user = $order->user;
            if ($user) {
                app(PaymentNotificationService::class)
                    ->sendPaymentNotification($order, $user);
            }
        }

        History::updateOrCreate(
            ['type' => 'order', 'transaction_id' => $order->id],
            [
                'status' => $order->status instanceof \BackedEnum ? $order->status->value : (string) $order->status,
                'amount' => $order->total_price,
                'info' => $order->package?->name ?? __('Pemesanan Paket'),
                'notes' => $order->notes,
            ]
        );
    }

    /**
     * Handle the Order "deleting" event — fired BEFORE the record is deleted.
     * Update History status to cancelled so it's visible in Transaction History.
     */
    public function deleting(Order $order): void
    {
        History::where('type', 'order')
            ->where('transaction_id', $order->id)
            ->update(['status' => 'cancelled']);
    }

    /**
     * Handle the Order "deleted" event.
     */
    public function deleted(Order $order): void
    {
        // History already updated to cancelled in deleting() event
        // Nothing else needed here
    }

    /**
     * Handle the Order "restored" event.
     */
    public function restored(Order $order): void
    {
        History::withTrashed()
            ->where('type', 'order')
            ->where('transaction_id', $order->id)
            ->restore();
    }

    /**
     * Handle the Order "force deleted" event.
     */
    public function forceDeleted(Order $order): void
    {
        History::withTrashed()
            ->where('type', 'order')
            ->where('transaction_id', $order->id)
            ->forceDelete();
    }
}
