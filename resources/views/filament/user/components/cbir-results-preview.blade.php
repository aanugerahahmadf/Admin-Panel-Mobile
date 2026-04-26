@php
    $results = session('cbir_mixed_results', []);
    $topMatch = collect($results)->first();
    $topScore = $topMatch['similarity'] ?? 0;
@endphp

@if(count($results) > 0)
<div class="mt-4 animate-in fade-in slide-in-from-bottom-4 duration-500">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-4 px-1">
        <div class="flex items-center gap-2">
            <x-filament::icon icon="heroicon-s-sparkles" class="w-5 h-5 text-amber-500 shadow-sm" />
            <span class="text-sm font-black text-gray-900 dark:text-white uppercase tracking-tighter">{{ __('Hasil Visual Terbaik') }}</span>
        </div>
        <div class="flex gap-2 items-center">
            <span class="text-[10px] font-medium text-gray-400 dark:text-gray-500">{{ session('cbir_search_time', 0) }}s</span>
            <x-filament::badge color="info" size="sm">
                {{ count($results) }} {{ __('kecocokan') }}
            </x-filament::badge>
        </div>
    </div>

    {{-- Top Match Highlight --}}
    @if($topScore >= 70)
    <div class="mb-4">
        <x-filament::section compact>
            <div class="flex items-center gap-3">
                <div class="p-2 bg-amber-500/10 rounded-xl">
                    <x-filament::icon icon="heroicon-s-fire" class="w-5 h-5 text-amber-500" />
                </div>
                <div>
                    <p class="text-[10px] text-gray-500 uppercase font-bold tracking-widest">{{ __('Rekomendasi Utama') }}</p>
                    <p class="text-sm font-semibold text-gray-900 dark:text-white">
                        {{ number_format($topScore, 1) }}% {{ __('kemiripan visual ditemukan') }}
                    </p>
                </div>
            </div>
        </x-filament::section>
    </div>
    @endif

    {{-- Mixed Result Cards --}}
    <div class="space-y-4">
        @foreach($results as $res)
        @php
            $type = $res['type'] ?? 'product';
            $data = $res['data'] ?? [];
            $score = $res['similarity'] ?? 0;
            $pct = number_format($score, 1);
            $badgeColor = $score >= 85 ? 'success' : ($score >= 65 ? 'warning' : 'gray');
            
            $url = $type === 'package' 
                ? route('filament.user.resources.packages.index', ['cbir_id' => $data['id'] ?? 0])
                : route('filament.user.resources.products.index', ['cbir_id' => $data['id'] ?? 0]);
        @endphp

        <x-filament::section compact class="relative overflow-hidden group hover:ring-2 hover:ring-primary-500/50 transition-all duration-300">
            {{-- Accuracy Badge --}}
            <div class="absolute top-0 right-0 p-3 flex flex-col items-end gap-1 z-10">
                <x-filament::badge color="{{ $badgeColor }}" size="sm" class="font-bold">
                    {{ $pct }}%
                </x-filament::badge>
                <x-filament::badge color="{{ $type === 'package' ? 'info' : 'gray' }}" size="xs" class="text-[10px] font-bold">
                    {{ ucfirst(__($type)) }}
                </x-filament::badge>
            </div>

            <div class="flex gap-4">
                {{-- Result Image --}}
                <div class="relative w-24 h-24 shrink-0 rounded-2xl overflow-hidden bg-gray-100 dark:bg-gray-800 shadow-inner">
                    <img
                        src="{{ str_starts_with($data['image_url'] ?? '', 'http') ? $data['image_url'] : asset('storage/' . ($data['image_url'] ?? '')) }}"
                        alt="{{ $data['name'] ?? '' }}"
                        class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700"
                        onerror="this.src='{{ asset('images/placeholders/image-placeholder.svg') }}'"
                    />
                    
                    {{-- Image only now --}}

                    {{-- Small Progress Bar --}}
                    <div class="absolute bottom-0 left-0 right-0 h-1.5 bg-gray-200/30 backdrop-blur-sm">
                        <div class="h-full bg-linear-to-r from-amber-500 to-primary-500 transition-all duration-1000 ease-out" style="width: {{ $pct }}%"></div>
                    </div>
                </div>

                {{-- Info --}}
                <div class="flex-1 min-w-0 pr-12 flex flex-col justify-between">
                    <div>
                        @if($data['category'] ?? null)
                            <p class="text-[9px] font-black text-primary-600 dark:text-primary-400 uppercase tracking-widest mb-1">{{ is_array($data['category']) ? ($data['category']['name'] ?? '') : $data['category'] }}</p>
                        @endif

                        <h4 class="text-base font-bold text-gray-900 dark:text-white leading-tight truncate">
                            {{ $data['name'] ?? '' }}
                        </h4>

                        <div class="flex items-center gap-1.5 mt-1.5">
                            <x-filament::icon icon="heroicon-s-building-storefront" class="w-3.5 h-3.5 text-gray-400 group-hover:text-primary-500 transition-colors" />
                            <p class="text-xs text-gray-500 dark:text-gray-400 truncate">
                                {{ $data['wedding_organizer']['name'] ?? '' }}
                            </p>
                        </div>
                    </div>

                    <div class="mt-2 flex items-baseline gap-2">
                        @if(($data['discount_price'] ?? 0) > 0)
                            <span class="text-lg font-black text-primary-600 dark:text-primary-400">
                                Rp {{ number_format($data['discount_price'], 0, ',', '.') }}
                            </span>
                            <span class="text-xs text-gray-400 line-through decoration-red-500/50">
                                Rp {{ number_format($data['price'] ?? 0, 0, ',', '.') }}
                            </span>
                        @else
                            <span class="text-lg font-black text-primary-600 dark:text-primary-400">
                                Rp {{ number_format($data['price'] ?? 0, 0, ',', '.') }}
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Actions Row --}}
            <div class="mt-4 flex items-center gap-2">
                <x-filament::icon-button
                    icon="{{ ($data['is_wishlisted'] ?? false) ? 'heroicon-s-heart' : 'heroicon-o-heart' }}"
                    color="{{ ($data['is_wishlisted'] ?? false) ? 'danger' : 'gray' }}"
                    size="md"
                    tooltip="{{ __('Wishlist') }}"
                    wire:click="toggleWishlist({{ $data['id'] ?? 0 }})"
                    class="bg-gray-100 dark:bg-gray-800 rounded-xl"
                />

                <x-filament::icon-button
                    icon="heroicon-o-shopping-cart"
                    color="primary"
                    size="md"
                    tooltip="{{ __('Masukkan ke Keranjang') }}"
                    wire:click="addToCart({{ $data['id'] ?? 0 }})"
                    class="bg-gray-100 dark:bg-gray-800 rounded-xl"
                />

                <x-filament::button
                    href="{{ $url }}"
                    tag="a"
                    color="gray"
                    icon="heroicon-m-eye"
                    size="sm"
                    outlined
                    class="rounded-xl flex-1"
                >
                    {{ __('Detail') }}
                </x-filament::button>

                <x-filament::button
                    wire:click="bookNow({{ $data['id'] ?? 0 }})"
                    color="success"
                    icon="heroicon-m-bolt"
                    size="sm"
                    class="rounded-xl flex-1 shadow-lg shadow-success-500/20 font-bold"
                >
                    {{ __('Pesan') }}
                </x-filament::button>
            </div>
        </x-filament::section>
        @endforeach
    </div>

    {{-- Reset Button --}}
    <div class="mt-6">
        <x-filament::button
            wire:click="clearVisualSearch"
            color="gray"
            size="lg"
            icon="heroicon-m-arrow-path"
            class="w-full rounded-2xl font-bold"
        >
            {{ __('Bersihkan Hasil Pencarian') }}
        </x-filament::button>
    </div>

</div>
@endif

