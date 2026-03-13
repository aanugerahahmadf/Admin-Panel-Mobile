<?php

namespace App\Filament\Auth;

use Filament\Pages\Page;
use Illuminate\Support\Facades\Cache;
use Filament\Notifications\Notification;
use Filament\Facades\Filament;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Component;
use Filament\Forms\Form;
use Illuminate\Support\Facades\Route;

class VerifyOtp extends \Filament\Pages\SimplePage
{
    protected static string $view = 'filament-panels::pages.auth.password-reset.reset-password';
    protected static string $layout = 'filament-panels::components.layout.simple';

    public $email = '';
    public $otp = '';

    public static function getUrl(array $parameters = []): string
    {
        return route('filament.admin.auth.verify-otp', $parameters);
    }

    public function mount(): void
    {
        $this->email = request()->query('email', '');
        
        if (blank($this->email)) {
            $this->redirect(route('filament.admin.auth.password-reset.request'));
        }

        $this->form->fill([
            'email' => $this->email,
        ]);
    }

    public function verify(): void
    {
        $data = $this->form->getState();
        $email = $data['email'];
        $otp = $data['otp'];

        $cachedOtp = Cache::get('password_reset_otp_' . $email);

        if ($cachedOtp && (string) $cachedOtp === (string) $otp) {
            // Tandai bahwa OTP sudah valid untuk email ini (berlaku 15 menit)
            Cache::put('otp_verified_for_' . $email, true, now()->addMinutes(15));

            Notification::make()
                ->title(__('Kode OTP valid! Silakan atur kata sandi baru.'))
                ->success()
                ->send();

            $this->redirect(\Illuminate\Support\Facades\URL::temporarySignedRoute(
                'filament.admin.auth.password-reset.reset',
                now()->addMinutes(30),
                [
                    'email' => $email,
                    'token' => 'otp',
                ]
            ));
        } else {
            Notification::make()
                ->title(__('Kode OTP tidak valid atau telah kadaluarsa.'))
                ->danger()
                ->send();
        }
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('email')
                    ->label(__('Email address'))
                    ->disabled()
                    ->dehydrated(),
                TextInput::make('otp')
                    ->label(__('6 Digit Kode OTP'))
                    ->required()
                    ->length(6)
                    ->numeric()
                    ->autofocus(),
            ]);
    }

    public function getHeading(): string
    {
        return __('Verifikasi Kode OTP');
    }

    public function getSubheading(): string
    {
        return __('Masukkan 6 digit kode yang kami kirim ke email Anda.');
    }

    public function getFormActions(): array
    {
        return [
            $this->getVerifyFormAction(),
        ];
    }

    protected function getVerifyFormAction(): \Filament\Actions\Action
    {
        return \Filament\Actions\Action::make('verify')
            ->label(__('Verifikasi Kode'))
            ->submit('verify');
    }

    public static function registerRoutes(\Filament\Panel $panel): void
    {
        Route::get('/password-reset/verify', static::class)
            ->name('auth.verify-otp')
            ->middleware(['web']);
    }
}
