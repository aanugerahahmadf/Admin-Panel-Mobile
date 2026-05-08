@php
    use App\Providers\NativeServiceProvider;

    $currentLocale = app()->getLocale();
    $locals = config('filament-language-switcher.locals', []);
    $isMobileApp = NativeServiceProvider::isNativeMobile();
    $isMobileBrowser = (bool) preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino/i', request()->userAgent() ?? '');

    // Sembunyikan language switcher di NativePHP mobile DAN mobile browser
    if ($isMobileApp || $isMobileBrowser) {
        return;
    }

    $emojiFlags = [
        'id' => '🇮🇩',
        'en' => '🇬🇧',
        'en_US' => '🇺🇸',
        'ar' => '🇸🇦',
        'de' => '🇩🇪',
        'es' => '🇪🇸',
        'fr' => '🇫🇷',
        'it' => '🇮🇹',
        'ja' => '🇯🇵',
        'ko' => '🇰🇷',
        'zh' => '🇨🇳',
        'ru' => '🇷🇺',
    ];

    $currentEmoji = $emojiFlags[$currentLocale] ?? '🌐';
    $currentLabel = match ($currentLocale) {
        'en_US' => 'US',
        'en' => 'UK',
        default => strtoupper($currentLocale),
    };

    $isAdmin = str_contains(request()->url(), '/admin');
    $isUser = str_contains(request()->url(), '/user');
    $activeColorClass = 'text-[#e91e63]';
    if ($isAdmin)
        $activeColorClass = 'text-[#6366f1]';
    if ($isUser)
        $activeColorClass = 'text-[#fbbf24]';
@endphp

{{-- ── MOBILE: Filament native select (no custom dropdown, no scroll issue) ── --}}
@if($isMobileApp)
    <div x-data="{ locale: '{{ $currentLocale }}' }" class="relative inline-flex items-center" x-tooltip="{
                content: '{{ __('Change Language') }}',
                theme: document.documentElement.classList.contains('dark') ? 'dark' : 'light'
            }">

        {{-- Visible label overlay (emoji + code) --}}
        <div class="pointer-events-none absolute inset-0 flex items-center justify-center gap-1 z-10">
            <span class="text-sm leading-none">{{ $currentEmoji }}</span>
            <span @class(['text-[10px] font-bold uppercase tracking-wider', $activeColorClass])>
                {{ $currentLabel }}
            </span>
        </div>

        {{-- Native select — invisible but functional --}}
        <x-filament::input.wrapper class="opacity-0 absolute inset-0 w-full h-full">
            <x-filament::input.select x-model="locale"
                x-on:change="window.location.href = '{{ NativeServiceProvider::normalizeUrl(url('/language/switch')) }}/' + locale"
                class="!opacity-0 !absolute !inset-0 !w-full !h-full !cursor-pointer">
                @foreach($locals as $key => $language)
                    <option value="{{ $key }}" @selected($currentLocale === $key)>
                        {{ $emojiFlags[$key] ?? '🌐' }} {{ __($language['label']) }}
                    </option>
                @endforeach
            </x-filament::input.select>
        </x-filament::input.wrapper>

        {{-- Visible button frame --}}
        <div @class([
            'flex items-center justify-center gap-1 h-8 px-2 rounded-md ring-1 ring-gray-950/10 dark:ring-white/20',
            'hover:bg-yellow-400 transition cursor-pointer',
        ]) style="min-width: 3rem;">
            <span class="text-sm leading-none">{{ $currentEmoji }}</span>
            <span @class(['text-[10px] font-bold uppercase tracking-wider', $activeColorClass])>
                {{ $currentLabel }}
            </span>
        </div>
    </div>

    {{-- ── WEB: Custom dropdown dengan CDN flag images ── --}}
@else
<div x-data="{
        isLanguageSwitcherOpen: false,
        toggleDropdown() { this.isLanguageSwitcherOpen = !this.isLanguageSwitcherOpen },
        closeDropdown() { this.isLanguageSwitcherOpen = false },
    }" class="relative inline-block text-left">

    {{-- Trigger Button --}}
    <button type="button" id="filament-language-switcher" x-on:click="toggleDropdown()"
        class="flex items-center justify-center gap-2 h-10 px-3 min-w-10 rounded-md ring-1 ring-gray-950/10 dark:ring-white/20 transition hover:bg-gray-50 dark:hover:bg-white/5"
        x-tooltip="{
                content: '{{ __('Change Language') }}',
                theme: document.documentElement.classList.contains('dark') ? 'dark' : 'light'
            }">
        <div class="w-6 h-4 bg-cover bg-center rounded-sm shadow-sm border border-gray-200 dark:border-gray-700 shrink-0"
            style="background-image: url('https://cdn.jsdelivr.net/gh/hampusborgos/country-flags@main/svg/{{ $locals[$currentLocale]['flag'] ?? 'gb' }}.svg')">
        </div>
            <span @class(['text-xs font-bold uppercase tracking-wider', $activeColorClass])>
            {{ $currentLabel }}
        </span>
    </button>

    {{-- Dropdown Panel --}}
    <div x-show="isLanguageSwitcherOpen" x-on:click.away="closeDropdown()"
        x-transition:enter="transition ease-out duration-100" x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
        class="lang-dd absolute right-0 top-full mt-2 divide-y rounded-lg shadow-2xl ring-1 bg-white divide-gray-100 ring-gray-950/10 dark:bg-gray-900 dark:divide-white/5 dark:ring-white/20"
        style="z-index:2000; min-width:200px; max-height:220px; overflow-y:scroll; scrollbar-width:none; -ms-overflow-style:none;"
        x-cloak>
        <style>
            .lang-dd::-webkit-scrollbar {
                display: none !important;
                width: 0 !important;
            }
        </style>
        <div class="p-1 w-full">
            @foreach($locals as $key => $language)
                @php
                    $isCurrent = $currentLocale === $key;
                    $flag = $language['flag'] ?? 'gb';
                    $label = match ($key) { 'en_US' => 'US', 'en' => 'UK', default => strtoupper($key)};
                @endphp
                <a href="{{ $isCurrent ? 'javascript:void(0)' : NativeServiceProvider::normalizeUrl(route('language.switch', ['locale' => $key])) }}" @class([
                    'group flex items-center w-full justify-between gap-3 whitespace-nowrap rounded-md p-2 text-sm outline-none transition-all',
                    'text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white hover:bg-gray-50 dark:hover:bg-white/5' => !$isCurrent,
                        "$activeColorClass font-bold cursor-default" => $isCurrent,
                ])>
                    <span class="truncate flex-1 text-start">{{ __($language['label']) }}</span>
                    <div class="w-6 h-4 shrink-0 bg-cover bg-center rounded-sm border border-gray-200 dark:border-gray-700 shadow-sm"
                        style="background-image: url('https://cdn.jsdelivr.net/gh/hampusborgos/country-flags@main/svg/{{ $flag }}.svg');">
                    </div>
                </a>
            @endforeach
        </div>
    </div>
</div>
@endif