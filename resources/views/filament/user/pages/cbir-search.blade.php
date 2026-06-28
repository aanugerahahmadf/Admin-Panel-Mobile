<x-filament-panels::page>
    <div class="space-y-4">
        {{ $this->form }}
    </div>
    <x-filament-actions::modals />

    @if ($this->mode === 'upload')
        @persist('cbir-browse-modal')
            @include('filament.user.components.cbir-browse-modal', [
                'isNative' => \App\Support\PlatformContext::cbirCameraMode() === 'native',
                'browseAccept' => 'image/jpeg,image/png,image/webp,image/heic,image/heif,video/mp4,video/quicktime,video/x-msvideo,video/x-matroska,.jpg,.jpeg,.png,.webp,.heic,.mp4,.mov,.avi,.mkv,.webm',
            ])
        @endpersist
    @endif

    @if ($this->mode === 'camera' && \App\Support\PlatformContext::cbirCameraMode() !== 'native')
        <script>
            (function () {
                function shouldOpenCameraModal() {
                    const params = new URLSearchParams(window.location.search);

                    return window.location.pathname.includes('cbir-search')
                        && (params.get('mode') === 'camera' || ! params.has('mode'));
                }

                function openCbirCameraModal() {
                    if (! shouldOpenCameraModal()) {
                        return;
                    }

                    window.dispatchEvent(new CustomEvent('cbir-open-webrtc-camera', {
                        detail: { facing: 'environment' }
                    }));
                }

                function scheduleOpen() {
                    window.clearTimeout(window.__cbirCameraOpenTimer);
                    window.__cbirCameraOpenTimer = window.setTimeout(openCbirCameraModal, 450);
                }

                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', scheduleOpen, { once: true });
                } else {
                    scheduleOpen();
                }

                document.addEventListener('livewire:navigated', scheduleOpen);
            })();
        </script>
    @endif
</x-filament-panels::page>
