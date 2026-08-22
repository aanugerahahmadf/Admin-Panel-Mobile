<?php

namespace App\Livewire;

use App\Models\User;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class CompleteProfileComponent extends Component implements HasForms
{
    use InteractsWithForms;

    public ?array $data = [];

    public function mount(): void
    {
        $user = Auth::user();
        if ($user) {
            $this->form->fill([
                'avatar_url' => filter_var($user->getRawOriginal('avatar_url'), FILTER_VALIDATE_URL) ? null : $user->getRawOriginal('avatar_url'),
                'full_name' => $user->full_name,
                'first_name' => $user->first_name,
                'mid_name' => $user->mid_name,
                'last_name' => $user->last_name,
                'username' => $user->username,
                'email' => $user->email,
                'whatsapp' => $user->whatsapp,
                'identity_type' => $user->identity_type ?? 'ktp',
                'ktp_number' => $user->ktp_number,
                'passport_number' => $user->passport_number,
                'sim_number' => $user->sim_number,
                'npwp_number' => $user->npwp_number,
                'birth_place' => $user->birth_place,
                'birth_date' => $user->birth_date,
                'country' => $user->country ?? 'Indonesia',
                'province_id' => $user->province_id,
                'city_id' => $user->city_id,
                'district_id' => $user->district_id,
                'village_id' => $user->village_id,
                'province_name' => $user->province_name,
                'city_name' => $user->city_name,
                'district_name' => $user->district_name,
                'village_name' => $user->village_name,
                'postal_code' => $user->postal_code,
                'address' => $user->address,
                'gender' => $user->gender,
                'religion' => $user->religion,
                'marital_status' => $user->marital_status,
                'mother_name' => $user->mother_name,
                'occupation' => $user->occupation,
                'income_range' => $user->income_range,
                'source_of_funds' => $user->source_of_funds,
                'ktp_photo' => $user->getRawOriginal('ktp_photo'),
                'selfie_photo' => $user->getRawOriginal('selfie_photo'),
            ]);
        }
    }

    public function form(Form $form): Form
    {
        return $form
            ->statePath('data')
            ->schema([
                Section::make(__('Foto Profil'))
                    ->icon('heroicon-o-camera')
                    ->schema([
                        FileUpload::make('avatar_url')
                            ->label(__(''))
                            ->image()
                            ->avatar()
                            ->imageEditor()
                            ->imageEditorAspectRatios(['1:1'])
                            ->directory('avatars')
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/gif'])
                            ->maxSize(5120)
                            ->columnSpanFull(),
                    ]),

                Section::make(__('Nama Lengkap'))
                    ->icon('heroicon-o-user')
                    ->schema([
                        TextInput::make('full_name')
                            ->label(__('Nama Lengkap'))
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($state, Set $set) {
                                if (blank($state)) return;
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
                            ->required()
                            ->maxLength(255),
                        TextInput::make('mid_name')
                            ->label(__('Nama Tengah'))
                            ->required()
                            ->maxLength(255),
                        TextInput::make('last_name')
                            ->label(__('Nama Belakang'))
                            ->required()
                            ->maxLength(255),
                    ])->columns(3),

                Section::make(__('Username'))
                    ->icon('heroicon-o-at-symbol')
                    ->schema([
                        TextInput::make('username')
                            ->label(__('Username'))
                            ->required()
                            ->unique(User::class, 'username', ignorable: Auth::user())
                            ->maxLength(255),
                    ]),

                Section::make(__('Nomor WhatsApp'))
                    ->icon('heroicon-o-chat-bubble-left-ellipsis')
                    ->description(__('Untuk notifikasi pembayaran via WhatsApp.'))
                    ->schema([
                        TextInput::make('whatsapp')
                            ->label(__('Nomor WhatsApp'))
                            ->tel()
                            ->required()
                            ->maxLength(255)
                            ->prefix('+62'),
                    ]),

                Section::make(__('Data Identitas'))
                    ->icon('heroicon-o-shield-check')
                    ->schema([
                        Select::make('identity_type')
                            ->label(__('Jenis Identitas'))
                            ->required()
                            ->options([
                                'ktp' => __('Kartu Tanda Penduduk (KTP)'),
                                'passport' => __('Passport'),
                                'sim' => __('Surat Izin Mengemudi (SIM)'),
                                'npwp' => __('Nomor Pokok Wajib Pajak (NPWP)'),
                            ])
                            ->native(false)
                            ->live(),
                        TextInput::make('ktp_number')
                            ->label(__('Nomor KTP'))
                            ->visible(fn (Get $get) => $get('identity_type') === 'ktp')
                            ->maxLength(20)
                            ->columnSpan(1),
                        TextInput::make('passport_number')
                            ->label(__('Nomor Passport'))
                            ->visible(fn (Get $get) => $get('identity_type') === 'passport')
                            ->maxLength(20)
                            ->columnSpan(1),
                        TextInput::make('sim_number')
                            ->label(__('Nomor SIM'))
                            ->visible(fn (Get $get) => $get('identity_type') === 'sim')
                            ->maxLength(20)
                            ->columnSpan(1),
                        TextInput::make('npwp_number')
                            ->label(__('Nomor NPWP'))
                            ->visible(fn (Get $get) => $get('identity_type') === 'npwp')
                            ->maxLength(20)
                            ->columnSpan(1),
                        FileUpload::make('ktp_photo')
                            ->label(fn (Get $get) => match ($get('identity_type')) {
                                'ktp' => __('Foto KTP'),
                                'passport' => __('Foto Passport'),
                                'sim' => __('Foto SIM'),
                                'npwp' => __('Foto NPWP'),
                                default => __('Foto Identitas'),
                            })
                            ->image()
                            ->maxSize(5120)
                            ->directory('ktp-photos')
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/jpg']),
                        FileUpload::make('selfie_photo')
                            ->label(fn (Get $get) => match ($get('identity_type')) {
                                'ktp' => __('Foto Selfie + KTP'),
                                'passport' => __('Foto Selfie + Passport'),
                                'sim' => __('Foto Selfie + SIM'),
                                'npwp' => __('Foto Selfie + NPWP'),
                                default => __('Foto Selfie + Identitas'),
                            })
                            ->image()
                            ->maxSize(5120)
                            ->directory('selfies')
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/jpg']),
                    ])->columns(2),

                Section::make(__('Tempat & Tanggal Lahir'))
                    ->icon('heroicon-o-map-pin')
                    ->schema([
                        TextInput::make('birth_place')
                            ->label(__('Tempat Lahir'))
                            ->maxLength(255),
                        DatePicker::make('birth_date')
                            ->label(__('Tanggal Lahir'))
                            ->native(false),
                    ])->columns(2),

                Section::make(__('Alamat'))
                    ->icon('heroicon-o-home')
                    ->schema([
                        TextInput::make('country')
                            ->label(__('Negara'))
                            ->default('Indonesia')
                            ->maxLength(255),
                        Select::make('province_id')
                            ->label(__('Provinsi'))
                            ->searchable()
                            ->native(false)
                            ->visible(fn (Get $get) => $get('country') === 'Indonesia')
                            ->options(fn () => \Laravolt\Indonesia\Models\Province::pluck('name', 'id'))
                            ->live(),
                        Select::make('city_id')
                            ->label(__('Kota/Kabupaten'))
                            ->searchable()
                            ->native(false)
                            ->visible(fn (Get $get) => $get('country') === 'Indonesia')
                            ->options(fn (Get $get) => $get('province_id')
                                ? \Laravolt\Indonesia\Models\City::where('province_code', \Laravolt\Indonesia\Models\Province::find($get('province_id'))?->code)->pluck('name', 'id')
                                : [])
                            ->live(),
                        Select::make('district_id')
                            ->label(__('Kecamatan'))
                            ->searchable()
                            ->native(false)
                            ->visible(fn (Get $get) => $get('country') === 'Indonesia')
                            ->options(fn (Get $get) => $get('city_id')
                                ? \Laravolt\Indonesia\Models\District::where('city_code', \Laravolt\Indonesia\Models\City::find($get('city_id'))?->code)->pluck('name', 'id')
                                : [])
                            ->live(),
                        Select::make('village_id')
                            ->label(__('Kelurahan/Desa'))
                            ->searchable()
                            ->native(false)
                            ->visible(fn (Get $get) => $get('country') === 'Indonesia')
                            ->options(fn (Get $get) => $get('district_id')
                                ? \Laravolt\Indonesia\Models\Village::where('district_code', \Laravolt\Indonesia\Models\District::find($get('district_id'))?->code)->pluck('name', 'id')
                                : []),
                        TextInput::make('province_name')
                            ->label(__('Provinsi / State'))
                            ->visible(fn (Get $get) => $get('country') !== null && $get('country') !== 'Indonesia'),
                        TextInput::make('city_name')
                            ->label(__('Kota / City'))
                            ->visible(fn (Get $get) => $get('country') !== null && $get('country') !== 'Indonesia'),
                        TextInput::make('district_name')
                            ->label(__('Kecamatan / District'))
                            ->visible(fn (Get $get) => $get('country') !== null && $get('country') !== 'Indonesia'),
                        TextInput::make('village_name')
                            ->label(__('Kelurahan / Village'))
                            ->visible(fn (Get $get) => $get('country') !== null && $get('country') !== 'Indonesia'),
                        TextInput::make('postal_code')
                            ->label(__('Kode Pos'))
                            ->maxLength(10)
                            ->visible(fn (Get $get) => $get('country') === 'Indonesia'),
                        Textarea::make('address')
                            ->label(__('Alamat Lengkap'))
                            ->required()
                            ->rows(3)
                            ->columnSpanFull(),
                    ])->columns(2),

                Section::make(__('Data Diri'))
                    ->icon('heroicon-o-user-group')
                    ->schema([
                        Select::make('gender')
                            ->label(__('Jenis Kelamin'))
                            ->required()
                            ->options([
                                'Pria' => __('Pria'),
                                'Wanita' => __('Wanita'),
                            ])
                            ->native(false),
                        Select::make('religion')
                            ->label(__('Agama'))
                            ->options([
                                'Islam' => __('Islam'),
                                'Kristen' => __('Kristen'),
                                'Katolik' => __('Katolik'),
                                'Hindu' => __('Hindu'),
                                'Buddha' => __('Buddha'),
                                'Konghucu' => __('Konghucu'),
                            ])
                            ->native(false),
                        Select::make('marital_status')
                            ->label(__('Status Pernikahan'))
                            ->options([
                                'Belum Menikah' => __('Belum Menikah'),
                                'Menikah' => __('Menikah'),
                                'Cerai' => __('Cerai'),
                            ])
                            ->native(false),
                        TextInput::make('mother_name')
                            ->label(__('Nama Ibu Kandung'))
                            ->maxLength(255),
                        Select::make('occupation')
                            ->label(__('Pekerjaan'))
                            ->required()
                            ->options([
                                'Karyawan' => __('Karyawan'),
                                'Wiraswasta' => __('Wiraswasta'),
                                'Pelajar/Mahasiswa' => __('Pelajar/Mahasiswa'),
                                'Ibu Rumah Tangga' => __('Ibu Rumah Tangga'),
                                'Profesional' => __('Profesional'),
                                'Lainnya' => __('Lainnya'),
                            ])
                            ->native(false),
                        Select::make('income_range')
                            ->label(__('Rentang Penghasilan'))
                            ->options([
                                '< Rp 1 Juta' => __('< Rp 1 Juta'),
                                'Rp 1-5 Juta' => __('Rp 1-5 Juta'),
                                'Rp 5-10 Juta' => __('Rp 5-10 Juta'),
                                'Rp 10-50 Juta' => __('Rp 10-50 Juta'),
                                '> Rp 50 Juta' => __('> Rp 50 Juta'),
                            ])
                            ->native(false),
                        Select::make('source_of_funds')
                            ->label(__('Sumber Dana'))
                            ->options([
                                'Gaji' => __('Gaji'),
                                'Bisnis/Usaha' => __('Bisnis/Usaha'),
                                'Investasi' => __('Investasi'),
                                'Hadiah/Warisan' => __('Hadiah/Warisan'),
                                'Lainnya' => __('Lainnya'),
                            ])
                            ->native(false),
                    ])->columns(3),
            ]);
    }

    public function save(): void
    {
        try {
            $data = $this->form->getState();
            $user = Auth::user();

            if (empty($data['avatar_url'])) {
                unset($data['avatar_url']);
            }

            $user->update($data);

            Notification::make()
                ->title(__('Profil berhasil dilengkapi!'))
                ->success()
                ->send();

            $this->redirect()->route('filament.user.pages.home');
        } catch (\Exception $e) {
            Notification::make()
                ->title(__('Gagal menyimpan profil'))
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function render(): View
    {
        return view('livewire.complete-profile-component');
    }
}
