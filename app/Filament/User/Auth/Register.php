<?php

namespace App\Filament\User\Auth;

use App\Models\User;
use App\Services\GeoLocationService;
use App\Services\GeoNamesService;
use App\Services\PlatformNotificationService;
use App\Services\WorldRegionService;
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
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Pages\Auth\Register as BaseRegister;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\HtmlString;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Laravolt\Indonesia\Models\City as IndonesiaCity;
use Laravolt\Indonesia\Models\District as IndonesiaDistrict;
use Laravolt\Indonesia\Models\Province as IndonesiaProvince;
use Laravolt\Indonesia\Models\Village as IndonesiaVillage;
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
                                ->label('')
                                ->image()
                                ->avatar()
                                ->directory('avatars')
                                ->alignCenter()
                                ->columnSpanFull()
                                ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/gif'])
                                ->maxSize(5120)
                                ->imageEditor()
                                ->imageEditorAspectRatios(['1:1'])
                                ->extraAttributes(['class' => 'flex flex-col items-center justify-center'])
                                ->extraInputAttributes([
                                    // 'image/*' → mobile browsers show: Camera / Gallery / Files sheet
                                    'accept' => 'image/*',
                                    // 'environment' = rear camera as default on mobile;
                                    // but we leave it empty so the OS shows the full picker sheet
                                    // (Camera + Gallery + Drive). Setting capture="environment" would
                                    // skip gallery entirely on some Android browsers.
                                    'class' => 'avatar-file-input',
                                ])
                                ->extraFieldWrapperAttributes(['class' => 'avatar-upload-centered']),
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
                                ->rule(Password::min(12)
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
                            TextInput::make('whatsapp')
                                ->label(__('Nomor WhatsApp'))
                                ->tel()
                                ->maxLength(255),
                            TextInput::make('birth_place')
                                ->label(__('Tempat Lahir'))
                                ->maxLength(255),
                            TextInput::make('birth_date')
                                ->label(__('Tanggal Lahir'))
                                ->type('date')
                                ->maxLength(255),
                            Select::make('gender')
                                ->label(__('Jenis Kelamin'))
                                ->options([
                                    'Pria' => __('Pria'),
                                    'Wanita' => __('Wanita'),
                                ])
                                ->native(false),
                        ]),
                    Step::make('alamat')
                        ->label(__('Alamat'))
                        ->description(__('Alamat lengkap'))
                        ->icon('heroicon-m-map-pin')
                        ->schema([
                            Select::make('country')
                                ->label(__('Negara'))
                                ->searchable()
                                ->native(false)
                                ->live()
                                ->options(config('countries')),
                            // Indonesia: cascading dropdown
                            Select::make('province_id')
                                ->label(__('Provinsi'))
                                ->searchable()
                                ->native(false)
                                ->live()
                                ->visible(fn (Get $get) => $get('country') === 'Indonesia')
                                ->options(fn () => IndonesiaProvince::pluck('name', 'id'))
                                ->afterStateUpdated(function (Set $set) {
                                    $set('city_id', null);
                                    $set('district_id', null);
                                    $set('village_id', null);
                                    $set('province_name', null);
                                    $set('city_name', null);
                                    $set('district_name', null);
                                    $set('village_name', null);
                                }),
                            Select::make('city_id')
                                ->label(__('Kota / Kabupaten'))
                                ->searchable()
                                ->native(false)
                                ->live()
                                ->visible(fn (Get $get) => $get('country') === 'Indonesia')
                                ->options(fn (Get $get) => $get('province_id')
                                    ? IndonesiaCity::where('province_code', IndonesiaProvince::find($get('province_id'))?->code)
                                        ->pluck('name', 'id')
                                    : [])
                                ->afterStateUpdated(function (Set $set) {
                                    $set('district_id', null);
                                    $set('village_id', null);
                                    $set('city_name', null);
                                    $set('district_name', null);
                                    $set('village_name', null);
                                }),
                            Select::make('district_id')
                                ->label(__('Kecamatan'))
                                ->searchable()
                                ->native(false)
                                ->live()
                                ->visible(fn (Get $get) => $get('country') === 'Indonesia')
                                ->options(fn (Get $get) => $get('city_id')
                                    ? IndonesiaDistrict::where('city_code', IndonesiaCity::find($get('city_id'))?->code)
                                        ->pluck('name', 'id')
                                    : [])
                                ->afterStateUpdated(function (Set $set) {
                                    $set('village_id', null);
                                    $set('district_name', null);
                                    $set('village_name', null);
                                }),
                            Select::make('village_id')
                                ->label(__('Kelurahan / Desa'))
                                ->searchable()
                                ->native(false)
                                ->visible(fn (Get $get) => $get('country') === 'Indonesia')
                                ->options(fn (Get $get) => $get('district_id')
                                    ? IndonesiaVillage::where('district_code', IndonesiaDistrict::find($get('district_id'))?->code)
                                        ->pluck('name', 'id')
                                    : []),
                            // Non-Indonesia: real data from WorldRegionService
                            Select::make('province_name')
                                ->label(__('Provinsi'))
                                ->searchable()
                                ->native(false)
                                ->live()
                                ->getSearchResultsUsing(function (string $search, Get $get) {
                                    $country = $get('country');
                                    if (! $country) {
                                        return [];
                                    }
                                    $states = app(WorldRegionService::class)->getStates($country);

                                    return collect($states)
                                        ->when($search, fn ($col) => $col->filter(fn ($s) => str_contains(
                                            strtolower($s['name'] ?? $s),
                                            strtolower($search)
                                        )))
                                        ->pluck('name', 'name')
                                        ->toArray();
                                })
                                ->getOptionLabelUsing(fn ($value) => $value)
                                ->afterStateUpdated(fn (Set $set) => $set('city_name', null))
                                ->visible(fn (Get $get) => $get('country') !== null && $get('country') !== 'Indonesia'),
                            Select::make('city_name')
                                ->label(__('Kota / Kabupaten'))
                                ->searchable()
                                ->native(false)
                                ->getSearchResultsUsing(function (string $search, Get $get) {
                                    $country = $get('country');
                                    $state = $get('province_name');
                                    if (! $country || ! $state) {
                                        return [];
                                    }
                                    $cities = app(WorldRegionService::class)->getCities($country, $state);

                                    return collect($cities)
                                        ->when($search, fn ($col) => $col->filter(fn ($c) => str_contains(
                                            strtolower($c),
                                            strtolower($search)
                                        )))
                                        ->mapWithKeys(fn ($city) => [$city => $city])
                                        ->toArray();
                                })
                                ->getOptionLabelUsing(fn ($value) => $value)
                                ->visible(fn (Get $get) => $get('country') !== null && $get('country') !== 'Indonesia'),
                            Select::make('district_name')
                                ->label(__('Kecamatan'))
                                ->searchable()
                                ->native(false)
                                ->live()
                                ->getSearchResultsUsing(function (string $search, Get $get) {
                                    $country = $get('country');
                                    $state = $get('province_name');
                                    if (! $country || ! $state) {
                                        return $search ? [$search => $search] : [];
                                    }
                                    $districts = app(GeoNamesService::class)->getAdmin2ByStateName($country, $state);
                                    $results = collect($districts)
                                        ->when($search, fn ($col) => $col->filter(fn ($d) => str_contains(
                                            strtolower($d['name']),
                                            strtolower($search)
                                        )))
                                        ->pluck('name', 'name')
                                        ->toArray();
                                    if (empty($results) && $search) {
                                        return [$search => $search];
                                    }

                                    return $results;
                                })
                                ->getOptionLabelUsing(fn ($value) => $value)
                                ->afterStateUpdated(fn (Set $set) => $set('village_name', null))
                                ->visible(fn (Get $get) => $get('country') !== null && $get('country') !== 'Indonesia'),
                            Select::make('village_name')
                                ->label(__('Kelurahan / Desa'))
                                ->searchable()
                                ->native(false)
                                ->getSearchResultsUsing(function (string $search, Get $get) {
                                    $country = $get('country');
                                    $state = $get('province_name');
                                    $district = $get('district_name');
                                    if (! $country || ! $state || ! $district) {
                                        return $search ? [$search => $search] : [];
                                    }
                                    $villages = app(GeoNamesService::class)->getAdmin3ByDistrictName($country, $state, $district);
                                    $results = collect($villages)
                                        ->when($search, fn ($col) => $col->filter(fn ($v) => str_contains(
                                            strtolower($v['name']),
                                            strtolower($search)
                                        )))
                                        ->pluck('name', 'name')
                                        ->toArray();
                                    if (empty($results) && $search) {
                                        return [$search => $search];
                                    }

                                    return $results;
                                })
                                ->getOptionLabelUsing(fn ($value) => $value)
                                ->visible(fn (Get $get) => $get('country') !== null && $get('country') !== 'Indonesia'),
                            TextInput::make('postal_code')
                                ->label(__('Kode Pos'))
                                ->maxLength(10)
                                ->visible(fn (Get $get) => $get('country') === 'Indonesia'),
                            Select::make('postal_code')
                                ->label(__('Kode Pos'))
                                ->searchable()
                                ->native(false)
                                ->getSearchResultsUsing(function (string $search, Get $get) {
                                    $country = $get('country');
                                    $city = $get('city_name');
                                    if (! $country || ! $city) {
                                        return $search ? [$search => $search] : [];
                                    }
                                    $codes = app(GeoNamesService::class)->searchPostalCodes($country, $city);
                                    $results = collect($codes)
                                        ->when($search, fn ($col) => $col->filter(fn ($p) => str_contains(
                                            $p['postal_code'],
                                            $search
                                        )))
                                        ->mapWithKeys(fn ($p) => [$p['postal_code'] => $p['postal_code'].' — '.$p['place_name']])
                                        ->toArray();
                                    if (empty($results) && $search) {
                                        return [$search => $search];
                                    }

                                    return $results;
                                })
                                ->getOptionLabelUsing(fn ($value) => $value)
                                ->visible(fn (Get $get) => $get('country') !== null && $get('country') !== 'Indonesia'),
                            Textarea::make('address')
                                ->label(__('Detail Alamat'))
                                ->rows(3)
                                ->maxLength(65535)
                                ->columnSpanFull(),
                        ]),
                    Step::make('verifikasi_identitas')
                        ->label(__('Verifikasi Identitas'))
                        ->description(__('Upload identitas dan selfie'))
                        ->icon('heroicon-m-shield-check')
                        ->schema([
                            Select::make('identity_type')
                                ->label(__('Jenis Identitas'))
                                ->searchable()
                                ->native(false)
                                ->live()
                                ->options([
                                    'ktp' => __('Kartu Tanda Kependudukan (KTP)'),
                                    'passport' => __('Passport'),
                                    'sim' => __('Surat Izin Mengemudi (SIM)'),
                                    'npwp' => __('Nomor Pokok Wajib Pajak (NPWP)'),
                                ])
                                ->default('ktp')
                                ->columnSpanFull(),
                            TextInput::make('nik')
                                ->label(__('Nomer Induk Kependudukan (NIK)'))
                                ->visible(fn (Get $get) => $get('identity_type') === 'ktp')
                                ->maxLength(20)
                                ->columnSpanFull(),
                            TextInput::make('passport_number')
                                ->label(__('Nomer Passport'))
                                ->visible(fn (Get $get) => $get('identity_type') === 'passport')
                                ->maxLength(20)
                                ->columnSpanFull(),
                            TextInput::make('sim_number')
                                ->label(__('Nomor SIM'))
                                ->visible(fn (Get $get) => $get('identity_type') === 'sim')
                                ->maxLength(20)
                                ->columnSpanFull(),
                            TextInput::make('npwp_number')
                                ->label(__('Nomor NPWP'))
                                ->visible(fn (Get $get) => $get('identity_type') === 'npwp')
                                ->maxLength(20)
                                ->columnSpanFull(),
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
                                ->columnSpanFull(),
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
                                ->helperText(fn (Get $get) => match ($get('identity_type')) {
                                    'ktp' => __('Foto diri Anda sambil memegang KTP'),
                                    'passport' => __('Foto diri Anda sambil memegang Passport'),
                                    'sim' => __('Foto diri Anda sambil memegang SIM'),
                                    'npwp' => __('Foto diri Anda sambil memegang NPWP'),
                                    default => __('Foto diri Anda sambil memegang identitas'),
                                })
                                ->columnSpanFull(),
                        ]),
                ])
                    ->submitAction(new HtmlString(
                        '<button type="submit"'
                        .' class="fi-btn fi-btn-size-md fi-color-custom fi-btn-color-primary fi-color-primary'
                        .' inline-flex items-center justify-center gap-1.5 font-semibold rounded-lg'
                        .' px-4 py-2 text-sm shadow-sm w-full'
                        .' bg-custom-600 text-white hover:bg-custom-500 focus:ring-custom-500/50 dark:bg-custom-500 dark:hover:bg-custom-400"'
                        .' style="--c-400:var(--primary-400);--c-500:var(--primary-500);--c-600:var(--primary-600);"'
                        .'>'
                        .__('Daftar')
                        .'</button>'
                    )),
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
            'whatsapp' => $data['whatsapp'] ?? null,
            'nik' => $data['nik'] ?? null,
            'passport_number' => $data['passport_number'] ?? null,
            'sim_number' => $data['sim_number'] ?? null,
            'npwp_number' => $data['npwp_number'] ?? null,
            'identity_type' => $data['identity_type'] ?? 'ktp',
            'birth_place' => $data['birth_place'] ?? null,
            'birth_date' => $data['birth_date'] ?? null,
            'country' => $data['country'] ?? null,
            'province_id' => $data['province_id'] ?? null,
            'city_id' => $data['city_id'] ?? null,
            'district_id' => $data['district_id'] ?? null,
            'village_id' => $data['village_id'] ?? null,
            'province_name' => $data['province_name'] ?? ($data['country'] === 'Indonesia' ? IndonesiaProvince::find($data['province_id'])?->name : null),
            'city_name' => $data['city_name'] ?? ($data['country'] === 'Indonesia' ? IndonesiaCity::find($data['city_id'])?->name : null),
            'district_name' => $data['district_name'] ?? ($data['country'] === 'Indonesia' ? IndonesiaDistrict::find($data['district_id'])?->name : null),
            'village_name' => $data['village_name'] ?? ($data['country'] === 'Indonesia' ? IndonesiaVillage::find($data['village_id'])?->name : null),
            'postal_code' => $data['postal_code'] ?? null,
            'ktp_photo' => $data['ktp_photo'] ?? null,
            'selfie_photo' => $data['selfie_photo'] ?? null,
            'gender' => $data['gender'] ?? null,
            'address' => $data['address'] ?? null,
            'ip_address' => $ip,
        ]);

        if (! empty($data['selfie_photo'])) {
            $user->update(['identity_verified_at' => now()]);
        }

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
