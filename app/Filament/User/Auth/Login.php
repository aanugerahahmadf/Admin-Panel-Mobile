<?php

namespace App\Filament\User\Auth;

use App\Models\User;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\TextInput;
use Filament\Http\Responses\Auth\Contracts\LoginResponse;
use Filament\Notifications\Notification;
use Filament\Pages\Auth\Login as BaseLogin;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class Login extends BaseLogin
{
    protected function getPasswordFormComponent(): Component
    {
        return parent::getPasswordFormComponent()
            ->label(__('Kata Sandi'));
    }

    protected function getRememberFormComponent(): Component
    {
        return parent::getRememberFormComponent()
            ->label(__('Ingat Saya'));
    }

    public function getHeading(): string|Htmlable
    {
        return __('Masuk ke Sistem');
    }

    public function getSubheading(): string|Htmlable|null
    {
        return __('Silakan masukkan kredensial Anda untuk melanjutkan rencana Anda.');
    }

    protected function getEmailFormComponent(): Component
    {
        return TextInput::make('login')
            ->label(__('Alamat Email / Username'))
            ->required()
            ->autocomplete()
            ->autofocus()
            ->extraInputAttributes(['tabindex' => 1]);
    }

    public function registerAction(): \Filament\Actions\Action
    {
        return parent::registerAction()
            ->label(__('Daftar Akun Baru'));
    }

    public function passwordResetAction(): \Filament\Actions\Action
    {
        return parent::passwordResetAction()
            ->label(__('Lupa Kata Sandi?'));
    }

    protected function getAuthenticateFormAction(): \Filament\Actions\Action
    {
        return parent::getAuthenticateFormAction()
            ->label(__('Log In'));
    }

    protected function getCredentialsFromFormData(array $data): array
    {
        $login = $data['login'];
        $password = $data['password'];

        // Check if the login is an email or username
        $field = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        return [
            $field => $login,
            'password' => $password,
        ];
    }

    public function authenticate(): ?LoginResponse
    {
        $response = parent::authenticate();

        if ($response) {
            Notification::make()
                ->title(__('Selamat Datang Kembali!'))
                ->body(__('Anda telah berhasil masuk ke sistem Weeding Organizer pada :time.', ['time' => now()->format('H:i:s')]))
                ->success()
                ->duration(5000)
                ->send();
        }

        return $response;
    }

    protected function throwFailureValidationException(): never
    {
        Notification::make()
            ->title(__('Otentikasi Gagal'))
            ->body(__('Kami tidak dapat memverifikasi kredensial Anda. Silakan periksa email/username dan kata sandi Anda, lalu coba lagi.'))
            ->danger()
            ->duration(8000)
            ->send();

        throw ValidationException::withMessages([
            'data.login' => __('filament-panels::pages/auth/login.messages.failed'),
        ]);
    }
}
