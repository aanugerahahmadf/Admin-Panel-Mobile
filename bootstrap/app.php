<?php

use App\Http\Middleware\SetLocale;
use App\Http\Middleware\VerifyCsrfToken;
use App\Providers\AutoTranslationServiceProvider;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Session\Middleware\StartSession;

/*
|--------------------------------------------------------------------------
| Vercel Storage Redirection
|--------------------------------------------------------------------------
| On Vercel, the filesystem is read-only. We need to redirect storage,
| cache, and views to /tmp during the build and at runtime.
*/
// Logic moved to AppServiceProvider to avoid premature config() calls.

$app = Application::configure(basePath: dirname(__DIR__))
    ->withProviders([
        AutoTranslationServiceProvider::class,
    ])
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Mendaftarkan middleware SetLocale ke group web agar session dan auth tersedia
        $middleware->web(append: [
            SetLocale::class,
        ]);

        // Mendaftarkan middleware SetLocale ke group api untuk sinkronisasi bahasa aplikasi mobile
        $middleware->api(append: [
            SetLocale::class,
        ]);

        // Define mobile group for NativePHP
        $middleware->group('mobile', [
            EncryptCookies::class,
            StartSession::class,
            SetLocale::class,
        ]);

        $middleware->replace(ValidateCsrfToken::class, VerifyCsrfToken::class);

        $middleware->redirectGuestsTo(fn () => route('filament.user.auth.login'));

        // Trust proxies for Vercel, Production, or ngrok development
        if (env('VERCEL') ||
            env('APP_ENV') === 'production' ||
            str_contains((string) env('APP_URL'), 'ngrok-free.dev') ||
            (isset($_SERVER['HTTP_X_FORWARDED_HOST']) && str_contains($_SERVER['HTTP_X_FORWARDED_HOST'], 'ngrok')) ||
            (isset($_SERVER['HTTP_HOST']) && str_contains($_SERVER['HTTP_HOST'], 'ngrok'))
        ) {
            $middleware->trustProxies('*');
        }
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();

return $app;
