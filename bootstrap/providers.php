<?php

use App\Providers\AppServiceProvider;
use App\Providers\AutoTranslationServiceProvider;
use App\Providers\Filament\AdminPanelProvider;
use App\Providers\Filament\UserPanelProvider;
use App\Providers\NativeServiceProvider;
use App\Providers\VoltServiceProvider;

return [
    AppServiceProvider::class,
    AutoTranslationServiceProvider::class,
    AdminPanelProvider::class,
    UserPanelProvider::class,
    NativeServiceProvider::class,
    VoltServiceProvider::class,
];
