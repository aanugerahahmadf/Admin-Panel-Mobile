<?php

namespace App\Notifications\Traits;

use App\Channels\NativePHPChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Queue\ShouldQueue;

trait HasNotificationChannels
{
    use Queueable;

    public array $actionUrl = [];

    public function via($notifiable): array
    {
        $channels = ['database'];

        if (config('broadcasting.default') !== 'null') {
            $channels[] = 'broadcast';
        }

        if (class_exists(\Native\Laravel\Notification::class) || class_exists(\Native\Mobile\Dialog::class)) {
            $channels[] = NativePHPChannel::class;
        }

        return $channels;
    }

    public function toDatabase($notifiable): array
    {
        return [
            'title' => $this->title(),
            'body' => $this->body(),
            'icon' => $this->icon(),
            'color' => $this->color(),
            'action_url' => $this->actionUrl,
            'type' => static::class,
        ];
    }

    public function toBroadcast($notifiable): array
    {
        return $this->toDatabase($notifiable);
    }

    public function toNativePHP($notifiable): array
    {
        return [
            'title' => $this->title(),
            'body' => strip_tags($this->body()),
        ];
    }

    abstract protected function title(): string;
    abstract protected function body(): string;
    abstract protected function icon(): string;
    abstract protected function color(): string;
}
