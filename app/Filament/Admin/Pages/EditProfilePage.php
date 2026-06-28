<?php

namespace App\Filament\Admin\Pages;

use App\Livewire\BrowserSessionsComponent;
use App\Livewire\DeleteAccountComponent;
use App\Livewire\EditPasswordComponent;
use App\Livewire\MobileSettingsComponent;
use App\Livewire\PersonalInfoComponent;
use App\Livewire\UsernameComponent;
use Filament\Pages\Page;

class EditProfilePage extends Page
{
    protected static string $view = 'filament.pages.edit-profile';

    protected static ?string $navigationIcon = 'heroicon-o-user-circle';

    protected static bool $shouldRegisterNavigation = false;

    public function getTitle(): string
    {
        return __('Profil');
    }

    public static function getNavigationLabel(): string
    {
        return __('Profil');
    }

    public function getRegisteredCustomProfileComponents(): array
    {
        $components = [
            PersonalInfoComponent::class,
            UsernameComponent::class,
            MobileSettingsComponent::class,
            EditPasswordComponent::class,
            BrowserSessionsComponent::class,
            DeleteAccountComponent::class,
        ];

        return collect($components)
            ->sortBy(fn (string $component) => $component::getSort())
            ->all();
    }
}
