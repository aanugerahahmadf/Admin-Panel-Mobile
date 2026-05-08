<?php

namespace App\Mail;

use App\Models\Order;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class OrderPaymentNotification extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * URL gambar item yang bisa diakses publik.
     * Jika APP_URL lokal (127.0.0.1/localhost), gunakan base64 kecil sebagai fallback.
     */
    public ?string $itemImageUrl    = null;
    public ?string $itemImageBase64 = null;
    public ?string $logoBase64      = null;

    public function __construct(
        public readonly Order $order,
        public readonly User  $user,
    ) {
        $this->prepareImages();
    }

    private function prepareImages(): void
    {
        $appUrl = rtrim(config('app.url', 'http://127.0.0.1:8000'), '/');
        $isLocal = str_contains($appUrl, '127.0.0.1')
            || str_contains($appUrl, 'localhost')
            || str_contains($appUrl, '::1');

        // ── Gambar item ──────────────────────────────────────────────────────
        $item = $this->order->package ?? $this->order->product;
        if ($item) {
            $col   = $this->order->package_id ? 'package_image' : 'product_image';
            $media = $item->getFirstMedia($col);

            if ($media && file_exists($media->getPath())) {
                if (! $isLocal) {
                    // Production: pakai URL publik langsung
                    $this->itemImageUrl = $media->getUrl();
                } else {
                    // Lokal: resize ke 160px dan encode base64 kecil (~8KB)
                    $this->itemImageBase64 = $this->resizeToBase64($media->getPath(), 160, 75);
                }
            }
        }

        // ── Logo ─────────────────────────────────────────────────────────────
        $logoPath = public_path('images/logo.png');
        if (file_exists($logoPath)) {
            if (! $isLocal) {
                // Production: pakai URL publik
                $this->logoBase64 = asset('images/logo.png');
            } else {
                // Lokal: embed base64 (logo biasanya kecil)
                $this->logoBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath));
            }
        }
    }

    /**
     * Resize gambar ke maxSize px dan return sebagai data URI base64.
     * Deteksi MIME dari konten file (bukan ekstensi) untuk handle file yang salah ekstensi.
     */
    private function resizeToBase64(string $srcPath, int $maxSize = 160, int $quality = 75): ?string
    {
        try {
            // Deteksi MIME dari konten file
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime  = finfo_file($finfo, $srcPath);
            finfo_close($finfo);

            [$origW, $origH] = getimagesize($srcPath);
            $ratio = min($maxSize / $origW, $maxSize / $origH, 1.0);
            $newW  = max(1, (int) ($origW * $ratio));
            $newH  = max(1, (int) ($origH * $ratio));

            $src = match($mime) {
                'image/jpeg' => imagecreatefromjpeg($srcPath),
                'image/png'  => imagecreatefrompng($srcPath),
                'image/gif'  => imagecreatefromgif($srcPath),
                'image/webp' => imagecreatefromwebp($srcPath),
                default      => null,
            };

            if (! $src) {
                return null;
            }

            $dst   = imagecreatetruecolor($newW, $newH);
            $white = imagecolorallocate($dst, 255, 255, 255);
            imagefill($dst, 0, 0, $white);
            imagecopyresampled($dst, $src, 0, 0, 0, 0, $newW, $newH, $origW, $origH);

            ob_start();
            imagejpeg($dst, null, $quality);
            $data = ob_get_clean();

            imagedestroy($src);
            imagedestroy($dst);

            return 'data:image/jpeg;base64,' . base64_encode($data);
        } catch (\Throwable $e) {
            Log::warning('[Mail] resizeToBase64 failed: ' . $e->getMessage());
            return null;
        }
    }

    public function envelope(): Envelope
    {
        $statusValue = $this->order->payment_status instanceof \BackedEnum
            ? $this->order->payment_status->value
            : (string) $this->order->payment_status;

        $isPaid = in_array($statusValue, ['paid', 'partial']);

        $subject = $isPaid
            ? 'Pembayaran Berhasil - Pesanan #' . $this->order->order_number
            : 'Pembayaran Belum Selesai - Pesanan #' . $this->order->order_number;

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.order-payment-notification',
            with: [
                'itemImageSrc' => $this->itemImageUrl ?? $this->itemImageBase64,
                'logoSrc'      => $this->logoBase64,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
