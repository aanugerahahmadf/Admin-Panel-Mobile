<?php

namespace App\Filament\User\Pages;

use Filament\Pages\Page;

class CompleteProfilePage extends Page
{
    protected static string $view = 'filament.user.pages.complete-profile-page';

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $maxWidth = 'md';

    public static function getSlug(): string
    {
        return 'complete-profile';
    }

    public function getTitle(): string
    {
        return __('Lengkapi Profil Anda');
    }

    public static function getNavigationLabel(): string
    {
        return __('Lengkapi Profil');
    }
}
