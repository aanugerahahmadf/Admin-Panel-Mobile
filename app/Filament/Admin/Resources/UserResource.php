<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Hash;

/**
 * @mixin \Eloquent
 *
 * @property-read User $record
 */
class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?int $navigationSort = 7;

    public static function getNavigationGroup(): ?string
    {
        return __('Data Master');
    }

    public static function getNavigationLabel(): string
    {
        return __('Pengguna');
    }

    public static function getModelLabel(): string
    {
        return __('Pengguna');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Pengguna');
    }

    public static function getNavigationBadge(): ?string
    {
        /** @var Builder $query */
        $query = static::$model::query();

        return (string) $query->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'primary';
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return __('Total Pengguna Terdaftar');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make(__('Data Pribadi'))
                            ->description(__('Informasi profil detail pengguna.'))
                            ->icon('heroicon-o-user')
                            ->schema([
                                Forms\Components\FileUpload::make('avatar_url')
                                    ->label(__('Avatar Profil'))
                                    ->image()
                                    ->avatar()
                                    ->imageEditor()
                                    ->circleCropper()
                                    ->columnSpanFull()
                                    ->alignCenter(),
                                Forms\Components\TextInput::make('full_name')
                                    ->label(__('Nama Lengkap'))
                                    ->required()
                                    ->maxLength(255)
                                    ->prefixIcon('heroicon-o-user-circle')
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
                                    })
                                    ->columnSpanFull(),
                                Forms\Components\TextInput::make('first_name')
                                    ->label(__('Nama Depan'))
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('mid_name')
                                    ->label(__('Nama Tengah'))
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('last_name')
                                    ->label(__('Nama Belakang'))
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('phone')
                                    ->label(__('Nomor Telepon'))
                                    ->tel()
                                    ->prefixIcon('heroicon-o-phone')
                                    ->maxLength(255)
                                    ->columnSpanFull(),
                                Forms\Components\Textarea::make('address')
                                    ->label(__('Alamat Tempat Tinggal'))
                                    ->rows(3)
                                    ->columnSpanFull(),
                            ])->columns(2),
                    ])->columnSpan(['lg' => 2]),

                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make(__('Akses & Akun'))
                            ->description(__('Manajemen login, keamanan, dan perizinan.'))
                            ->icon('heroicon-o-shield-check')
                            ->schema([
                                Forms\Components\TextInput::make('username')
                                    ->label(__('Username'))
                                    ->maxLength(255)
                                    ->prefixIcon('heroicon-o-at-symbol'),
                                Forms\Components\TextInput::make('email')
                                    ->label(__('Alamat Email'))
                                    ->email()
                                    ->required()
                                    ->maxLength(255)
                                    ->prefixIcon('heroicon-o-envelope'),
                                Forms\Components\TextInput::make('password')
                                    ->label(__('Kata Sandi'))
                                    ->password()
                                    ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                                    ->dehydrated(fn ($state) => filled($state))
                                    ->required(fn (string $context): bool => $context === 'create')
                                    ->maxLength(255)
                                    ->revealable()
                                    ->prefixIcon('heroicon-o-key'),
                                Forms\Components\DateTimePicker::make('email_verified_at')
                                    ->label(__('Waktu Verifikasi Email'))
                                    ->native(false)
                                    ->prefixIcon('heroicon-o-check-badge'),
                            ]),

                        Forms\Components\Section::make(__('Koneksi Sosial'))
                            ->description(__('Informasi akun yang terhubung melalui pihak ketiga.'))
                            ->icon('heroicon-o-link')
                            ->schema([
                                Forms\Components\Grid::make(2)
                                    ->schema([
                                        Forms\Components\TextInput::make('social_type')
                                            ->label(__('Metode Login'))
                                            ->placeholder(__('Manual'))
                                            ->formatStateUsing(fn ($state) => $state === 'google' ? 'Google' : $state)
                                            ->disabled()
                                            ->dehydrated(false)
                                            ->prefixIcon('heroicon-o-globe-alt'),
                                        Forms\Components\TextInput::make('social_id')
                                            ->label(__('ID Akun Google'))
                                            ->placeholder(__('N/A'))
                                            ->disabled()
                                            ->dehydrated(false)
                                            ->prefixIcon('heroicon-o-identification'),
                                    ]),
                            ])
                            ->collapsible()
                            ->collapsed(fn (?User $record) => blank($record?->social_id)),

                        Forms\Components\Section::make(__('Otorisasi'))
                            ->icon('heroicon-o-identification')
                            ->schema([
                                Forms\Components\Select::make('roles')
                                    ->searchable()
                                    ->label(__('Peran Sistem (Role)'))
                                    ->relationship('roles', 'name')
                                    ->getOptionLabelFromRecordUsing(fn ($record) => str($record->name)->headline())
                                    ->multiple()
                                    ->preload(),
                                Forms\Components\Toggle::make('active_status')
                                    ->label(__('Status Akun Aktif'))
                                    ->required()
                                    ->disabled(fn (?User $record) => $record?->hasRole('super_admin') ?? false)
                                    ->helperText(__('Super admin tidak dapat dinonaktifkan demi alasan keamanan.'))
                                    ->onIcon('heroicon-s-check')
                                    ->offIcon('heroicon-s-x-mark'),
                            ]),
                    ])->columnSpan(['lg' => 1]),
            ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('avatar_url')
                    ->label(__('Avatar'))
                    ->circular()
                    ->alignment('center'),

                Tables\Columns\TextColumn::make('full_name')
                    ->searchable()
                    ->label(__('Nama Lengkap')),

                Tables\Columns\TextColumn::make('first_name')
                    ->label(__('Nama Depan')),

                Tables\Columns\TextColumn::make('mid_name')
                    ->label(__('Nama Tengah')),

                Tables\Columns\TextColumn::make('last_name')
                    ->label(__('Nama Belakang')),

                Tables\Columns\TextColumn::make('username')
                    ->searchable()
                    ->label(__('Username')),

                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->label(__('Email')),

                Tables\Columns\TextColumn::make('email_verified_at')
                    ->label(__('Diverifikasi Pada'))
                    ->dateTime()
                    ->alignment('center')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('social_type')
                    ->label(__('Metode Login'))
                    ->badge()
                    ->color(fn (string $state): string => $state === 'google' ? 'danger' : 'gray')
                    ->formatStateUsing(fn (string $state): string => $state === 'google' ? 'Google' : $state)
                    ->alignment('center')
                    ->placeholder(__('Manual')),

                Tables\Columns\TextColumn::make('roles.name')
                    ->searchable()
                    ->label(__('Peran'))
                    ->badge()
                    ->alignment('center')
                    ->formatStateUsing(fn ($state): string => __((string) str($state)->headline())),

                Tables\Columns\ToggleColumn::make('active_status')
                    ->label(__('Status Aktif'))
                    ->disabled(fn (?User $record) => $record?->hasRole('super_admin') ?? false)
                    ->alignment('center')
                    ->afterStateUpdated(function ($record, $state): void {
                        if (! $state) {
                            $record->tokens()->delete();
                        }
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('Terdaftar Pada'))
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->alignment('center'),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label(__('Terakhir Diperbarui'))
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->alignment('center'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->slideOver()
                    ->button()
                    ->color('info')
                    ->size('lg'),
                Tables\Actions\EditAction::make()
                    ->slideOver()
                    ->button()
                    ->color('warning')
                    ->size('lg')
                    ->successNotification(
                        Notification::make()
                            ->success()
                            ->title(__('Pengguna diperbarui'))
                            ->body(__('Pengguna telah berhasil diperbarui.'))
                    ),
                Tables\Actions\DeleteAction::make()
                    ->button()
                    ->color('danger')
                    ->size('lg')
                    ->successNotification(
                        Notification::make()
                            ->success()
                            ->title(__('Pengguna dihapus'))
                            ->body(__('Pengguna telah berhasil dihapus.'))
                    ),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageUsers::route('/'),
        ];
    }
}
