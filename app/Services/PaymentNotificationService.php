<?php

namespace App\Services;

use App\Enums\OrderPaymentStatus;
use App\Mail\OrderPaymentNotification;
use App\Models\Message;
use App\Models\Order;
use App\Models\User;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class PaymentNotificationService
{
    /**
     * Kirim semua notifikasi pembatalan order (Inbox + Bell + Email + WhatsApp).
     * Dipanggil dari OrderObserver saat status berubah ke CANCELLED.
     */
    public function sendCancellationNotification(Order $order, User $user): void
    {
        $lockKey = "cancel_notif_{$order->id}";
        if (\Illuminate\Support\Facades\Cache::has($lockKey)) {
            Log::info("[CancellationNotification] Skipped duplicate for order #{$order->order_number}");
            return;
        }
        \Illuminate\Support\Facades\Cache::put($lockKey, true, now()->addSeconds(60));

        $item      = $order->package ?? $order->product;
        $itemType  = $order->package_id ? 'Package' : 'Product';
        $itemName  = $item?->name ?? 'Item';
        $itemCat   = $item?->category?->name ?? 'Umum';
        $itemImage = $item?->image_url ?? '';
        $name      = $user->full_name ?? $user->username ?? 'Pelanggan';
        $amount    = 'Rp ' . number_format((float) $order->total_price, 0, ',', '.');

        $isRefunded = in_array(
            $order->payment_status instanceof \BackedEnum ? $order->payment_status->value : (string) $order->payment_status,
            ['paid', 'partial']
        );

        $message = "❌ Halo Kak {$name},\n\n"
            . "Pesanan Anda telah *dibatalkan*.\n\n"
            . "📦 *{$itemType}:* {$itemName}\n"
            . "🏷️ *Kategori:* {$itemCat}\n"
            . "🔖 *No. Order:* {$order->order_number}\n"
            . "💰 *Total:* {$amount}\n"
            . ($isRefunded
                ? "\n💸 Dana Anda akan dikembalikan ke saldo akun.\n"
                : '')
            . "\nHubungi kami jika ada pertanyaan. 💬";

        // 1. Inbox
        $this->sendCancellationToInbox($order, $user, $itemName, $itemImage, $message, $isRefunded);

        // 2. Bell
        try {
            Notification::make()
                ->title('Pesanan #' . $order->order_number . ' Dibatalkan')
                ->body("Pesanan {$itemName} telah dibatalkan." . ($isRefunded ? ' Dana dikembalikan ke saldo.' : ''))
                ->icon('heroicon-o-x-circle')
                ->danger()
                ->actions([
                    \Filament\Notifications\Actions\Action::make('view_order')
                        ->label('Lihat Pesanan')
                        ->icon('heroicon-o-eye')
                        ->url(\App\Filament\User\Resources\OrderResource::getUrl('view', ['record' => $order->id]))
                        ->button(),
                ])
                ->sendToDatabase($user);
        } catch (\Throwable $e) {
            Log::warning('[CancellationNotification] Bell failed: ' . $e->getMessage());
        }

        // 3. Email
        try {
            if (! empty($user->email)) {
                Mail::to($user->email)->send(new OrderPaymentNotification($order, $user));
                Log::info("[CancellationNotification] Email sent to {$user->email} for order #{$order->order_number}");
            }
            $admin = User::whereHas('roles', fn ($q) => $q->where('name', 'super_admin'))->first();
            if ($admin && $admin->email && $admin->email !== $user->email) {
                Mail::to($admin->email)->send(new OrderPaymentNotification($order, $user));
            }
        } catch (\Throwable $e) {
            Log::warning('[CancellationNotification] Email failed: ' . $e->getMessage());
        }

        // 4. WhatsApp
        $this->sendWhatsApp($user, $message, $itemImage);
    }

    private function sendCancellationToInbox(
        Order $order,
        User $user,
        string $itemName,
        string $itemImage,
        string $message,
        bool $isRefunded
    ): void {
        try {
            $inbox = ChatService::getOrCreateInboxWithAdmin($user->id);
            $admin = User::whereHas('roles', fn ($q) => $q->where('name', 'super_admin'))->first();

            Message::create([
                'inbox_id' => $inbox->id,
                'user_id'  => $admin?->id ?? $user->id,
                'message'  => $message,
                'meta'     => [
                    'type'              => $order->package_id ? 'package' : 'product',
                    'name'              => $itemName,
                    'price'             => $order->total_price,
                    'image'             => $itemImage,
                    'is_cancellation'   => true,
                    'is_refunded'       => $isRefunded,
                    'order_number'      => $order->order_number,
                    'order_id'          => $order->id,
                    'payment_status'    => $order->payment_status instanceof OrderPaymentStatus
                        ? $order->payment_status->getLabel()
                        : (string) $order->payment_status,
                ],
            ]);
        } catch (\Throwable $e) {
            Log::warning('[CancellationNotification] Inbox failed: ' . $e->getMessage());
        }
    }

    /**
     * Kirim semua notifikasi pembayaran (Email + WhatsApp + Inbox + Bell).
     * Dipanggil dari OrderObserver saat payment_status berubah.
     */
    public function sendPaymentNotification(Order $order, User $user): void
    {
        // Cegah duplikat — skip jika sudah dikirim dalam 60 detik terakhir
        $psValue = $order->payment_status instanceof \BackedEnum
            ? $order->payment_status->value
            : (string) $order->payment_status;
        $lockKey = "payment_notif_{$order->id}_{$psValue}";
        if (\Illuminate\Support\Facades\Cache::has($lockKey)) {
            Log::info("[PaymentNotification] Skipped duplicate for order #{$order->order_number}");
            return;
        }
        \Illuminate\Support\Facades\Cache::put($lockKey, true, now()->addSeconds(60));
        $paymentStatus = $order->payment_status instanceof OrderPaymentStatus
            ? $order->payment_status
            : OrderPaymentStatus::tryFrom((string) $order->payment_status);

        $isPaid = in_array(
            $paymentStatus?->value,
            [OrderPaymentStatus::PAID->value, OrderPaymentStatus::PARTIAL->value]
        );

        $paymentLabel = $paymentStatus?->getLabel() ?? (string) $order->payment_status;

        // Deteksi item (package atau product)
        $item      = $order->package ?? $order->product;
        $itemType  = $order->package_id ? 'Package' : 'Product';
        $itemName  = $item?->name ?? 'Item';
        $itemCat   = $item?->category?->name ?? 'Umum';
        $itemImage = $item?->image_url ?? '';

        // Pesan teks utama
        $message = $this->buildMessage($user, $order, $isPaid, $itemType, $itemName, $itemCat, $paymentLabel);

        // Kirim ke semua channel
        $this->sendToInbox($order, $user, $item, $itemType, $itemName, $itemImage, $message);
        $this->sendBellNotification($order, $user, $itemName, $paymentLabel, $isPaid);
        $this->sendEmail($order, $user);
        $this->sendWhatsApp($user, $message, $itemImage);
    }

    // ── Pesan Teks ────────────────────────────────────────────────────────────

    private function buildMessage(
        User $user,
        Order $order,
        bool $isPaid,
        string $itemType,
        string $itemName,
        string $itemCat,
        string $paymentLabel
    ): string {
        $name   = $user->full_name ?? $user->username ?? 'Pelanggan';
        $amount = 'Rp ' . number_format((float) $order->total_price, 0, ',', '.');

        if ($isPaid) {
            return "✅ Terima kasih Kak {$name}!\n\n"
                . "Pembayaran Anda telah *berhasil* dikonfirmasi.\n\n"
                . "📦 *{$itemType}:* {$itemName}\n"
                . "🏷️ *Kategori:* {$itemCat}\n"
                . "🔖 *No. Order:* {$order->order_number}\n"
                . "💰 *Total:* {$amount}\n"
                . "📊 *Status:* {$paymentLabel}\n\n"
                . "Tim kami akan segera memproses pesanan Anda. 🎊";
        }

        return "⚠️ Halo Kak {$name},\n\n"
            . "Pesanan Anda *belum dibayar*. Segera lakukan pembayaran agar pesanan diproses.\n\n"
            . "📦 *{$itemType}:* {$itemName}\n"
            . "🏷️ *Kategori:* {$itemCat}\n"
            . "🔖 *No. Order:* {$order->order_number}\n"
            . "💰 *Total:* {$amount}\n"
            . "📊 *Status:* {$paymentLabel}\n\n"
            . "Hubungi kami jika butuh bantuan. 💬";
    }

    // ── 1. Inbox (Messages Panel) ─────────────────────────────────────────────

    private function sendToInbox(
        Order $order,
        User $user,
        mixed $item,
        string $itemType,
        string $itemName,
        string $itemImage,
        string $message
    ): void {
        try {
            $inbox = ChatService::getOrCreateInboxWithAdmin($user->id);
            $admin = User::whereHas('roles', fn ($q) => $q->where('name', 'super_admin'))->first();

            Message::create([
                'inbox_id' => $inbox->id,
                'user_id'  => $admin?->id ?? $user->id,
                'message'  => $message,
                'meta'     => [
                    'type'              => $order->package_id ? 'package' : 'product',
                    'name'              => $itemName,
                    'price'             => $order->total_price,
                    'image'             => $itemImage,
                    'is_payment_update' => true,
                    'order_number'      => $order->order_number,
                    'payment_status'    => $order->payment_status instanceof OrderPaymentStatus
                        ? $order->payment_status->getLabel()
                        : (string) $order->payment_status,
                ],
            ]);
        } catch (\Throwable $e) {
            Log::warning('[PaymentNotification] Inbox failed: ' . $e->getMessage());
        }
    }

    // ── 2. Bell Notification (Filament) ───────────────────────────────────────

    private function sendBellNotification(
        Order $order,
        User $user,
        string $itemName,
        string $paymentLabel,
        bool $isPaid
    ): void {
        try {
            Notification::make()
                ->title('Update Pembayaran #' . $order->order_number)
                ->body("Status pembayaran {$itemName}: {$paymentLabel}")
                ->icon($isPaid ? 'heroicon-o-check-circle' : 'heroicon-o-exclamation-circle')
                ->color($isPaid ? 'success' : 'warning')
                ->when($isPaid, fn ($n) => $n->success())
                ->when(! $isPaid, fn ($n) => $n->warning())
                ->actions([
                    \Filament\Notifications\Actions\Action::make('view_order')
                        ->label('Lihat Pesanan')
                        ->icon('heroicon-o-eye')
                        ->url(\App\Filament\User\Resources\OrderResource::getUrl('view', ['record' => $order->id]))
                        ->button(),
                    \Filament\Notifications\Actions\Action::make('download_invoice')
                        ->label('Unduh Invoice')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->url(route('invoice.pdf', ['order' => $order->id, 'download' => true]))
                        ->openUrlInNewTab()
                        ->button()
                        ->color('success'),
                ])
                ->sendToDatabase($user);
        } catch (\Throwable $e) {
            Log::warning('[PaymentNotification] Bell notification failed: ' . $e->getMessage());
        }
    }

    // ── 3. Email (Gmail via SMTP) ─────────────────────────────────────────────

    private function sendEmail(Order $order, User $user): void
    {
        try {
            // Kirim ke user
            if (! empty($user->email)) {
                Mail::to($user->email)
                    ->send(new OrderPaymentNotification($order, $user));
                Log::info("[PaymentNotification] Email sent to {$user->email} for order #{$order->order_number}");
            }

            // Kirim salinan ke superadmin
            $admin = User::whereHas('roles', fn ($q) => $q->where('name', 'super_admin'))->first();
            if ($admin && $admin->email && $admin->email !== $user->email) {
                Mail::to($admin->email)
                    ->send(new OrderPaymentNotification($order, $user));
                Log::info("[PaymentNotification] Email CC sent to admin {$admin->email} for order #{$order->order_number}");
            }
        } catch (\Throwable $e) {
            Log::warning('[PaymentNotification] Email failed: ' . $e->getMessage());
        }
    }

    // ── 4. WhatsApp (Fonnte API) ──────────────────────────────────────────────

    private function sendWhatsApp(User $user, string $message, string $imageUrl = ''): void
    {
        try {
            // Prioritas: whatsapp > phone
            $phone = $this->normalizePhone($user->whatsapp ?? $user->phone ?? '');

            if (empty($phone)) {
                Log::info('[PaymentNotification] WhatsApp skipped — no phone for user #' . $user->id);
                return;
            }

            $token = config('services.fonnte_token', env('FONNTE_TOKEN', ''));
            if (empty($token)) {
                Log::warning('[PaymentNotification] WhatsApp skipped — FONNTE_TOKEN not set');
                return;
            }

            $payload = [
                'target'  => $phone,
                'message' => $message,
            ];

            // Sertakan gambar jika ada
            if (! empty($imageUrl) && filter_var($imageUrl, FILTER_VALIDATE_URL)) {
                $payload['url'] = $imageUrl;
            }

            $response = Http::withHeaders([
                'Authorization' => $token,
            ])->timeout(10)->post('https://api.fonnte.com/send', $payload);

            if ($response->successful()) {
                Log::info("[PaymentNotification] WhatsApp sent to {$phone}");
            } else {
                Log::warning("[PaymentNotification] WhatsApp failed ({$response->status()}): " . $response->body());
            }
        } catch (\Throwable $e) {
            Log::warning('[PaymentNotification] WhatsApp exception: ' . $e->getMessage());
        }
    }

    /**
     * Normalisasi nomor telepon ke format internasional (628xxx).
     * Input: 08xxx, +628xxx, 628xxx → Output: 628xxx
     */
    private function normalizePhone(string $phone): string
    {
        $phone = preg_replace('/\D/', '', $phone); // hapus non-digit

        if (empty($phone)) {
            return '';
        }

        // 08xxx → 628xxx
        if (str_starts_with($phone, '0')) {
            $phone = '62' . substr($phone, 1);
        }

        // +628xxx → 628xxx (sudah di-strip oleh regex di atas)

        return $phone;
    }
}
