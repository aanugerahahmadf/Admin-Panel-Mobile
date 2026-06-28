<?php

namespace App\Filament\User\Pages;

use App\Filament\User\Widgets\ProfileOverview;
use Filament\Pages\Page;
use Illuminate\Contracts\View\View;

class ProfilePage extends Page
{

    protected static ?string $navigationIcon = 'heroicon-o-user-circle';

    protected static string $view = 'filament.user.pages.profile-page';

    protected static bool $shouldRegisterNavigation = false;

    public function getTitle(): string
    {
        return __('Profil');
    }

    public static function getNavigationLabel(): string
    {
        return __('Profil');
    }

    public function getHeader(): ?View
    {
        $user = auth()->user();
        $avatar = $user?->avatar_url ?? $user?->getFilamentAvatarUrl();
        $username = $user->username ?? '';
        $fullName = $user->full_name ?? $user->name ?? '';

        $avatarHtml = $avatar
            ? '<img src="' . e($avatar) . '" alt="" class="w-20 h-20 rounded-full object-cover ring-4 ring-primary-200 dark:ring-primary-800">'
            : '<div class="w-20 h-20 rounded-full bg-gray-100 dark:bg-gray-800 ring-4 ring-primary-200 dark:ring-primary-800 flex items-center justify-center"><svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg></div>';

        return view('filament.user.pages.profile-header', [
            'avatarHtml' => $avatarHtml,
            'username'   => $username,
            'fullName'   => $fullName,
        ]);
    }

    protected function getHeaderWidgets(): array
    {
        return [
            ProfileOverview::class,
        ];
    }

}
