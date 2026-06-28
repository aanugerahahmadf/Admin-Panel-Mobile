@php
    $cameraAccept = $cameraAccept ?? 'image/jpeg,image/png,image/webp,image/heic,image/heif,video/mp4,video/quicktime,video/x-msvideo,video/x-matroska,.jpg,.jpeg,.png,.webp,.heic,.mp4,.mov,.avi,.mkv';
@endphp

{{-- Hidden file inputs for web video/gallery (triggered from inside TakePicture modal) --}}
@unless ($isNative ?? false)
    <div
        class="sr-only"
        x-data="{}"
        x-on:cbir-pick-video.window="$refs.videoCamera.click()"
        x-on:cbir-pick-gallery.window="$refs.gallery.click()"
    >
        <input x-ref="videoCamera" type="file" accept="video/*" capture="environment" wire:model.live="cameraUpload">
        <input x-ref="gallery" type="file" accept="{{ $cameraAccept }}" wire:model.live="cameraUpload">
    </div>
@endunless

{{-- Native app: satu tombol → modal berisi 4 opsi --}}
@if ($isNative ?? false)
    <div x-data="{ open: false }" class="py-2">
        @if ($isProcessing ?? false)
            <div class="flex items-center justify-center gap-2 py-8 text-sm text-gray-500 dark:text-gray-400">
                <svg class="h-5 w-5 animate-spin text-primary-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span>{{ __('Memproses...') }}</span>
            </div>
        @else
            <button
                type="button"
                x-on:click="open = true"
                class="relative w-full rounded-lg border-2 border-dashed border-gray-300 bg-white py-8 shadow-sm transition-all hover:border-primary-500 hover:bg-gray-50 dark:border-white/10 dark:bg-white/5 dark:hover:border-primary-400 dark:hover:bg-white/10"
            >
                <div class="flex flex-col items-center justify-center px-4">
                    <div class="mb-3 rounded-full bg-primary-500/10 p-3">
                        <x-filament::icon icon="heroicon-o-camera" class="h-6 w-6 text-primary-500" />
                    </div>
                    <p class="text-sm font-medium text-gray-900 dark:text-white">{{ __('Klik untuk mengambil foto') }}</p>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-300">{{ __('Kamera, video, atau galeri') }}</p>
                </div>
            </button>
        @endif

        <template x-teleport="body">
            <div
                x-cloak
                x-show="open"
                x-transition.opacity
                x-on:keydown.escape.window="open = false"
                class="fixed inset-0 z-50 flex items-end justify-center bg-gray-950/60 p-0 sm:items-center sm:p-4"
                x-on:click.self="open = false"
            >
                <div
                    x-show="open"
                    x-transition
                    x-on:click.stop
                    class="w-full max-w-xl rounded-t-2xl bg-white shadow-xl ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 sm:rounded-2xl"
                >
                    <div class="flex items-center justify-between border-b border-gray-200 px-5 py-4 dark:border-white/10">
                        <h3 class="text-base font-semibold text-gray-950 dark:text-white">{{ __('Ambil Foto') }}</h3>
                        <button type="button" x-on:click="open = false" class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300">
                            <x-filament::icon icon="heroicon-m-x-mark" class="h-5 w-5" />
                        </button>
                    </div>

                    <div class="grid grid-cols-2 gap-2 p-4">
                        @foreach ([
                            ['mode' => 'photo-back', 'icon' => 'heroicon-o-camera', 'label' => __('Foto Kamera Belakang')],
                            ['mode' => 'photo-front', 'icon' => 'heroicon-o-user-circle', 'label' => __('Foto Kamera Depan')],
                            ['mode' => 'video', 'icon' => 'heroicon-o-video-camera', 'label' => __('Rekam Video')],
                            ['mode' => 'gallery', 'icon' => 'heroicon-o-photo', 'label' => __('Pilih dari Galeri')],
                        ] as $option)
                            <button
                                type="button"
                                wire:click="openCamera('{{ $option['mode'] }}')"
                                x-on:click="open = false"
                                class="flex flex-col items-center gap-2 rounded-xl border border-gray-200 bg-gray-50 px-3 py-4 text-center transition-all hover:border-primary-400 hover:bg-primary-50 active:scale-95 dark:border-white/10 dark:bg-white/5 dark:hover:border-primary-500 dark:hover:bg-primary-500/10"
                            >
                                <x-filament::icon :icon="$option['icon']" class="h-6 w-6 text-primary-500" />
                                <span class="text-xs font-medium text-gray-900 dark:text-white">{{ $option['label'] }}</span>
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>
        </template>
    </div>
@endif
