<?php

namespace App\Channels;

use Illuminate\Notifications\Notification;

class NativePHPChannel
{
    public function send($notifiable, Notification $notification): void
    {
        $data = method_exists($notification, 'toNativePHP')
            ? $notification->toNativePHP($notifiable)
            : [];

        $title = $data['title'] ?? 'Notifikasi';
        $body = $data['body'] ?? '';

        try {
            if (class_exists(\Native\Laravel\Notification::class)) {
                \Native\Laravel\Notification::new()
                    ->title($title)
                    ->message($body)
                    ->show();
            }
        } catch (\Throwable $e) {
            report($e);
        }

        try {
            if (class_exists(\Native\Mobile\Dialog::class)) {
                \Native\Mobile\Dialog::toast($body, 'long');
            }
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
