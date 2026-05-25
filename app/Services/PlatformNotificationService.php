<?php

namespace App\Services;

use App\Models\User;
use Filament\Notifications\Notification as FilamentNotification;
use Native\Laravel\Notification;
use Native\Mobile\Dialog;

class PlatformNotificationService
{
    public static function send(User $user, string $title, string $body): void
    {
        // 1. Filament database notification (website — all browsers)
        FilamentNotification::make()
            ->title($title)
            ->body($body)
            ->warning()
            ->sendToDatabase($user);

        // 2. NativePHP desktop notification (desktop app)
        try {
            if (class_exists(Notification::class)) {
                Notification::new()
                    ->title($title)
                    ->message(strip_tags($body))
                    ->show();
            }
        } catch (\Throwable $e) {
            report($e);
        }

        // 3. NativePHP mobile toast notification (Android / iOS app)
        try {
            if (class_exists(Dialog::class)) {
                Dialog::toast(strip_tags($body), 'long');
            }
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
