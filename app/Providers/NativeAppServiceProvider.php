<?php

namespace App\Providers;

use Native\Laravel\Contracts\ProvidesPhpIni;
use Native\Laravel\Facades\Window;

class NativeAppServiceProvider implements ProvidesPhpIni
{
    /**
     * Executed once the native application has been booted.
     * Use this method to open windows, register global shortcuts, etc.
     */
    public function boot(): void
    {
        Window::open();
    }

    /**
     * Return an array of php.ini directives to be set.
     */
    public function phpIni(): array
    {
        return [
            'memory_limit' => '512M',
            'opcache.enable' => '1',
            'opcache.memory_consumption' => '128',
            'opcache.interned_strings_buffer' => '8',
            'opcache.max_accelerated_files' => '4000',
            'opcache.revalidate_freq' => '0',
            'opcache.validate_timestamps' => '0',
        ];
    }
}
