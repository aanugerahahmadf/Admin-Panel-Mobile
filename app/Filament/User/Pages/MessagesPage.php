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
use Illuminate\Support\Facades\DB;

class MessagesPage extends Page
{
    protected static string $view = 'filament.pages.messages';

    protected static ?string $activeNavigationIcon = 'heroicon-o-chat-bubble-left-right';

    protected static ?int $navigationSort = 1;

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

        $count = Cache::remember(
            "user_{$userId}_unread_messages_count",
            now()->addSeconds(30),
            function () use ($userId) {
                $adminIds = User::query()->whereHas('roles', function ($q) {
                    $q->where('name', 'super_admin');
                })->pluck('id')->toArray();

                return Inbox::query()
                    ->whereJsonContains('user_ids', $userId)
                    ->where(function ($q) use ($adminIds) {
                        foreach ($adminIds as $adminId) {
                            $q->orWhereJsonContains('user_ids', $adminId);
                        }
                    })
                    ->whereHas('messages', function (Builder $query) use ($userId) {
                        // Pesan yang read_by-nya tidak mengandung userId ini
                        if (DB::getDriverName() === 'sqlite') {
                            $query->whereRaw('read_by NOT LIKE ?', ["%\"{$userId}\"%"]);
                        } else {
                            $query->whereRaw(
                                'JSON_SEARCH(read_by, "one", ?) IS NULL',
                                [(string) $userId]
                            );
                        }
                        $query->where('user_id', '!=', $userId); // hanya pesan dari orang lain
                    })
                    ->count();
            }
        );

        // Jangan tampilkan badge kalau 0
        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return __('Pesan belum dibaca');
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'danger';
    }

    public static function getNavigationIcon(): string|Htmlable|null
    {
        return config('messages.navigation.navigation_icon', static::$activeNavigationIcon);
    }

    public static function getNavigationSort(): ?int
    {
        return config('messages.navigation.navigation_sort', static::$navigationSort);
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

    /**
     * Remove the default Filament page header (title + breadcrumbs) on desktop
     * so the chat container gets the full available vertical space.
     * The page title is still used for the browser tab / navigation badge.
     */
    public function hasHeader(): bool
    {
        // Keep heading visible on mobile (≤ 1023px) because the layout is single-column
        // and the user needs context. On desktop the topnav already shows the page name.
        return request()->header('X-Filament-Mobile', false) || (
            isset($_SERVER['HTTP_USER_AGENT']) &&
            preg_match('/Mobile|Android|iPhone|iPad/i', $_SERVER['HTTP_USER_AGENT'] ?? '')
        );
    }
}
