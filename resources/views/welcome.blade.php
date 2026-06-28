<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">

    <title>{{ config('app.name') }} - {{ __('Dekorasi Bunga Pernikahan') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

    <!-- Styles / Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles

    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/anchor@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/tooltip@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        [x-cloak] {
            display: none !important;
        }

        /* Hide semua scrollbar — web & mobile */
        *, *::-webkit-scrollbar { scrollbar-width: none !important; -ms-overflow-style: none !important; }
        *::-webkit-scrollbar { display: none !important; width: 0 !important; height: 0 !important; }

        /* Force hide scrollbar pada semua overflow element */
        [style*="overflow"] { scrollbar-width: none !important; -ms-overflow-style: none !important; }
        [style*="overflow"]::-webkit-scrollbar { display: none !important; width: 0 !important; height: 0 !important; }
    </style>
    <script>
        (function () {
            try {
                const theme = localStorage.getItem('theme') || localStorage.getItem('filament_theme') || 'system';
                if (theme === 'dark' || (theme === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                    document.documentElement.classList.add('dark');
                } else {
                    document.documentElement.classList.remove('dark');
                }
            } catch (e) {
                console.error('Theme sync failed:', e);
            }
        })();

        // Inject scrollbar-hiding style ke semua elemen overflow secara dinamis
        (function hideAllScrollbars() {
            const style = document.createElement('style');
            style.textContent = `
                * { scrollbar-width: none !important; -ms-overflow-style: none !important; }
                *::-webkit-scrollbar { display: none !important; width: 0 !important; height: 0 !important; background: transparent !important; }
                *::-webkit-scrollbar-thumb { background: transparent !important; }
                *::-webkit-scrollbar-track { background: transparent !important; }
            `;
            document.head.appendChild(style);
        })();
    </script>
</head>

<body
    class="bg-white dark:bg-gray-950 text-gray-900 dark:text-gray-100 flex flex-col"
    style="height: 100dvh; overflow: hidden; position: fixed; width: 100%; -webkit-tap-highlight-color: transparent;">

    @livewireScripts
    @include('filament.header')

    {{-- Hanya area ini yang scroll — header & footer tetap diam --}}
    <div style="
            position: absolute;
            top: {{ \App\Providers\NativeServiceProvider::isAnyMobile() ? '0' : '4rem' }};
            bottom: 3rem;
            left: 0;
            right: 0;
            overflow-y: auto;
            overflow-x: hidden;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: none;
            -ms-overflow-style: none;
        ">

        <div class="flex items-center justify-center w-full min-h-full p-6 lg:p-8">
            <main
                class="flex max-w-[335px] w-full flex-col-reverse lg:max-w-4xl lg:flex-row shadow-sm rounded-lg overflow-hidden border border-[#19140015] dark:border-[#ffffff10]">
            <div
                class="text-[13px] leading-[20px] flex-1 p-6 pb-12 lg:p-20 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100">
                <h1 class="mb-1 font-semibold text-lg text-gray-950 dark:text-white">
                    {{ __('Welcome To Dekorasi Bunga Pernikahan') }}
                </h1>
                <p class="mb-2 text-gray-600 dark:text-gray-400">
                    {{ __('Manage your decoration needs efficiently with our comprehensive system.') }}
                </p>

                <ul class="flex flex-col mb-4 lg:mb-6 gap-2">
                    <li
                        class="flex items-center gap-4 py-2 relative before:border-l before:border-[#e3e3e0] dark:before:border-[#3E3E3A] before:top-1/2 before:bottom-0 before:left-[0.4rem] before:absolute">
                        <span class="relative py-1 bg-white dark:bg-gray-900">
                            <span
                                class="flex items-center justify-center rounded-full bg-[#FDFDFC] dark:bg-gray-900 shadow-[0px_0px_1px_0px_rgba(0,0,0,0.03),0px_1px_2px_0px_rgba(0,0,0,0.06)] w-3.5 h-3.5 border dark:border-[#3E3E3A] border-[#e3e3e0]">
                                <span class="rounded-full bg-[#E91E63] w-1.5 h-1.5"></span>
                            </span>
                        </span>
                        <span>{{ __('Explore Packages & Portfolio') }}</span>
                    </li>
                    <li
                        class="flex items-center gap-4 py-2 relative before:border-l before:border-[#e3e3e0] dark:before:border-[#3E3E3A] before:bottom-1/2 before:top-0 before:left-[0.4rem] before:absolute">
                        <span class="relative py-1 bg-white dark:bg-gray-900">
                            <span
                                class="flex items-center justify-center rounded-full bg-[#FDFDFC] dark:bg-gray-900 shadow-[0px_0px_1px_0px_rgba(0,0,0,0.03),0px_1px_2px_0px_rgba(0,0,0,0.06)] w-3.5 h-3.5 border dark:border-[#3E3E3A] border-[#e3e3e0]">
                                <span class="rounded-full bg-[#E91E63] w-1.5 h-1.5"></span>
                            </span>
                        </span>
                        <span>{{ __('Track Orders & Booking Details') }}</span>
                    </li>
                </ul>

                <ul class="flex flex-col items-center lg:flex-row lg:justify-start w-full mt-4 lg:mt-6 gap-3" style="position: relative; z-index: 10;">
                    @php
                        $isMobileEnv = \App\Providers\NativeServiceProvider::isAnyMobile();
                        $btnClass = $isMobileEnv ? 'w-full' : 'w-full lg:hidden';
                        // Di NativePHP mobile, navigasi halaman harus pakai path relatif
                        // agar WebView navigate secara internal, bukan buka Chrome.
                        // normalizeUrl() hanya untuk asset/API calls ke server PC.
                        $loginUrl   = $isMobileEnv ? '/user/login'    : \App\Providers\NativeServiceProvider::normalizeUrl(route('filament.user.auth.login'));
                        $registerUrl = $isMobileEnv ? '/user/register' : \App\Providers\NativeServiceProvider::normalizeUrl(route('filament.user.auth.register'));
                        $homeUrl    = $isMobileEnv ? '/user'           : \App\Providers\NativeServiceProvider::normalizeUrl(route('filament.user.pages.home'));
                        $googleUrl  = \App\Providers\NativeServiceProvider::normalizeUrl(route('auth.redirect', ['provider' => 'google']));
                    @endphp
                    @auth
                        <li class="{{ $btnClass }}">
                            <a href="{{ $homeUrl }}"
                                style="display: flex; position: relative; z-index: 10; cursor: pointer;"
                                 class="flex items-center justify-center gap-2 dark:bg-white dark:text-gray-950 dark:hover:bg-gray-200 hover:bg-black hover:border-black px-5 py-2.5 bg-gray-900 rounded-sm border border-black text-white text-sm font-semibold leading-normal transition-all active:scale-95 shadow-sm w-full">
                                <x-gmdi-dashboard-o class="w-5 h-5" />
                                <span>{{ __('Buka Beranda') }}</span>
                            </a>
                        </li>
                    @else
                        {{-- Ditampilkan di mobile (web & app Android/iOS) dan disembunyikan di desktop (PC/Laptop/Macbook) --}}
                        <li class="{{ $btnClass }}">
                            <a href="{{ $loginUrl }}"
                                style="display: flex; position: relative; z-index: 10; cursor: pointer;"
                                class="flex items-center justify-center gap-2 dark:bg-white dark:text-gray-950 dark:hover:bg-gray-200 hover:bg-black hover:border-black px-5 py-2.5 bg-gray-900 rounded-sm border border-black text-white text-sm font-semibold leading-normal transition-all active:scale-95 shadow-sm w-full">
                                <x-gmdi-login-o class="w-5 h-5" />
                                <span>{{ __('Masuk') }}</span>
                            </a>
                        </li>
                        <li class="{{ $btnClass }}">
                            <a href="{{ $registerUrl }}"
                                style="display: flex; position: relative; z-index: 10; cursor: pointer;"
                                class="flex items-center justify-center gap-2 dark:bg-white dark:text-gray-950 dark:hover:bg-gray-200 hover:bg-black hover:border-black px-5 py-2.5 bg-gray-900 rounded-sm border border-black text-white text-sm font-semibold leading-normal transition-all active:scale-95 shadow-sm w-full">
                                <x-gmdi-person-add-o class="w-5 h-5" />
                                <span>{{ __('Daftar') }}</span>
                            </a>
                        </li>
                        <li class="{{ $btnClass }}">
                            <a href="{{ $googleUrl }}"
                                style="display: flex; position: relative; z-index: 10; cursor: pointer;"
                                class="flex items-center justify-center gap-3 dark:bg-white dark:text-gray-950 dark:hover:bg-gray-200 hover:bg-black hover:border-black px-5 py-2.5 bg-gray-900 rounded-sm border border-black text-white text-sm font-semibold leading-normal transition-all active:scale-95 shadow-sm w-full">
                                <svg class="w-5 h-5 mr-1.5 flex-shrink-0" viewBox="0 0 24 24">
                                    <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                                    <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                                    <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l3.66-2.84z"/>
                                    <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                                </svg>
                                <span>{{ __('Masuk Dengan Google') }}</span>
                            </a>
                        </li>
                    @endauth
                </ul>
            </div>

            <div
                class="relative lg:-ml-px -mb-px lg:mb-0 rounded-t-lg lg:rounded-t-none lg:rounded-r-lg aspect-[335/376] lg:aspect-auto w-full lg:w-[438px] shrink-0 overflow-hidden flex items-center justify-center border-b lg:border-b-0 lg:border-l border-[#19140015] dark:border-[#ffffff10]">
                <img
                    src="{{ asset('images/article/article-4.png') }}"
                    alt="{{ __('Dekorasi Bunga Pernikahan') }}"
                    class="z-10 w-full h-full object-cover absolute inset-0"
                />
            </div>
        </main>
    </div>

    @include('filament.footer')
</body>

</html>