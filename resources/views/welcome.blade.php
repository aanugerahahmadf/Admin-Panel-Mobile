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
            top: 4rem;
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
                        <span class="relative py-1 bg-white dark:bg-[#161615]">
                            <span
                                class="flex items-center justify-center rounded-full bg-[#FDFDFC] dark:bg-[#161615] shadow-[0px_0px_1px_0px_rgba(0,0,0,0.03),0px_1px_2px_0px_rgba(0,0,0,0.06)] w-3.5 h-3.5 border dark:border-[#3E3E3A] border-[#e3e3e0]">
                                <span class="rounded-full bg-[#E91E63] w-1.5 h-1.5"></span>
                            </span>
                        </span>
                        <span>{{ __('Explore Packages & Portfolio') }}</span>
                    </li>
                    <li
                        class="flex items-center gap-4 py-2 relative before:border-l before:border-[#e3e3e0] dark:before:border-[#3E3E3A] before:bottom-1/2 before:top-0 before:left-[0.4rem] before:absolute">
                        <span class="relative py-1 bg-white dark:bg-[#161615]">
                            <span
                                class="flex items-center justify-center rounded-full bg-[#FDFDFC] dark:bg-[#161615] shadow-[0px_0px_1px_0px_rgba(0,0,0,0.03),0px_1px_2px_0px_rgba(0,0,0,0.06)] w-3.5 h-3.5 border dark:border-[#3E3E3A] border-[#e3e3e0]">
                                <span class="rounded-full bg-[#E91E63] w-1.5 h-1.5"></span>
                            </span>
                        </span>
                        <span>{{ __('Track Orders & Booking Details') }}</span>
                    </li>
                </ul>

                <ul class="flex w-full mt-4 lg:mt-6" style="position: relative; z-index: 10;">
                    <li class="w-full lg:w-auto">
                        <a href="{{ NativeServiceProvider::normalizeUrl(route('filament.user.resources.home.index', ['record' => 1])) }}"
                            style="display: inline-block; position: relative; z-index: 10; cursor: pointer;"
                            class="dark:bg-white dark:text-gray-950 dark:hover:bg-gray-200 hover:bg-black hover:border-black px-5 py-1.5 bg-gray-900 rounded-sm border border-black text-white text-sm font-semibold leading-normal transition-all active:scale-95 shadow-sm">
                            {{ __('Buka Beranda') }}
                        </a>
                    </li>
                </ul>
            </div>

            <div
                class="bg-[#fff2f2] dark:bg-[#1D0002] relative lg:-ml-px -mb-px lg:mb-0 rounded-t-lg lg:rounded-t-none lg:rounded-r-lg aspect-[335/376] lg:aspect-auto w-full lg:w-[438px] shrink-0 overflow-hidden flex items-center justify-center border-b lg:border-b-0 lg:border-l border-[#19140015] dark:border-[#ffffff10]">
                <div class="z-10 text-center p-8">
                    <h2 class="text-3xl lg:text-4xl font-bold text-[#E91E63] dark:text-[#FF80AB] mb-2">
                        {{ __('Dekorasi Bunga Pernikahan') }}
                    </h2>
                </div>
            </div>
        </main>
    </div>

    @include('filament.footer')
</body>

</html>