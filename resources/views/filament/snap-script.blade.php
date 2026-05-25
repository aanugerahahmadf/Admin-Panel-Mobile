<!-- Midtrans Snap Script - Optimized for Filament -->
<script type="text/javascript"
  src="{{ config('midtrans.snap_url') }}"
  data-client-key="{{ config('midtrans.client_key') }}"></script>

<script type="text/javascript">
  var isNativeMobile = @json(\App\Providers\NativeServiceProvider::isNativeMobile());

  window.addEventListener('open-midtrans-snap', function (event) {
    const detail = event.detail || {};
    const token = detail.token || (Array.isArray(detail) ? detail[0]?.token : null);

    if (token) {
      console.log('[Midtrans] Membuka Pembayaran dengan Token:', token);

      if (isNativeMobile) {
        var container = document.getElementById('snap-container');
        if (container) {
          container.style.display = 'block';
          container.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
        window.snap.embed(token, {
          embedId: 'snap-container',
          onSuccess: function(result) {
            console.log('[Midtrans] Success:', result);
            window.location.href = '/user/orders';
          },
          onPending: function(result) {
            console.log('[Midtrans] Pending:', result);
            window.location.href = '/user/orders';
          },
          onError: function(result) {
            console.log('[Midtrans] Error:', result);
          },
          onClose: function() {
            console.log('[Midtrans] Embed ditutup');
          }
        });
      } else {
        window.snap.pay(token, {
          onSuccess: function(result) {
            console.log('[Midtrans] Success:', result);
            alert("{{ __('Pembayaran Berhasil!') }}");
            window.location.href = '/user/orders';
          },
          onPending: function(result) {
            console.log('[Midtrans] Pending:', result);
            alert("{{ __('Menunggu pembayaran Anda!') }}");
            window.location.href = '/user/orders';
          },
          onError: function(result) {
            console.log('[Midtrans] Error:', result);
            alert("{{ __('Pembayaran Gagal!') }}");
          },
          onClose: function() {
            console.log('[Midtrans] Modal ditutup tanpa menyelesaikan pembayaran');
          }
        });
      }
    } else {
      console.error('[Midtrans] Error: Token tidak ditemukan!');
    }
  });
</script>