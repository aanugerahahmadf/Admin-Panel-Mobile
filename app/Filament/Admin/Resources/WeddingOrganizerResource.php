<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\WeddingOrganizerResource\Pages;
use App\Models\WeddingOrganizer;
use Dotswan\MapPicker\Fields\Map;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\HtmlString;

/**
 * @mixin \Eloquent
 *
 * @property-read WeddingOrganizer $record
 */
class WeddingOrganizerResource extends Resource
{
    protected static ?string $model = WeddingOrganizer::class;

    protected static ?string $slug = 'Profile';

    protected static ?string $navigationIcon = 'heroicon-o-sparkles';

    protected static ?int $navigationSort = 1;

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getModel()::count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'primary';
    }

    protected static ?string $recordTitleAttribute = 'name';

    public static function getModelLabel(): string
    {
        return __('Profil Studio');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Profil Studio');
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['name'];
    }

    public static function getNavigationGroup(): ?string
    {
        return __('Data Master');
    }

    public static function getNavigationLabel(): string
    {
        return __('Profil Dekorasi Bunga Pernikahan');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make(__('Visual & Galeri'))
                            ->description(__('Koleksi foto karya dan presentasi video studio.'))
                            ->icon('heroicon-o-photo')
                            ->schema([
                                Forms\Components\SpatieMediaLibraryFileUpload::make('gallery')
                                    ->label(__('Galeri Foto Portfolio'))
                                    ->collection('gallery')
                                    ->multiple()
                                    ->reorderable()
                                    ->image()
                                    ->imageEditor()
                                    ->columnSpanFull(),
                                Forms\Components\SpatieMediaLibraryFileUpload::make('videos')
                                    ->label(__('Video Profil Studio'))
                                    ->collection('videos')
                                    ->multiple()
                                    ->acceptedFileTypes(['video/mp4', 'video/webm', 'video/ogg', 'video/quicktime'])
                                    ->maxSize(102400000) // 100GB
                                    ->maxFiles(3)
                                    ->helperText(__('Upload video profil/showreel studio. Format: MP4, WebM, MOV. Maks 100MB per file.'))
                                    ->columnSpanFull(),
                            ]),

                        Forms\Components\Section::make(__('Profil & Identitas Studio'))
                            ->description(__('Nama, deskripsi, dan identitas visual utama.'))
                            ->icon('govicon-building')
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label(__('Nama Studio'))
                                    ->required()
                                    ->maxLength(255)
                                    ->dehydrated()
                                    ->prefixIcon('heroicon-o-sparkles'),
                                Forms\Components\RichEditor::make('description')
                                    ->label(__('Deskripsi Lengkap Studio'))
                                    ->columnSpanFull()
                                    ->toolbarButtons(['bold', 'italic', 'underline', 'bulletList', 'orderedList']),
                            ])->columns(2),

                        Forms\Components\Section::make(__('Lokasi Geografis'))
                            ->icon('heroicon-o-map-pin')
                            ->description(__('Ketik alamat lalu klik di luar kotak untuk menyinkronkan titik peta secara otomatis.'))
                            ->schema([
                                Forms\Components\Textarea::make('address')
                                    ->label(__('Alamat Lengkap'))
                                    ->helperText(__('Setelah mengisi alamat, titik peta akan otomatis berpindah ke lokasi tersebut.'))
                                    ->maxLength(255)
                                    ->rows(3)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function (Forms\Get $get, Forms\Set $set, ?string $state) {
                                        if (! $state) {
                                            return;
                                        }

                                        try {
                                            $response = Http::withHeaders([
                                                'User-Agent' => 'WeddingOrganizerApp/1.0',
                                            ])->get('https://nominatim.openstreetmap.org/search', [
                                                'q' => $state,
                                                'format' => 'json',
                                                'limit' => 1,
                                            ]);

                                            if ($response->successful() && $json = $response->json()) {
                                                if (isset($json[0])) {
                                                    $data = $json[0];
                                                    $lat = (float) $data['lat'];
                                                    $lng = (float) $data['lon'];

                                                    $set('location', ['lat' => $lat, 'lng' => $lng]);
                                                    $set('latitude', $lat);
                                                    $set('longitude', $lng);
                                                }
                                            }
                                        } catch (\Exception $e) {
                                            // Fail silently
                                        }
                                    }),

                                Map::make('location')
                                    ->label(__('Titik Koordinat Peta'))
                                    ->helperText(__('Tips: Anda bisa geser titik biru ini untuk mengisi Alamat Lengkap secara otomatis.'))
                                    ->columnSpanFull()
                                    ->extraStyles([
                                        'min-height: 450px',
                                        'z-index: 1',
                                    ])
                                    ->showMyLocationButton(false)
                                    ->live()
                                    ->afterStateHydrated(function (Forms\Set $set, $record) {
                                        if ($record) {
                                            $set('location', [
                                                'lat' => (float) ($record->latitude),
                                                'lng' => (float) ($record->longitude),
                                            ]);
                                        }
                                    })
                                    ->afterStateUpdated(function (Forms\Get $get, Forms\Set $set, ?array $state) {
                                        if (! $state) {
                                            return;
                                        }

                                        $set('latitude', $state['lat']);
                                        $set('longitude', $state['lng']);

                                        try {
                                            $response = Http::withHeaders([
                                                'User-Agent' => 'WeddingOrganizerApp/1.0',
                                            ])->get('https://nominatim.openstreetmap.org/reverse', [
                                                'lat' => $state['lat'],
                                                'lon' => $state['lng'],
                                                'format' => 'json',
                                            ]);

                                            if ($response->successful()) {
                                                $address = $response->json()['display_name'] ?? null;
                                                if ($address) {
                                                    $set('address', $address);
                                                }
                                            }
                                        } catch (\Exception $e) {
                                            // Fail silently
                                        }
                                    }),

                                Forms\Components\Hidden::make('latitude'),
                                Forms\Components\Hidden::make('longitude'),
                            ]),
                    ])->columnSpan(['lg' => 2]),

                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make(__('Status'))
                            ->icon('heroicon-o-star')
                            ->schema([
                                Forms\Components\Toggle::make('is_verified')
                                    ->label(__('Akun Terverifikasi'))
                                    ->required()
                                    ->onColor('success')
                                    ->onIcon('heroicon-s-check-badge')
                                    ->offIcon('heroicon-o-x-mark'),
                            ]),

                        Forms\Components\Section::make(__('Informasi Kontak'))
                            ->icon('heroicon-o-phone')
                            ->description(__('Data kontak yang ditampilkan ke pengguna di tab Informasi Kontak.'))
                            ->schema([
                                Forms\Components\TextInput::make('phone')
                                    ->label(__('Nomor Telepon'))
                                    ->tel()
                                    ->prefixIcon('heroicon-o-phone')
                                    ->placeholder('+62 812 3456 7890'),

                                Forms\Components\TextInput::make('whatsapp')
                                    ->label(__('Nomor WhatsApp'))
                                    ->tel()
                                    ->prefixIcon('heroicon-o-chat-bubble-left-right')
                                    ->placeholder('+62 812 3456 7890')
                                    ->helperText(__('Kosongkan jika sama dengan nomor telepon.')),

                                Forms\Components\TextInput::make('email')
                                    ->label(__('Email'))
                                    ->email()
                                    ->prefixIcon('heroicon-o-envelope')
                                    ->placeholder('studio@example.com'),

                                Forms\Components\TextInput::make('instagram')
                                    ->label(__('Username Instagram'))
                                    ->prefixIcon('ri-instagram-line')
                                    ->placeholder('username_instagram')
                                    ->helperText(__('Masukkan username saja (tanpa @).')),

                                Forms\Components\TextInput::make('operational_hours')
                                    ->label(__('Jam Operasional'))
                                    ->prefixIcon('heroicon-o-clock')
                                    ->placeholder('Senin - Minggu: 09:00 - 18:00')
                                    ->default('Senin - Minggu: 09:00 - 18:00'),
                            ]),
                    ])->columnSpan(['lg' => 1]),
            ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                SpatieMediaLibraryImageColumn::make('gallery')
                    ->label(__('Galeri'))
                    ->collection('gallery')
                    ->defaultImageUrl(asset('images/placeholders/image-placeholder.png'))
                    ->height(60)
                    ->width(60)
                    ->circular()
                    ->extraImgAttributes(['class' => 'rounded-lg object-cover'])
                    ->alignment('center'),
                Tables\Columns\TextColumn::make('video_url')
                    ->label(__('Video'))
                    ->formatStateUsing(fn ($state) => $state
                        ? new HtmlString(
                            '<video src="'.e($state).'" class="rounded-lg" height="60" width="80" controls preload="none"></video>'
                        )
                        : new HtmlString('<span class="text-gray-400 text-xs">—</span>')
                    )
                    ->html()
                    ->alignment('center'),
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->label(__('Nama Studio'))
                    ->alignment('center')
                    ->sortable()
                    ->icon('heroicon-o-sparkles'),
                Tables\Columns\TextColumn::make('description')
                    ->label(__('Deskripsi'))
                    ->limit(50)
                    ->html()
                    ->alignment('center')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('address')
                    ->searchable()
                    ->label(__('Alamat'))
                    ->limit(40)
                    ->alignment('center')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->icon('heroicon-o-map-pin'),
                Tables\Columns\TextColumn::make('latitude')
                    ->label(__('Latitude'))
                    ->alignment('center')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->copyable()
                    ->copyableState(fn ($state) => $state),
                Tables\Columns\TextColumn::make('longitude')
                    ->label(__('Longitude'))
                    ->alignment('center')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->copyable()
                    ->copyableState(fn ($state) => $state),
                Tables\Columns\IconColumn::make('is_verified')
                    ->label(__('Terverifikasi'))
                    ->boolean()
                    ->alignment('center')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('Terdaftar Pada'))
                    ->dateTime()
                    ->alignment('center')
                    ->sortable()
                    ->icon('heroicon-o-calendar')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label(__('Diperbarui Pada'))
                    ->dateTime()
                    ->alignment('center')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->slideOver()
                    ->modalWidth('5xl')
                    ->button()
                    ->color('info')
                    ->size('lg'),
                Tables\Actions\EditAction::make()
                    ->label(__('Atur Profil'))
                    ->url(fn (WeddingOrganizer $record): string => static::getUrl('edit', ['record' => $record]))
                    ->button()
                    ->color('warning')
                    ->size('lg')
                    ->successNotification(
                        Notification::make()
                            ->success()
                            ->title(__('Profil Dekorasi Bunga Pernikahan diperbarui'))
                            ->body(__('Profil dekorasi bunga pernikahan telah berhasil diperbarui.'))
                    ),
                Tables\Actions\DeleteAction::make()
                    ->button()
                    ->color('danger')
                    ->size('lg')
                    ->successNotification(
                        Notification::make()
                            ->success()
                            ->title(__('Profil Dekorasi Bunga Pernikahan dihapus'))
                            ->body(__('Profil dekorasi bunga pernikahan telah berhasil dihapus.'))
                    ),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function canCreate(): bool
    {
        return true;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageWeddingOrganizers::route('/'),
            'edit' => Pages\EditWeddingOrganizer::route('/{record}/edit'),
        ];
    }
}
