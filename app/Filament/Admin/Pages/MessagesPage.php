<?php

namespace App\Filament\Admin\Pages;

use App\Models\Inbox;
use Filament\Pages\Page;
use Filament\Support\Enums\MaxWidth;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class MessagesPage extends Page
{
    protected static string $view = 'filament.pages.messages';

    public ?Inbox $selectedConversation;

    public static function getSlug(): string
    {
        return 'inbox/messages/{id?}';
    }

    public static function shouldRegisterNavigation(): bool
    {
        return config('messages.navigation.show_in_menu', true);
    }

    public static function getNavigationGroup(): ?string
    {
        return __(config('messages.navigation.navigation_group'));
    }

    public static function getNavigationLabel(): string
    {
        return __(config('messages.navigation.navigation_label', 'Messages'));
    }

    public static function getNavigationBadge(): ?string
    {
        $userId = Auth::id();
        if (! $userId) {
            return null;
        }

        return (string) Cache::remember(
            "admin_{$userId}_unread_messages_count",
            now()->addMinutes(1),
            function () use ($userId) {
                return Inbox::query()->whereJsonContains('user_ids', $userId, 'and', false)
                    ->whereHas('messages', function (Builder $query) use ($userId) {
                        $query->whereJsonDoesntContain('read_by', $userId, 'and', false);
                    })
                    ->count();
            }
        );
    }

    public static function getNavigationIcon(): string|Htmlable|null
    {
        return config('messages.navigation.navigation_icon', 'heroicon-o-chat-bubble-left-right');
    }

    public static function getNavigationSort(): ?int
    {
        return config('messages.navigation.navigation_sort');
    }

    public function mount(?int $id = null): void
    {
        if ($id) {
            $this->selectedConversation = Inbox::findOrFail($id, ['*']);
        }
    }

    public function getTitle(): string
    {
        return __(config('messages.navigation.navigation_label', 'Messages'));
    }

    public function getMaxContentWidth(): MaxWidth|string|null
    {
        return config('messages.max_content_width', MaxWidth::Full);
    }

    public function getHeading(): string|Htmlable
    {
        return __('Messages');
    }
}
