<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{{ __('Notifikasi Pembayaran') }}</title>
<style>
  body  { margin:0; padding:0; background:#f5f5f5; font-family:Arial,sans-serif; font-size:14px; color:#333; }
  .wrap { max-width:600px; margin:24px auto; background:#fff; border:1px solid #e0e0e0; }

  .logo-bar { text-align:center; padding:24px 0 20px; border-bottom:1px solid #e0e0e0; }
  .logo-bar img { height:48px; width:auto; }
  .logo-bar .app-name { font-size:20px; font-weight:bold; color:#111; }

  .body { padding:28px 32px; }
  .body p { margin:0 0 12px; line-height:1.6; font-size:14px; }

  .status-bar { padding:10px 16px; font-size:13px; font-weight:bold; margin-bottom:20px; border-radius:3px; }
  .status-paid    { background:#e8f5e9; color:#2e7d32; border-left:4px solid #43a047; }
  .status-pending { background:#fff8e1; color:#e65100; border-left:4px solid #ffb300; }
  .status-failed  { background:#ffebee; color:#c62828; border-left:4px solid #e53935; }

  .section-title { font-size:12px; font-weight:bold; color:#111; text-transform:uppercase;
                   letter-spacing:0.5px; border-bottom:2px solid #111; padding-bottom:6px;
                   margin:20px 0 14px; }

  table.detail { width:100%; border-collapse:collapse; margin-bottom:20px; }
  table.detail td { padding:7px 0; font-size:13px; border-bottom:1px solid #f0f0f0; }
  table.detail td.key { color:#666; width:45%; }
  table.detail td.val { font-weight:bold; color:#111; }

  .product-card { text-align:center; padding:20px 0 12px; }
  .product-card img { width:160px; height:160px; object-fit:cover;
                      border:1px solid #e0e0e0; border-radius:4px; display:block;
                      margin:0 auto; }
  .product-card .no-img { width:160px; height:160px; background:#f5f5f5;
                           border:1px solid #e0e0e0; border-radius:4px;
                           display:block; margin:0 auto;
                           line-height:160px; font-size:11px; color:#aaa; }
  .product-card .prod-name  { font-size:14px; font-weight:bold; color:#111; margin-top:10px; }
  .product-card .prod-meta  { font-size:11px; color:#888; margin-top:3px; }
  .product-card .prod-price { font-size:15px; font-weight:bold; color:#111; margin-top:6px; }

  .btn-wrap { text-align:center; margin:24px 0 8px; }
  .btn { display:inline-block; background:#111; color:#fff !important; text-decoration:none;
         padding:11px 32px; border-radius:3px; font-size:13px; font-weight:bold; }

  .footer { background:#f5f5f5; padding:16px 32px; font-size:11px; color:#999;
            border-top:1px solid #e0e0e0; text-align:center; line-height:1.8; }
</style>
</head>
<body>

@php
use App\Enums\OrderPaymentStatus;

$ps = $order->payment_status instanceof OrderPaymentStatus
    ? $order->payment_status
    : OrderPaymentStatus::tryFrom((string) $order->payment_status);

$statusVal   = $ps?->value ?? '';
$isPaid      = in_array($statusVal, ['paid', 'partial']);
$isFailed    = in_array($statusVal, ['failed']);
$statusLabel = $ps?->getLabel() ?? $statusVal;
$statusClass = $isFailed ? 'status-failed' : ($isPaid ? 'status-paid' : 'status-pending');
$statusText  = $isFailed
    ? __('Pembayaran Gagal')
    : ($isPaid ? __('Pembayaran Berhasil') : __('Menunggu Pembayaran'));

$item      = $order->package ?? $order->product;
$itemType  = $order->package_id
    ? \App\Filament\User\Resources\PackageResource::getModelLabel()
    : \App\Filament\User\Resources\ProductResource::getModelLabel();
$itemName  = $item?->name ?? '-';
$itemCat   = $item?->category?->name ?? '-';
$itemPrice = $order->total_price;

$orderUrl   = config('app.url') . '/user/orders';
$appName    = config('app.name', 'Wedding Organizer');
$userName   = $user->full_name ?? $user->username ?? __('Pelanggan');
$adminEmail = \App\Models\User::whereHas('roles', fn($q) => $q->where('name','super_admin'))
    ->value('email') ?? config('mail.from.address');

// Dari Mailable::build() — URL publik atau base64 kecil
$imgSrc  = $itemImageSrc ?? null;
$logoSrc = $logoSrc      ?? null;
@endphp

<div class="wrap">

  {{-- Logo --}}
  <div class="logo-bar">
    @if($logoSrc)
      <img src="{{ $logoSrc }}" alt="{{ $appName }}">
    @else
      <span class="app-name">{{ $appName }}</span>
    @endif
  </div>

  <div class="body">

    <div class="status-bar {{ $statusClass }}">{{ $statusText }}</div>

    <p>{{ __('Hai') }} <strong>{{ $userName }},</strong></p>

    @if($isPaid)
      <p>{{ __('Pembayaran Anda untuk pesanan berikut telah berhasil dikonfirmasi.') }}</p>
    @elseif($isFailed)
      <p>{{ __('Pembayaran Anda tidak berhasil diproses. Silakan coba lagi atau hubungi kami.') }}</p>
    @else
      <p>{{ __('Pesanan Anda belum dibayar. Segera selesaikan pembayaran agar pesanan dapat diproses.') }}</p>
    @endif

    <div class="section-title">{{ __('Rincian Pesanan') }}</div>

    <table class="detail">
      <tr>
        <td class="key">{{ __('No. Pesanan') }}</td>
        <td class="val">#{{ $order->order_number }}</td>
      </tr>
      <tr>
        <td class="key">{{ __('Tanggal Booking') }}</td>
        <td class="val">{{ \Carbon\Carbon::parse($order->booking_date)->translatedFormat('d F Y') }}</td>
      </tr>
      <tr>
        <td class="key">{{ __('Total Pembayaran') }}</td>
        <td class="val">Rp {{ number_format($itemPrice, 0, ',', '.') }}</td>
      </tr>
      <tr>
        <td class="key">{{ __('Status Pembayaran') }}</td>
        <td class="val">{{ $statusLabel }}</td>
      </tr>
      <tr>
        <td class="key">{{ __('Status Pesanan') }}</td>
        <td class="val">
          {{ $order->status instanceof \App\Enums\OrderStatus
              ? $order->status->getLabel()
              : (string) $order->status }}
        </td>
      </tr>
    </table>

    {{-- Gambar produk/paket seperti Shopee --}}
    <div class="product-card">
      @if($imgSrc)
        <img src="{{ $imgSrc }}" alt="{{ $itemName }}">
      @else
        <div class="no-img">No Image</div>
      @endif
      <div class="prod-name">{{ $itemName }}</div>
      <div class="prod-meta">{{ $itemType }} &bull; {{ $itemCat }}</div>
      <div class="prod-price">Rp {{ number_format($itemPrice, 0, ',', '.') }}</div>
    </div>

    <div class="btn-wrap">
      <a href="{{ $orderUrl }}" class="btn">{{ __('Lihat Pesanan') }}</a>
    </div>

  </div>

  <div class="footer">
    {{ __('Email ini dikirim otomatis oleh sistem :app. Jangan balas email ini.', ['app' => $appName]) }}<br>
    {{ __('Pertanyaan? Hubungi kami di') }} {{ $adminEmail }}
  </div>

</div>
</body>
</html>
