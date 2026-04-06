@php
    $currentLocale = app()->getLocale();
    $locals = config('filament-language-switcher.locals');
    $currentFlag = $locals[$currentLocale]['flag'] ?? ($locals[config('app.fallback_locale')]['flag'] ?? 'us');
    $isFilament = str_contains(request()->url(), config('filament.path', 'admin')) || request()->routeIs('filament.*');
@endphp

<div x-data="{
    open: false,
    toggle: function() {
        this.open = !this.open
    },
    close: function() {
        this.open = false
    },
}" class="relative">
    {{-- Trigger Button --}}
    <button type="button" id="filament-language-switcher" x-on:click="toggle" @class([
        'flex items-center justify-center gap-2 rounded-md px-2 transition hover:bg-gray-500/5 focus:bg-gray-500/5 dark:hover:bg-white/5 dark:focus:bg-white/5',
        'h-10 min-w-10 ring-1 ring-gray-950/10 dark:ring-white/20',
    ])
        x-tooltip="{
            content: '{{ __('Change Language') }}',
            theme: document.documentElement.classList.contains('dark') ? 'dark' : 'light',
        }">
        <div class="w-6 h-4 bg-cover bg-center rounded-sm shadow-sm border border-gray-200 dark:border-gray-700 shrink-0"
            style="background-image: url('https://cdn.jsdelivr.net/gh/hampusborgos/country-flags@main/svg/{{ $currentFlag }}.svg')">
        </div>
        <span @class([
            'text-xs font-bold uppercase',
            'text-gray-700 dark:text-gray-200' => $isFilament,
            'text-[#1b1b18] dark:text-[#EDEDEC]' => !$isFilament,
        ])>
            {{ $currentLocale === 'en_US' ? 'US' : ($currentLocale === 'en' ? 'UK' : strtoupper($currentLocale)) }}
        </span>
    </button>

    {{-- Dropdown Panel --}}
    <div
        x-ref="panel"
        x-show="open"
        x-on:click.away="close"
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        style="z-index: 2000; max-height: 260px; overflow-y: auto;"
        @class([
            'ffi-dropdown-panel absolute right-0 top-full mt-2 min-w-[200px] divide-y rounded-lg shadow-2xl ring-1',
            $isFilament
                ? 'bg-white divide-gray-100 ring-gray-950/10 dark:bg-gray-900 dark:divide-white/5 dark:ring-white/20'
                : 'bg-white divide-gray-100 ring-gray-950/10 dark:bg-gray-900 dark:divide-white/5 dark:ring-white/20',
        ])
        x-cloak>
        <div class="filament-dropdown-list p-1 w-full">
            @foreach ($locals as $key => $language)
                @php $isCurrent = $currentLocale === $key; @endphp
                <a @if (!$isCurrent) href="{{ route('language.switch', ['locale' => $key]) }}"
                    @else
                        href="javascript:void(0)" @endif
                    @class([
                        'filament-dropdown-list-item filament-dropdown-item group flex items-center w-full justify-between gap-3 whitespace-nowrap rounded-md p-2 text-sm outline-none',
                        // Hover & Colors for non-current
                        'text-gray-500 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-white/5 focus:bg-gray-50 dark:focus:bg-white/5' => !$isCurrent,
                        // Colors for current
                        'bg-gray-50 dark:bg-white/5 text-primary-600 dark:text-primary-400 font-semibold cursor-default' => $isCurrent,
                    ])>
                    {{-- Label --}}
                    <span class="truncate flex-1 text-start">
                        {{ str_replace('.', '', __($language['label'])) }}
                    </span>

                    {{-- Flag --}}
                    <div class="w-6 h-4 shrink-0 bg-cover bg-center rounded-sm border border-gray-200 dark:border-gray-700 shadow-sm"
                        style="background-image: url('https://cdn.jsdelivr.net/gh/hampusborgos/country-flags@main/svg/{{ $language['flag'] }}.svg'); background-repeat: no-repeat">
                    </div>
                </a>
            @endforeach
        </div>
    </div>
</div>