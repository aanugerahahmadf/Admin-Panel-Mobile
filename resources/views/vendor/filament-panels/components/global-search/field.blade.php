@php
    $debounce = filament()->getGlobalSearchDebounce();
    $keyBindings = filament()->getGlobalSearchKeyBindings();
    $suffix = filament()->getGlobalSearchFieldSuffix();
    $isUserPanel = filament()->getCurrentPanel()?->getId() === 'user';
@endphp

<div
    x-id="['input']"
    {{ $attributes->class(['fi-global-search-field']) }}
>
    <label x-bind:for="$id('input')" class="sr-only">
        {{ __('filament-panels::global-search.field.label') }}
    </label>

    <div class="fi-input-wrp flex items-center rounded-lg shadow-sm ring-1 ring-gray-950/10 dark:ring-white/20 bg-white dark:bg-white/5">
        {{-- Prefix icon --}}
        <div class="flex items-center ps-3 text-gray-400 dark:text-gray-500 shrink-0">
            <x-filament::icon
                alias="panels::global-search.field"
                icon="heroicon-m-magnifying-glass"
                class="h-5 w-5"
            />
        </div>

        {{-- Input --}}
        <x-filament::input
            autocomplete="off"
            inline-prefix
            maxlength="1000"
            :placeholder="__('filament-panels::global-search.field.placeholder')"
            type="search"
            wire:key="global-search.field.input"
            x-bind:id="$id('input')"
            x-on:keydown.down.prevent.stop="$dispatch('focus-first-global-search-result')"
            x-data="{}"
            class="flex-1 min-w-0 border-0 bg-transparent py-2 ps-2 pe-2 text-sm text-gray-950 placeholder:text-gray-400 focus:ring-0 dark:text-white dark:placeholder:text-gray-500"
            :attributes="
                \Filament\Support\prepare_inherited_attributes(
                    new \Illuminate\View\ComponentAttributeBag([
                        'wire:model.live.debounce.' . $debounce => 'search',
                        'x-mousetrap.global.' . collect($keyBindings)->map(fn (string $keyBinding): string => str_replace('+', '-', $keyBinding))->implode('.') => $keyBindings ? 'document.getElementById($id(\'input\')).focus()' : null,
                    ])
                )
            "
        />

        {{-- CBIR buttons — only in user panel --}}
        @if($isUserPanel)
            <div class="flex items-center gap-0.5 pe-2 ps-2 shrink-0 self-stretch" style="border-left:1px solid rgba(156,163,175,0.4);">
                <a href="{{ route('filament.user.pages.cbir-search', ['mode' => 'camera']) }}"
                   wire:navigate
                   title="{{ __('Cari dengan Kamera') }}"
                   class="inline-flex items-center justify-center w-7 h-7 rounded-md text-gray-400 hover:text-primary-500 dark:hover:text-primary-400 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-[18px] h-[18px]" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 0 1 5.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 0 0-1.134-.175 2.31 2.31 0 0 1-1.64-1.055l-.822-1.316a2.192 2.192 0 0 0-1.736-1.039 48.774 48.774 0 0 0-5.232 0 2.192 2.192 0 0 0-1.736 1.039l-.821 1.316Z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0ZM18.75 10.5h.008v.008h-.008V10.5Z" />
                    </svg>
                </a>
                <a href="{{ route('filament.user.pages.cbir-search', ['mode' => 'upload']) }}"
                   wire:navigate
                   title="{{ __('Cari dengan Foto') }}"
                   class="inline-flex items-center justify-center w-7 h-7 rounded-md text-gray-400 hover:text-primary-500 dark:hover:text-primary-400 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-[18px] h-[18px]" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                    </svg>
                </a>
            </div>
        @endif

        {{-- Original suffix if any --}}
        @if($suffix)
            <div class="pe-3 text-sm text-gray-500 dark:text-gray-400 shrink-0">{{ $suffix }}</div>
        @endif
    </div>
</div>