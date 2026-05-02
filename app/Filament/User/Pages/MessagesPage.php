<?php

namespace App\Filament\User\Pages;

use App\Models\Inbox;
use App\Models\User;
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
        return config('messages.slug', 'messages').'/{id?}';
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
            "user_{$userId}_unread_messages_count",
            now()->addMinutes(1),
            function () use ($userId) {
                $query = Inbox::query()->whereJsonContains('user_ids', $userId, 'and', false);

                // User panel should only count messages that include an admin
                $adminIds = User::query()->whereHas('roles', function ($q) {
                    $q->where('name', 'super_admin');
                })->pluck('id')->toArray();
                $query->where(function ($q) use ($adminIds) {
                    foreach ($adminIds as $adminId) {
                        $q->orWhereJsonContains('user_ids', $adminId);
                    }
                });

                return $query->whereHas('messages', function (Builder $query) use ($userId) {
                    $query->whereJsonDoesntContain('read_by', $userId, 'and', false);
                })
                    ->count();
            }
        );
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return static::getNavigationLabel();
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
            $this->selectedConversation = Inbox::query()->findOrFail($id, ['*']);

            return;
        }

        // If no ID is provided, find or create conversation with Super Admin
        $userId = Auth::id();
        $admin = User::query()->whereHas('roles', function ($q) {
            $q->where('name', 'super_admin');
        })->first();

        if ($admin) {
            $inbox = Inbox::query()->whereJsonContains('user_ids', $userId, 'and', false)
                ->whereJsonContains('user_ids', $admin->id, 'and', false)
                ->first();

            if (! $inbox) {
                $inbox = Inbox::create([
                    'user_ids' => [$userId, $admin->id],
                ]);
            }

            $this->redirect(static::getUrl(['id' => $inbox->id]));
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
