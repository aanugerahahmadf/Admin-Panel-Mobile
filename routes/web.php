<?php

use App\Http\Controllers\LanguageController;
use App\Http\Controllers\Auth\SocialiteController;
use Illuminate\Support\Facades\Route;
use Native\Mobile\Facades\System;
// Legal Routes using Filament Pages (HUBUNGKAN!)
// No standalone routes needed, now using modals in social-buttons.blade.php


Route::get('/', function () {
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
Route::get('/media/{path}', function (string $path) {
    if (str_contains($path, '../')) { abort(403); }
    $file = storage_path('app/public/' . $path);
    if (!file_exists($file)) { abort(404); }
    return response()->file($file, ['Content-Type' => \Illuminate\Support\Facades\File::mimeType($file)]);
})->where('path', '.*')->name('media.serve');

require __DIR__.'/debug.php';