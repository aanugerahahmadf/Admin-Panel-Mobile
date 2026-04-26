@php
    $isTerms = $active === 'terms';
    $isPrivacy = $active === 'privacy';
@endphp

<header class="sticky top-0 z-50 legal-glass transition-all duration-300">
    <div class="max-w-7xl mx-auto px-6 lg:px-12">
        <div class="flex items-center justify-between h-20">

            {{-- Left Side: Branding --}}
            <div class="flex items-center gap-8">
                <a href="{{ url('/') }}" class="flex items-center gap-3 group transition-transform hover:scale-[1.02]">
                    <div class="p-2 bg-primary-600/10 dark:bg-primary-400/10 rounded-xl">
                        <img src="{{ asset('images/logo.png') }}" alt="Logo" class="h-8 w-auto">
                    </div>
                    <div class="flex flex-col">
                        <span class="text-sm font-bold text-gray-900 dark:text-white leading-tight">
                            {{ __('Dekorasi Bunga Pernikahan') }}
                        </span>
                        <span
                            class="text-[10px] font-medium text-gray-500 dark:text-gray-400 uppercase tracking-widest leading-tight">
                            {{ __('Legal Documentation') }}
                        </span>
                    </div>
                </a>

                {{-- Desktop Nav --}}
                <nav
                    class="hidden md:flex items-center gap-1 p-1 bg-gray-100/50 dark:bg-white/5 rounded-xl border border-gray-200/50 dark:border-white/5">
                    <a href="/terms" @class([
                        'px-4 py-1.5 text-xs font-semibold rounded-lg transition-all',
                        'bg-white dark:bg-gray-800 text-primary-600 dark:text-primary-400 shadow-sm ring-1 ring-gray-950/5 dark:ring-white/5' => $isTerms,
                        'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white' => !$isTerms
                    ])>
                        {{ __('Terms of Service') }}
                    </a>
                    <a href="/privacy" @class([
                        'px-4 py-1.5 text-xs font-semibold rounded-lg transition-all',
                        'bg-white dark:bg-gray-800 text-primary-600 dark:text-primary-400 shadow-sm ring-1 ring-gray-950/5 dark:ring-white/5' => $isPrivacy,
                        'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white' => !$isPrivacy
                    ])>
                        {{ __('Privacy Policy') }}
                    </a>
                </nav>
            </div>

            {{-- Right Side: Actions --}}
            <div class="flex items-center gap-4">
                {{-- Theme Switcher & Language --}}
                <div class="flex items-center gap-2 pr-4 border-r border-gray-200 dark:border-white/10">
                    {{-- Language Switcher from Filament --}}
                    @include('filament.filament-language-switcher.language-switcher')

                    {{-- Compact Theme Toggle --}}
                    <button x-data="{ 
                                theme: localStorage.getItem('theme') || 'system',
                                toggle() {
                                    this.theme = this.theme === 'light' ? 'dark' : 'light';
                                    localStorage.setItem('theme', this.theme);
                                    if (this.theme === 'dark') document.documentElement.classList.add('dark');
                                    else document.documentElement.classList.remove('dark');
                                }
                            }" @click="toggle()"
                        class="p-2 text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white transition-colors">
                        <x-heroicon-o-sun x-show="theme !== 'dark'" class="w-5 h-5" />
                        <x-heroicon-o-moon x-show="theme === 'dark'" class="w-5 h-5" />
                    </button>
                </div>

                <a href="{{ route('filament.user.auth.login') }}"
                    class="hidden sm:flex items-center gap-2 px-4 py-2 text-xs font-bold text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-white/5 rounded-xl transition-all">
                    <x-heroicon-m-arrow-left class="w-4 h-4" />
                    {{ __('Back to Login') }}
                </a>
            </div>
        </div>
    </div>
</header>