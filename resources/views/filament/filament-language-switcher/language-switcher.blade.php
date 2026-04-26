@php
    $currentLocale = app()->getLocale();
    $locals = config('filament-language-switcher.locals', []);
    $currentFlag = $locals[$currentLocale]['flag'] ?? ($locals[config('app.fallback_locale')]['flag'] ?? 'us');
    
    // Improved detection: check if we are in a filament panel route or path
    $isAdmin = str_contains(request()->url(), '/admin');
    $isUser = str_contains(request()->url(), '/user');
    $isFilament = $isAdmin || $isUser || request()->routeIs('filament.*');
    
    // Choose active color based on context
    $activeColorClass = 'text-[#e91e63]'; // Default Pink (Welcome)
    if ($isAdmin) $activeColorClass = 'text-[#6366f1]'; // Admin Indigo
    if ($isUser) $activeColorClass = 'text-[#fbbf24]'; // User Yellow
@endphp

<div x-data="{
    isLanguageSwitcherOpen: false,
    toggleDropdown: function() {
        this.isLanguageSwitcherOpen = !this.isLanguageSwitcherOpen
    },
    closeDropdown: function() {
        this.isLanguageSwitcherOpen = false
    },
}" class="relative inline-block text-left">
    {{-- Trigger Button --}}
    <button type="button" id="filament-language-switcher" x-on:click="toggleDropdown()" @class([

        'flex items-center justify-center gap-2 rounded-md px-3 transition hover:bg-yellow-400 hover:text-gray-900 focus:bg-yellow-400 focus:text-gray-900',
        'h-10 min-w-10 transition ring-1 ring-gray-950/10 dark:ring-white/20',
    ])
        x-tooltip="{
            content: '{{ __('Change Language') }}',
            theme: document.documentElement.classList.contains('dark') ? 'dark' : 'light'
        }">

        <div class="w-6 h-4 bg-cover bg-center rounded-sm shadow-sm border border-gray-200 dark:border-gray-700 shrink-0"
            style="background-image: url('https://cdn.jsdelivr.net/gh/hampusborgos/country-flags@main/svg/{{ $currentFlag }}.svg')">
        </div>
        <span @class([
            'text-xs font-bold uppercase tracking-wider',
            $activeColorClass,
        ])>
            {{ $currentLocale === 'en_US' ? 'US' : ($currentLocale === 'en' ? 'UK' : strtoupper($currentLocale)) }}
        </span>
    </button>

    {{-- Dropdown Panel --}}
    <div
        x-ref="panel"
        x-show="isLanguageSwitcherOpen"
        x-on:click.away="closeDropdown()"


        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        style="z-index: 2000; max-height: 260px; overflow-y: auto;"
        @class([
            'absolute right-0 top-full mt-2 min-w-[200px] divide-y rounded-lg shadow-2xl ring-1',
            'bg-white divide-gray-100 ring-gray-950/10 dark:bg-gray-900 dark:divide-white/5 dark:ring-white/20',
        ])
        x-cloak>
        <div class="p-1 w-full">
            @foreach ($locals as $key => $language)
                @php $isCurrent = $currentLocale === $key; @endphp
                <a @if (!$isCurrent) href="{{ route('language.switch', ['locale' => $key]) }}"
                    @else
                        href="javascript:void(0)" @endif
                    @class([
                        'group flex items-center w-full justify-between gap-3 whitespace-nowrap rounded-md p-2 text-sm outline-none transition-all',
                        'text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white hover:bg-gray-50 dark:hover:bg-white/5' => !$isCurrent,
                        "$activeColorClass font-bold cursor-default" => $isCurrent,
                    ])>
                    {{-- Label --}}
                    <span class="truncate flex-1 text-start">
                        {{ __($language['label']) }}
                    </span>

                    {{-- Flag --}}
                    <div class="w-6 h-4 shrink-0 bg-cover bg-center rounded-sm border border-gray-200 dark:border-gray-700 shadow-sm"
                        style="background-image: url('https://cdn.jsdelivr.net/gh/hampusborgos/country-flags@main/svg/{{ $language['flag'] }}.svg');">
                    </div>
                </a>
            @endforeach
        </div>
    </div>
</div>