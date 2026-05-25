<?php

namespace App\Filament\User\Auth;

use App\Models\User;
use App\Services\GeoLocationService;
use App\Services\PlatformNotificationService;
use Filament\Actions\Action;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\Wizard\Step;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Pages\Auth\Register as BaseRegister;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\HtmlString;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;

class Register extends BaseRegister
{
    public function getView(): string
    {
        return 'filament.user.auth.register';
    }

    public function getHeading(): string|Htmlable
    {
        return __('Daftar Akun Baru');
    }

    protected function getEmailFormComponent(): Component
    {
        return parent::getEmailFormComponent()
            ->label(__('Alamat Email'));
    }

    protected function getPasswordFormComponent(): Component
    {
        return parent::getPasswordFormComponent()
            ->label(__('Kata Sandi'));
    }

    protected function getPasswordConfirmationFormComponent(): Component
    {
        return parent::getPasswordConfirmationFormComponent()
            ->label(__('Konfirmasi Kata Sandi'));
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Wizard::make([
                    Step::make('akun')
                        ->label(__('Akun'))
                        ->description(__('Info akun dasar'))
                        ->icon('heroicon-m-user-circle')
                        ->schema([
                            FileUpload::make('avatar_url')
                                ->label(__('Foto Profil'))
                                ->image()
                                ->avatar()
                                ->directory('avatars')
                                ->alignCenter()
                                ->columnSpanFull()
                                ->extraAttributes(['class' => 'flex flex-col items-center justify-center']),
                            TextInput::make('username')
                                ->label(__('Username'))
                                ->required()
                                ->minLength(3)
                                ->maxLength(255)
                                ->unique(User::class)
                                ->autocomplete('username')
                                ->columnSpanFull(),
                            $this->getEmailFormComponent(),
                            TextInput::make('password')
                                ->label(__('Kata Sandi'))
                                ->password()
                                ->revealable()
                                ->required()
                                ->rule(Password::min(8)
                                    ->letters()
                                    ->mixedCase()
                                    ->numbers()
                                    ->symbols()
                                )
                                ->same('password_confirmation')
                                ->validationAttribute(__('Kata Sandi')),
                            TextInput::make('password_confirmation')
                                ->label(__('Konfirmasi Kata Sandi'))
                                ->password()
                                ->revealable()
                                ->required()
                                ->dehydrated(false),
                        ]),
                    Step::make('detail_pribadi')
                        ->label(__('Detail Pribadi'))
                        ->description(__('Info kontak Anda'))
                        ->icon('heroicon-m-identification')
                        ->schema([
                            TextInput::make('full_name')
                                ->label(__('Nama Lengkap'))
                                ->required()
                                ->maxLength(255)
                                ->live(onBlur: true)
                                ->afterStateUpdated(function ($state, Set $set) {
                                    if (blank($state)) {
                                        return;
                                    }
                                    $parts = explode(' ', trim($state));
                                    $firstName = array_shift($parts);
                                    $lastName = count($parts) > 0 ? array_pop($parts) : '';
                                    $midName = count($parts) > 0 ? implode(' ', $parts) : '';
                                    $set('first_name', $firstName);
                                    $set('mid_name', $midName);
                                    $set('last_name', $lastName);
                                }),
                            TextInput::make('first_name')
                                ->label(__('Nama Depan'))
                                ->maxLength(255),
                            TextInput::make('mid_name')
                                ->label(__('Nama Tengah'))
                                ->maxLength(255),
                            TextInput::make('last_name')
                                ->label(__('Nama Belakang'))
                                ->maxLength(255),
                            TextInput::make('phone')
                                ->label(__('Nomor Telepon / WhatsApp'))
                                ->tel()
                                ->maxLength(255),
                            Select::make('gender')
                                ->label(__('Jenis Kelamin'))
                                ->options([
                                    'male' => __('Laki-laki'),
                                    'female' => __('Perempuan'),
                                ])
                                ->native(false),
                            Textarea::make('address')
                                ->label(__('Alamat'))
                                ->rows(3)
                                ->maxLength(65535)
                                ->columnSpanFull(),
                        ]),
                ])
                    ->submitAction(new HtmlString('<button type="submit" style="background-color: #e11d48; color: white; padding: 0.5rem 1.5rem; border-radius: 0.5rem; font-weight: 600; cursor: pointer; border: none; transition: background-color 0.2s;" onmouseover="this.style.backgroundColor=\'#be123c\'" onmouseout="this.style.backgroundColor=\'#e11d48\'">'.__('Daftar').'</button>')),
                Hidden::make('agreement'),
                Hidden::make('remember'),
            ])
            ->statePath('data');
    }

    protected function getFormActions(): array
    {
        return [];
    }

    protected function handleRegistration(array $data): User
    {
        // Enforce mandatory checkboxes (Agreement & Remember)
        if (! ($data['agreement'] ?? false)) {
            Notification::make()
                ->title(__('Perhatian'))
                ->body(__('Anda harus menyetujui syarat dan ketentuan untuk melanjutkan.'))
                ->warning()
                ->send();
            throw ValidationException::withMessages([
                'data.agreement' => __('Anda harus menyetujui syarat dan ketentuan untuk melanjutkan.'),
            ]);
        }

        if (! ($data['remember'] ?? false)) {
            Notification::make()
                ->title(__('Perhatian'))
                ->body(__('Anda harus mencentang Ingat Saya untuk melanjutkan.'))
                ->warning()
                ->send();
            throw ValidationException::withMessages([
                'data.remember' => __('Anda harus mencentang Ingat Saya untuk melanjutkan.'),
            ]);
        }

        $ip = request()->ip();

        $user = User::create([
            'avatar_url' => $data['avatar_url'] ?? null,
            'full_name' => $data['full_name'],
            'first_name' => $data['first_name'] ?? null,
            'mid_name' => $data['mid_name'] ?? null,
            'last_name' => $data['last_name'] ?? null,
            'username' => $data['username'],
            'email' => $data['email'],
            'password' => Hash::make($data['password'] ?? ''),
            'phone' => $data['phone'] ?? null,
            'gender' => $data['gender'] ?? null,
            'address' => $data['address'] ?? null,
            'ip_address' => $ip,
        ]);

        // Assign customer role automatically
        $customerRole = Role::where('name', 'customer')->first(['*']);
        if ($customerRole) {
            $user->assignRole($customerRole);
        }

        $location = app(GeoLocationService::class)->lookup($ip);
        $locationParts = array_filter([
            $location['city'] ?? null,
            $location['region'] ?? null,
            $location['country'] ?? null,
        ]);
        $locationText = $locationParts
            ? implode(', ', $locationParts)
            : __('Lokasi tidak diketahui');

        PlatformNotificationService::send(
            $user,
            __('Pendaftaran Berhasil'),
            __('Akun Anda telah terdaftar dari :ip (:location) pada :time.', [
                'ip' => $ip,
                'location' => $locationText,
                'time' => now()->format('d M Y H:i:s'),
            ])
        );

        Notification::make()
            ->title(__('Pendaftaran Berhasil'))
            ->body(__('Akun Anda Telah Terdaftar :ip (:location) pada :time.', [
                'ip' => $ip,
                'location' => $locationText,
                'time' => now()->format('d M Y H:i:s'),
            ]))
            ->success()
            ->send();

        Notification::make()
            ->title(__('Perhatian'))
            ->body(__('Account Anda Sudah Terdaftar Silahkan Ke Halaman Login.'))
            ->warning()
            ->send();

        return $user;
    }

    public function loginAction(): Action
    {
        return Action::make('login')
            ->label('')
            ->hidden();
    }
}
