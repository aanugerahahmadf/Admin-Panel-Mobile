<?php

namespace App\Filament\Auth;

use App\Models\User;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\Wizard\Step;
use Filament\Forms\Form;
use Filament\Pages\Auth\Register as BaseRegister;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\HtmlString;
use Spatie\Permission\Models\Role;

class Register extends BaseRegister
{
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Wizard::make([
                    Step::make('Akun')
                        ->description(__('Info akun dasar'))
                        ->icon('heroicon-m-user-circle')
                        ->schema([
                            TextInput::make('username')
                                ->label(__('Username'))
                                ->required()
                                ->unique(User::class)
                                ->maxLength(255),
                            $this->getEmailFormComponent(),
                            $this->getPasswordFormComponent(),
                            $this->getPasswordConfirmationFormComponent(),
                        ]),
                    Step::make('Detail Pribadi')
                        ->description(__('Info kontak Anda'))
                        ->icon('heroicon-m-identification')
                        ->schema([
                            TextInput::make('first_name')
                                ->label(__('Nama Depan'))
                                ->required(),
                            TextInput::make('last_name')
                                ->label(__('Nama Belakang'))
                                ->required(),
                            TextInput::make('phone')
                                ->label(__('Nomor Telepon'))
                                ->tel()
                                ->required(),
                            Textarea::make('address')
                                ->label(__('Alamat Lengkap'))
                                ->required()
                                ->rows(3),
                        ]),
                ])
                    ->submitAction(new HtmlString('<button type="submit" style="background-color: #e11d48; color: white; padding: 0.5rem 1.5rem; border-radius: 0.5rem; font-weight: 600; cursor: pointer; border: none; transition: background-color 0.2s;" onmouseover="this.style.backgroundColor=\'#be123c\'" onmouseout="this.style.backgroundColor=\'#e11d48\'">Daftar</button>')),
            ])
            ->statePath('data');
    }

    protected function getFormActions(): array
    {
        return [];
    }

    protected function handleRegistration(array $data): User
    {
        $user = User::create([
            'name' => trim(($data['first_name'] ?? '').' '.($data['last_name'] ?? '')),
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'username' => $data['username'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'phone' => $data['phone'] ?? null,
            'address' => $data['address'] ?? null,
        ]);

        // Assign customer role automatically
        $customerRole = Role::where('name', 'customer')->first();
        if ($customerRole) {
            $user->assignRole($customerRole);
        }

        return $user;
    }
}
