@php
    $allResults = session('cbir_mixed_results', []);
    $context    = session('cbir_context'); // 'package', 'product', or null

    // Filter to only show the relevant type when context is set
    $results = $context
        ? collect($allResults)->filter(fn ($r) => ($r['type'] ?? '') === $context)->values()->all()
        : $allResults;

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
    <div class="space-y-3">
        @foreach($results as $res)
        @php
            $type = $res['type'] ?? 'product';
            $data = $res['data'] ?? [];
            $score = $res['similarity'] ?? 0;
            $pct = number_format($score, 1);
            $badgeColor = $score >= 85 ? 'success' : ($score >= 65 ? 'warning' : 'gray');

            $url = $type === 'package'
                ? route('filament.user.resources.packages.view', ['record' => $data['id'] ?? 0])
                : route('filament.user.resources.products.view', ['record' => $data['id'] ?? 0]);
        @endphp

        {{-- Card: fully in-flow, no absolute badge --}}
        <div class="relative group rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-gray-900 shadow-sm hover:shadow-md hover:ring-2 hover:ring-primary-500/40 transition-all duration-300 overflow-hidden">

            {{-- Clickable overlay --}}
            <a href="{{ $url }}" class="absolute inset-0 z-10" aria-label="{{ $data['name'] ?? '' }}"></a>

            <div class="flex gap-3 p-3">

                {{-- Thumbnail --}}
                <div class="relative w-20 h-20 shrink-0 rounded-xl overflow-hidden bg-gray-100 dark:bg-gray-800 shadow-inner self-center">
                    <img
                        src="{{ str_starts_with($data['image_url'] ?? '', 'http') ? $data['image_url'] : asset('storage/' . ($data['image_url'] ?? '')) }}"
                        alt="{{ $data['name'] ?? '' }}"
                        class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700"
                        onerror="this.src='{{ asset('images/placeholders/image-placeholder.svg') }}'"
                        loading="lazy"
                    />
                    {{-- Score bar at bottom of image --}}
                    <div class="absolute bottom-0 left-0 right-0 h-1 bg-gray-200/30">
                        <div class="h-full bg-gradient-to-r from-amber-500 to-primary-500 transition-all duration-1000 ease-out" style="width: {{ $pct }}%"></div>
                    </div>
                </div>

                {{-- Info column --}}
                <div class="flex-1 min-w-0 flex flex-col justify-between gap-1">

                    {{-- Top row: badges inline, never overlapping --}}
                    <div class="flex items-center gap-1.5 flex-wrap">
                        <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-[10px] font-bold leading-none
                            @if($score >= 85) bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-400
                            @elseif($score >= 65) bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-400
                            @else bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400
                            @endif">
                            {{ $pct }}%
                        </span>
                        @if($data['category'] ?? null)
                            <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-[10px] font-medium leading-none bg-primary-50 text-primary-600 dark:bg-primary-900/30 dark:text-primary-400 truncate max-w-[80px]">
                                {{ is_array($data['category']) ? ($data['category']['name'] ?? '') : $data['category'] }}
                            </span>
                        @endif
                    </div>

                    {{-- Name --}}
                    <h4 class="text-sm font-bold text-gray-900 dark:text-white leading-snug line-clamp-2">
                        {{ $data['name'] ?? '' }}
                    </h4>

                    {{-- Organizer --}}
                    @if($data['wedding_organizer']['name'] ?? null)
                    <div class="flex items-center gap-1">
                        <x-filament::icon icon="heroicon-s-building-storefront" class="w-3 h-3 text-gray-400 shrink-0" />
                        <p class="text-[11px] text-gray-500 dark:text-gray-400 truncate">
                            {{ $data['wedding_organizer']['name'] }}
                        </p>
                    </div>
                    @endif

                    {{-- Price --}}
                    <div class="flex items-baseline gap-1.5 flex-wrap">
                        @if(($data['discount_price'] ?? 0) > 0)
                            <span class="text-sm font-black text-primary-600 dark:text-primary-400">
                                Rp {{ number_format($data['discount_price'], 0, ',', '.') }}
                            </span>
                            <span class="text-[11px] text-gray-400 line-through">
                                Rp {{ number_format($data['price'] ?? 0, 0, ',', '.') }}
                            </span>
                        @else
                            <span class="text-sm font-black text-primary-600 dark:text-primary-400">
                                Rp {{ number_format($data['price'] ?? 0, 0, ',', '.') }}
                            </span>
                        @endif
                    </div>

                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Reset Button --}}
    <div class="mt-5">
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
