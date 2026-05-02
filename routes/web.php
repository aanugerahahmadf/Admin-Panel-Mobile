<?php

use App\Http\Controllers\Auth\SocialiteController;
use App\Http\Controllers\LanguageController;
use App\Providers\NativeServiceProvider;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Native\Mobile\Facades\System;

// Legal Routes using Filament Pages (HUBUNGKAN!)
// No standalone routes needed, now using modals in social-buttons.blade.php

Route::get('/', function () {
    if (auth()->check()) {
        return redirect('/user');
    }
    
    return view('welcome');
});

Route::redirect('/admin/inbox', '/admin/inbox/messages');
Route::get('/mobile/settings', function () {
    System::appSettings();

    return back();
})->name('mobile.settings')->middleware(['auth']);
Route::get('/language/switch/{locale}', [LanguageController::class, 'switch'])
    ->name('language.switch');
Route::get('/auth/{provider}/redirect', [SocialiteController::class, 'redirect'])
    ->name('auth.redirect');
Route::get('/auth/{provider}/callback', [SocialiteController::class, 'callback'])
    ->name('auth.callback');
// Mobile OAuth: callback dari Google → simpan token → redirect ke deep link
Route::get('/auth/{provider}/callback/mobile', [SocialiteController::class, 'callbackMobile'])
    ->name('auth.callback.mobile');
// Mobile OAuth: deep link handler → verifikasi token → login user
Route::get('/auth/mobile/verify', [SocialiteController::class, 'verifyMobileToken'])
    ->name('auth.mobile.verify');

// NativePHP Deep Link Handler — weddingapp://auth/google/success?token=xxx
// NativePHP intercepts the deep link and loads this URL in the WebView
Route::get('/auth/deeplink/google/success', [SocialiteController::class, 'verifyMobileToken'])
    ->name('auth.deeplink.success');

// NativePHP juga bisa load path langsung dari deep link
// weddingapp://auth/google/success → /auth/google/success di WebView
Route::get('/auth/google/success', [SocialiteController::class, 'verifyMobileToken'])
    ->name('auth.google.success');
Route::get('/media/{path}', function (string $path) {
    if (str_contains($path, '../')) {
        abort(403);
    }
    $file = storage_path('app/public/'.$path);
    if (! file_exists($file)) {
        abort(404);
    }

    return response()->file($file, ['Content-Type' => File::mimeType($file)]);
})->where('path', '.*')->name('media.serve');

require __DIR__.'/debug.php';
