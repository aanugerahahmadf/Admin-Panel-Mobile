<?php

use App\Providers\AppServiceProvider;
use App\Providers\AutoTranslationServiceProvider;
use App\Providers\Filament\AdminPanelProvider;
use App\Providers\Filament\UserPanelProvider;
use App\Providers\NativeServiceProvider;
use App\Providers\PlatformModeServiceProvider;
use App\Providers\PlatformSupportServiceProvider;
use App\Providers\VoltServiceProvider;

return [
    PlatformModeServiceProvider::class,
    AppServiceProvider::class,
    AutoTranslationServiceProvider::class,
    AdminPanelProvider::class,
    UserPanelProvider::class,
    NativeServiceProvider::class,
    PlatformSupportServiceProvider::class,
    VoltServiceProvider::class,
    \Laravel\Boost\BoostServiceProvider::class,
];
