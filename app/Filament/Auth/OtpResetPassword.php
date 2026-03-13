<?php

namespace App\Filament\Auth;

use Filament\Pages\Auth\PasswordReset\ResetPassword as BaseResetPassword;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Filament\Notifications\Notification;
use Filament\Facades\Filament;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Component;
use Filament\Forms\Form;
use App\Models\User;

class OtpResetPassword extends BaseResetPassword
{
    public function mount(?string $email = null, ?string $token = null): void
    {
        if (Filament::auth()->check()) {
            redirect()->intended(Filament::getUrl());
            return;
        }

        $this->email = request()->query('email', '');
        
        $this->form->fill([
            'email' => $this->email,
        ]);
        
        $this->token = 'otp';
    }

    public function resetPassword(): ?\Filament\Http\Responses\Auth\Contracts\PasswordResetResponse
    {
        $data = $this->form->getState();
        $email = $data['email'] ?? $this->email;

        // Cek apakah OTP sudah diverifikasi di halaman sebelumnya
        if (!Cache::get('otp_verified_for_' . $email)) {
            Notification::make()
                ->title(__('Sesi verifikasi habis. Silakan verifikasi ulang OTP Anda.'))
                ->danger()
                ->send();
            
            $this->redirect(\App\Filament\Auth\VerifyOtp::getUrl(['email' => $email]));
            return null;
        }

        $user = User::where('email', $email)->first();
        
        if ($user) {
            $user->password = Hash::make($data['password']);
            $user->save();

            Cache::forget('otp_verified_for_' . $email);
            Cache::forget('password_reset_otp_' . $email);

            Notification::make()
                ->title(__('Kata sandi berhasil diatur ulang.'))
                ->success()
                ->send();

            $this->redirect(Filament::getLoginUrl());
            return null;
        } else {
            Notification::make()
                ->title(__('Pengguna tidak ditemukan.'))
                ->danger()
                ->send();
            return null;
        }
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                $this->getEmailFormComponent(),
                $this->getPasswordFormComponent(),
                $this->getPasswordConfirmationFormComponent(),
            ]);
    }
    
    protected function getEmailFormComponent(): Component
    {
        $field = parent::getEmailFormComponent();
        $field->disabled()->dehydrated();
        return $field;
    }
}
