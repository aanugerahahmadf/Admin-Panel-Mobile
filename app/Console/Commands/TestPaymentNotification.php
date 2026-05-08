<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Models\User;
use App\Services\PaymentNotificationService;
use Illuminate\Console\Command;

class TestPaymentNotification extends Command
{
    protected $signature = 'notify:test
                            {--order= : ID order yang akan ditest (default: order terbaru)}
                            {--user=  : ID user (default: user dari order)}
                            {--email= : Override email tujuan}
                            {--wa=    : Override nomor WhatsApp tujuan}';

    protected $description = 'Test kirim notifikasi pembayaran via Email (Gmail) dan WhatsApp (Fonnte)';

    public function handle(PaymentNotificationService $service): int
    {
        $this->info('');
        $this->info('╔══════════════════════════════════════════╗');
        $this->info('║   Test Notifikasi Pembayaran             ║');
        $this->info('║   Email (Gmail) + WhatsApp (Fonnte)      ║');
        $this->info('╚══════════════════════════════════════════╝');
        $this->info('');

        // ── Ambil Order ───────────────────────────────────────────────────────
        $orderId = $this->option('order');
        $order   = $orderId
            ? Order::with(['user', 'package', 'product'])->find($orderId)
            : Order::with(['user', 'package', 'product'])->latest()->first();

        if (! $order) {
            $this->error('Tidak ada order ditemukan. Buat order dulu atau gunakan --order=ID');
            return 1;
        }

        // ── Ambil User ────────────────────────────────────────────────────────
        $userId = $this->option('user');
        $user   = $userId ? User::find($userId) : $order->user;

        if (! $user) {
            $this->error('User tidak ditemukan.');
            return 1;
        }

        // ── Override email/WA jika ada ────────────────────────────────────────
        if ($overrideEmail = $this->option('email')) {
            $user->email = $overrideEmail;
        }
        if ($overrideWa = $this->option('wa')) {
            $user->whatsapp = $overrideWa;
        }

        // ── Info ──────────────────────────────────────────────────────────────
        $item = $order->package ?? $order->product;
        $this->table(
            ['Field', 'Value'],
            [
                ['Order ID',        $order->id],
                ['Order Number',    $order->order_number],
                ['Item',            $item?->name ?? '-'],
                ['Payment Status',  $order->payment_status instanceof \BackedEnum ? $order->payment_status->value : (string) $order->payment_status],
                ['Total',           'Rp ' . number_format($order->total_price, 0, ',', '.')],
                ['User',            $user->full_name ?? $user->email],
                ['Email',           $user->email ?: '(kosong)'],
                ['WhatsApp',        $user->whatsapp ?: ($user->phone ?: '(kosong)')],
            ]
        );

        // ── Konfirmasi ────────────────────────────────────────────────────────
        if (! $this->confirm('Kirim notifikasi ke email dan WhatsApp di atas?', true)) {
            $this->warn('Dibatalkan.');
            return 0;
        }

        $this->info('');
        $this->info('Mengirim notifikasi...');

        // ── Kirim ─────────────────────────────────────────────────────────────
        try {
            $service->sendPaymentNotification($order, $user);

            $this->info('');
            $this->info('✅ Notifikasi berhasil dikirim!');
            $this->info('   📧 Email  → ' . ($user->email ?: 'tidak ada'));
            $this->info('   📱 WA     → ' . ($user->whatsapp ?: $user->phone ?: 'tidak ada'));
            $this->info('');
            $this->info('Cek log di storage/logs/laravel.log untuk detail.');
        } catch (\Throwable $e) {
            $this->error('❌ Gagal: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
