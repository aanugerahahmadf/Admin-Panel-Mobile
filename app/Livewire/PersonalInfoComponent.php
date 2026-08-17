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
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Laravolt\Indonesia\Models\City as IndonesiaCity;
use Laravolt\Indonesia\Models\District as IndonesiaDistrict;
use Laravolt\Indonesia\Models\Province as IndonesiaProvince;
use Laravolt\Indonesia\Models\Village as IndonesiaVillage;
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

            $ktpNumber = $user->ktp_number;
            $identityType = $user->identity_type ?? (($ktpNumber && preg_match('/^\d{16}$/', $ktpNumber)) ? 'ktp' : 'passport');

            $this->form->fill([
                'identity_type' => $identityType,
                'avatar_url' => $avatarValue,
                'full_name' => $user->full_name,
                'first_name' => $user->first_name,
                'mid_name' => $user->mid_name,
                'last_name' => $user->last_name,
                'email' => $user->email,
                'whatsapp' => $user->whatsapp,
                'gender' => $user->gender,
                'religion' => $user->religion,
                'marital_status' => $user->marital_status,
                'mother_name' => $user->mother_name,
                'occupation' => $user->occupation,
                'income_range' => $user->income_range,
                'source_of_funds' => $user->source_of_funds,
                'address' => $user->address,
                'ktp_number' => $ktpNumber,
                'passport_number' => $user->passport_number,
                'sim_number' => $user->sim_number,
                'npwp_number' => $user->npwp_number,
                'birth_place' => $user->birth_place,
                'birth_date' => $user->birth_date,
                'country' => $user->country,
                'province_id' => $user->province_id,
                'city_id' => $user->city_id,
                'district_id' => $user->district_id,
                'village_id' => $user->village_id,
                'province_name' => $user->province_name,
                'city_name' => $user->city_name,
                'district_name' => $user->district_name,
                'village_name' => $user->village_name,
                'postal_code' => $user->postal_code,
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
                Section::make(__('Informasi Profil'))
                    ->aside()
                    ->icon('heroicon-o-user-circle')
                    ->description(__('Perbarui informasi profil dan alamat email akun Anda.'))
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
                            ->extraAttributes(['class' => 'flex flex-col items-center justify-center'])
                            ->extraInputAttributes([
                                'accept' => 'image/*',
                                'class' => 'avatar-file-input',
                            ])
                            ->extraFieldWrapperAttributes(['class' => 'avatar-upload-centered'])
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
                        TextInput::make('whatsapp')
                            ->label(__('Nomor WhatsApp'))
                            ->tel()
                            ->maxLength(255)
                            ->prefixIcon('heroicon-o-chat-bubble-left-ellipsis')
                            ->helperText(__('Untuk notifikasi pembayaran via WhatsApp. Kosongkan jika sama dengan nomor telepon.')),
                        Select::make('gender')
                            ->label(__('Jenis Kelamin'))
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
                        Textarea::make('address')
                            ->label(__('Alamat'))
                            ->rows(3)
                            ->maxLength(65535)
                            ->columnSpanFull(),
                    ]),
                Section::make(__('Data Identitas'))
                    ->aside()
                    ->icon('heroicon-o-shield-check')
                    ->description(__('Data identitas sesuai KTP/Passport (tidak dapat diubah)'))
                    ->schema([
                        Select::make('identity_type')
                            ->label(__('Jenis Identitas'))
                            ->searchable()
                            ->native(false)
                            ->disabled()
                            ->options([
                                'ktp' => __('Kartu Tanda Kependudukan (KTP)'),
                                'passport' => __('Passport'),
                                'sim' => __('Surat Izin Mengemudi (SIM)'),
                                'npwp' => __('Nomor Pokok Wajib Pajak (NPWP)'),
                            ])
                            ->columnSpan(1),
                        TextInput::make('ktp_number')
                            ->label(__('Nomor KTP'))
                            ->disabled()
                            ->visible(fn (Get $get) => $get('identity_type') === 'ktp')
                            ->maxLength(20)
                            ->columnSpan(1),
                        TextInput::make('passport_number')
                            ->label(__('Nomer Passport'))
                            ->disabled()
                            ->visible(fn (Get $get) => $get('identity_type') === 'passport')
                            ->maxLength(20)
                            ->columnSpan(1),
                        TextInput::make('sim_number')
                            ->label(__('Nomor SIM'))
                            ->disabled()
                            ->visible(fn (Get $get) => $get('identity_type') === 'sim')
                            ->maxLength(20)
                            ->columnSpan(1),
                        TextInput::make('npwp_number')
                            ->label(__('Nomor NPWP'))
                            ->disabled()
                            ->visible(fn (Get $get) => $get('identity_type') === 'npwp')
                            ->maxLength(20)
                            ->columnSpan(1),
                        TextInput::make('birth_place')
                            ->label(__('Tempat Lahir'))
                            ->disabled()
                            ->maxLength(255)
                            ->columnSpan(1),
                        TextInput::make('birth_date')
                            ->label(__('Tanggal Lahir'))
                            ->disabled()
                            ->type('date')
                            ->columnSpan(1),
                        TextInput::make('country')
                            ->label(__('Negara'))
                            ->disabled()
                            ->maxLength(255)
                            ->columnSpan(1),
                        Select::make('province_id')
                            ->label(__('Provinsi'))
                            ->disabled()
                            ->searchable()
                            ->native(false)
                            ->visible(fn (Get $get) => $get('country') === 'Indonesia')
                            ->options(fn () => IndonesiaProvince::pluck('name', 'id'))
                            ->columnSpan(1),
                        Select::make('city_id')
                            ->label(__('Kota/Kabupaten'))
                            ->disabled()
                            ->searchable()
                            ->native(false)
                            ->visible(fn (Get $get) => $get('country') === 'Indonesia')
                            ->options(fn (Get $get) => $get('province_id')
                                ? IndonesiaCity::where('province_code', IndonesiaProvince::find($get('province_id'))?->code)
                                    ->pluck('name', 'id')
                                : [])
                            ->columnSpan(1),
                        Select::make('district_id')
                            ->label(__('Kecamatan'))
                            ->disabled()
                            ->searchable()
                            ->native(false)
                            ->visible(fn (Get $get) => $get('country') === 'Indonesia')
                            ->options(fn (Get $get) => $get('city_id')
                                ? IndonesiaDistrict::where('city_code', IndonesiaCity::find($get('city_id'))?->code)
                                    ->pluck('name', 'id')
                                : [])
                            ->columnSpan(1),
                        Select::make('village_id')
                            ->label(__('Kelurahan/Desa'))
                            ->disabled()
                            ->searchable()
                            ->native(false)
                            ->visible(fn (Get $get) => $get('country') === 'Indonesia')
                            ->options(fn (Get $get) => $get('district_id')
                                ? IndonesiaVillage::where('district_code', IndonesiaDistrict::find($get('district_id'))?->code)
                                    ->pluck('name', 'id')
                                : [])
                            ->columnSpan(1),
                        Select::make('province_name')
                            ->label(__('Provinsi / State'))
                            ->disabled()
                            ->searchable()
                            ->native(false)
                            ->options(fn () => [])
                            ->visible(fn (Get $get) => $get('country') !== null && $get('country') !== 'Indonesia')
                            ->columnSpan(1),
                        Select::make('city_name')
                            ->label(__('Kota / City'))
                            ->disabled()
                            ->searchable()
                            ->native(false)
                            ->options(fn () => [])
                            ->visible(fn (Get $get) => $get('country') !== null && $get('country') !== 'Indonesia')
                            ->columnSpan(1),
                        Select::make('district_name')
                            ->label(__('Kecamatan / District'))
                            ->disabled()
                            ->searchable()
                            ->native(false)
                            ->options(fn () => [])
                            ->visible(fn (Get $get) => $get('country') !== null && $get('country') !== 'Indonesia')
                            ->columnSpan(1),
                        Select::make('village_name')
                            ->label(__('Kelurahan / Village'))
                            ->disabled()
                            ->searchable()
                            ->native(false)
                            ->options(fn () => [])
                            ->visible(fn (Get $get) => $get('country') !== null && $get('country') !== 'Indonesia')
                            ->columnSpan(1),
                        TextInput::make('postal_code')
                            ->label(__('Kode Pos / Postal Code'))
                            ->disabled()
                            ->maxLength(10)
                            ->visible(fn (Get $get) => $get('country') === 'Indonesia')
                            ->columnSpan(1),
                        Select::make('postal_code')
                            ->label(__('Kode Pos / Postal Code'))
                            ->disabled()
                            ->searchable()
                            ->native(false)
                            ->options(fn () => [])
                            ->visible(fn (Get $get) => $get('country') !== null && $get('country') !== 'Indonesia')
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
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/jpg'])
                            ->columnSpan(1),
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
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/jpg'])
                            ->helperText(fn (Get $get) => match ($get('identity_type')) {
                                'ktp' => __('Foto diri Anda sambil memegang KTP'),
                                'passport' => __('Foto diri Anda sambil memegang Passport'),
                                'sim' => __('Foto diri Anda sambil memegang SIM'),
                                'npwp' => __('Foto diri Anda sambil memegang NPWP'),
                                default => __('Foto diri Anda sambil memegang identitas'),
                            })
                            ->columnSpan(1),
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

            if (! empty($data['selfie_photo']) && $data['selfie_photo'] !== $user->getRawOriginal('selfie_photo')) {
                $data['identity_verified_at'] = now();
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
