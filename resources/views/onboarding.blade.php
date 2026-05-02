<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Welcome - {{ config('app.name') }}</title>
    
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
    </script>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700,800" rel="stylesheet" />
    
    <!-- Icons -->
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    
    <!-- Styles -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Instrument Sans', 'sans-serif'],
                    },
                    colors: {
                        primary: {
                            50: '#FDF2F5',
                            100: '#FCE4EC',
                            200: '#F8BBD0',
                            300: '#F48FB1',
                            400: '#F06292',
                            500: '#E91E63',
                            600: '#D81B60',
                            700: '#C2185B',
                            800: '#AD1457',
                            900: '#880E4F',
                            950: '#4A072B',
                        },
                    }
                }
            }
        }
    </script>
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/anchor@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/tooltip@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @vite(['resources/css/onboarding.css'])
</head>
<body class="h-[100dvh] flex flex-col justify-between overflow-hidden">
    
    @include('filament.header', ['hideAuth' => true])

    <!-- Content Section -->
    <div class="flex-1 flex flex-col items-center justify-center p-8 mt-16">
        
        <!-- Animated Logo Container -->
        <div class="relative mb-10 floating">
            <div class="absolute inset-0 bg-primary-500 blur-3xl opacity-20 rounded-full animate-pulse"></div>
            <div class="relative w-32 h-32 glass-panel rounded-[2.5rem] flex items-center justify-center shadow-2xl overflow-hidden border-white/20 dark:border-white/10">
                <img src="{{ asset('images/logo.png') }}" alt="Logo" class="w-24 h-24 object-contain">
            </div>
        </div>

        <div class="text-center space-y-4 max-w-xs">
            <h1 class="text-4xl font-extrabold tracking-tight leading-none uppercase">
                Wedding<br>
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-primary-500 to-pink-400">
                    Decoration
                </span>
            </h1>
            <p class="text-slate-500 dark:text-slate-400 text-sm font-medium leading-relaxed px-2">
                {{ __('Rancang dekorasi pernikahan impian Anda dengan bantuan AI yang cerdas dan modern.') }}
            </p>
        </div>
    </div>

    <!-- Actions Section -->
    <div class="p-8 pb-12 space-y-4">
        <!-- Main Buttons -->
        <div class="flex gap-3">
            <a href="{{ url('/user/login') }}" 
               class="btn-animate flex-1 flex items-center justify-center py-4 px-6 glass-panel rounded-2xl font-bold shadow-xl text-slate-900 dark:text-white border border-slate-200/50 dark:border-white/5">
                {{ __('Masuk') }}
            </a>
            <a href="{{ url('/user/register') }}" 
               class="btn-animate flex-1 flex items-center justify-center py-4 px-6 bg-primary-500 rounded-2xl font-bold text-white shadow-xl shadow-primary-900/20">
                {{ __('Daftar') }}
            </a>
        </div>

        <!-- Google Sign-In -->
        <a href="{{ route('auth.redirect', 'google') }}" 
           class="btn-animate flex items-center justify-center gap-3 w-full py-4 px-6 bg-slate-950 dark:bg-white text-white dark:text-slate-900 rounded-2xl font-bold text-base shadow-2xl">
            <svg class="w-5 h-5" viewBox="0 0 24 24">
                <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l3.66-2.84z" fill="#FBBC05"/>
                <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
            </svg>
            {{ __('Lanjut dengan Google') }}
        </a>
    </div>

    <!-- Native App Integration -->
    <script>
        // Native Detection
        const isNative = window.process && window.process.type === 'renderer';
        if (isNative) {
            document.body.classList.add('is-native');
            console.log("Running in Native Mode");
        }
    </script>
</body>
</html>
