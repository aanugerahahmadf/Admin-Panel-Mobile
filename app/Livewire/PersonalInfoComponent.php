<?php

namespace App\Livewire;

use App\Models\User;
use App\Providers\NativeServiceProvider;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Native\Mobile\Notification as NativeNotification;

/**
 * @mixin Component
 */
class PersonalInfoComponent extends Component implements HasForms
{
    use InteractsWithForms;

    public ?array $data = [];

    protected static int $sort = 1;

    public static function getSort(): int
    {
        return static::$sort;
    }

    public function mount(): void
    {
        $user = Auth::user();
        if ($user) {
            $rawAvatar = $user->getRawOriginal('avatar_url');

            $avatarValue = filter_var($rawAvatar, FILTER_VALIDATE_URL) ? null : $rawAvatar;

            $this->form->fill([
                'avatar_url' => $avatarValue,
                'full_name' => $user->full_name,
                'first_name' => $user->first_name,
                'mid_name' => $user->mid_name,
                'last_name' => $user->last_name,
                'email' => $user->email,
                'phone' => $user->phone,
                'gender' => $user->gender,
                'address' => $user->address,
            ]);
        }
    }

    public function form(Form $form): Form
    {
        return $form
            ->statePath('data')
            ->schema([
                Section::make(__('Informasi Profil'))
                    ->aside()
                    ->icon('heroicon-o-user-circle')
                    ->description(__('Perbarui informasi profil dan alamat email akun Anda.'))
                    ->schema([
                        FileUpload::make('avatar_url')
                            ->label(__('Foto Profil'))
                            ->image()
                            ->avatar()
                            ->imageEditor()
                            ->directory('avatars')
                            ->extraAttributes(['class' => 'flex flex-col items-center justify-center'])
                            ->alignCenter()
                            ->columnSpanFull(),
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
                        TextInput::make('email')
                            ->label(__('Email'))
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->unique(User::class, 'email', ignorable: Auth::user()),
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
            ]);
    }

    public function save(): void
    {
        try {
            $data = $this->form->getState();
            $user = Auth::user();

            // Jika avatar_url kosong (tidak upload baru), jangan hapus foto lama (terutama foto Google)
            if (empty($data['avatar_url'])) {
                unset($data['avatar_url']);
            }

            $user->update($data);

            Notification::make()
                ->title(__('Profil berhasil diperbarui!'))
                ->success()
                ->send();

            // Notifikasi Native jika di mobile
            if (app()->environment('mobile') || NativeServiceProvider::isNativeMobile()) {
                NativeNotification::new()
                    ->title(__('Profil Diperbarui'))
                    ->message(__('Data pribadi Anda telah berhasil disimpan.'))
                    ->show();
            }

            $this->dispatch('profile-updated');
        } catch (\Exception $e) {
            Notification::make()
                ->title(__('Gagal memperbarui profil'))
                ->danger()
                ->send();
        }
    }

    public function render(): View
    {
        return view('livewire.personal-info-component');
    }
}
