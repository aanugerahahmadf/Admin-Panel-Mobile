<div
    class="relative inline-flex items-center justify-center w-7 h-7"
    x-data="{ open: false }"
    x-on:click.outside="open = false"
>
    <input x-ref="backCamera" type="file" class="sr-only" accept="{{ $cameraAccept }}" capture="environment" wire:model.live="cameraUpload">
    <input x-ref="frontCamera" type="file" class="sr-only" accept="{{ $cameraAccept }}" capture="user" wire:model.live="cameraUpload">
    <input x-ref="videoCamera" type="file" class="sr-only" accept="{{ $cameraAccept }}" capture="environment" wire:model.live="cameraUpload">
    <input x-ref="gallery" type="file" class="sr-only" accept="{{ $cameraAccept }}" wire:model.live="cameraUpload">

    @if($isLoading)
        <svg class="animate-spin w-[18px] h-[18px] text-primary-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
    @else
        <button
            type="button"
            id="cbir-camera-btn"
            x-on:click="open = ! open"
            title="{{ __('Kamera, Video, atau Galeri') }}"
            class="inline-flex items-center justify-center w-7 h-7 rounded-md
                   text-gray-400 hover:text-primary-500 dark:hover:text-primary-400
                   transition-all duration-150 active:scale-90 touch-manipulation
                   focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500"
        >
            <svg xmlns="http://www.w3.org/2000/svg" class="w-[18px] h-[18px]" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 0 1 5.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 0 0-1.134-.175 2.31 2.31 0 0 1-1.64-1.055l-.822-1.316a2.192 2.192 0 0 0-1.736-1.039 48.774 48.774 0 0 0-5.232 0 2.192 2.192 0 0 0-1.736 1.039l-.821 1.316Z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0ZM18.75 10.5h.008v.008h-.008V10.5Z" />
            </svg>
        </button>
    @endif

    <div
        x-cloak
        x-show="open"
        x-transition.origin.top.right
        class="absolute right-0 top-8 z-50 w-48 overflow-hidden rounded-lg bg-white py-1 text-sm shadow-lg ring-1 ring-gray-950/10 dark:bg-gray-900 dark:ring-white/10"
    >
        <button type="button" @if($isNative) wire:click="openCamera('photo-back')" x-on:click="open = false" @else x-on:click="$refs.backCamera.click(); open = false" @endif class="flex w-full items-center gap-2 px-3 py-2 text-left text-gray-700 hover:bg-gray-50 dark:text-gray-200 dark:hover:bg-white/10">
            <x-heroicon-m-camera class="h-4 w-4" />
            <span>{{ __('Foto Kamera Belakang') }}</span>
        </button>
        <button type="button" @if($isNative) wire:click="openCamera('photo-front')" x-on:click="open = false" @else x-on:click="$refs.frontCamera.click(); open = false" @endif class="flex w-full items-center gap-2 px-3 py-2 text-left text-gray-700 hover:bg-gray-50 dark:text-gray-200 dark:hover:bg-white/10">
            <x-heroicon-m-user-circle class="h-4 w-4" />
            <span>{{ __('Foto Kamera Depan') }}</span>
        </button>
        <button type="button" @if($isNative) wire:click="openCamera('video')" x-on:click="open = false" @else x-on:click="$refs.videoCamera.click(); open = false" @endif class="flex w-full items-center gap-2 px-3 py-2 text-left text-gray-700 hover:bg-gray-50 dark:text-gray-200 dark:hover:bg-white/10">
            <x-heroicon-m-video-camera class="h-4 w-4" />
            <span>{{ __('Rekam Video') }}</span>
        </button>
        <button type="button" @if($isNative) wire:click="openCamera('gallery')" x-on:click="open = false" @else x-on:click="$refs.gallery.click(); open = false" @endif class="flex w-full items-center gap-2 px-3 py-2 text-left text-gray-700 hover:bg-gray-50 dark:text-gray-200 dark:hover:bg-white/10">
            <x-heroicon-m-photo class="h-4 w-4" />
            <span>{{ __('Pilih dari Galeri') }}</span>
        </button>
    </div>

    @if($statusMessage || ! empty($recentUploads))
        <div class="absolute right-0 top-8 z-40 mt-40 w-56 rounded-lg bg-white p-2 text-xs shadow-lg ring-1 ring-gray-950/10 dark:bg-gray-900 dark:text-gray-200 dark:ring-white/10">
            @if($statusMessage)
                <div class="mb-2 text-gray-600 dark:text-gray-300">{{ $statusMessage }}</div>
            @endif
            <div class="grid grid-cols-3 gap-1">
                @foreach($recentUploads as $upload)
                    @if(str_starts_with($upload['mime'] ?? '', 'image/'))
                        <img src="{{ $upload['url'] }}" alt="{{ $upload['name'] }}" class="h-12 w-full rounded object-cover">
                    @elseif(str_starts_with($upload['mime'] ?? '', 'video/'))
                        <video src="{{ $upload['url'] }}" class="h-12 w-full rounded object-cover" muted controls></video>
                    @else
                        <div class="flex h-12 items-center justify-center rounded bg-gray-100 px-1 text-[10px] dark:bg-white/10">{{ pathinfo($upload['name'], PATHINFO_EXTENSION) }}</div>
                    @endif
                @endforeach
            </div>
        </div>
    @endif
</div>
