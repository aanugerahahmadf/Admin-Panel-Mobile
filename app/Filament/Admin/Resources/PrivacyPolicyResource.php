<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\PrivacyPolicyResource\Pages;
use App\Models\PrivacyPolicy;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PrivacyPolicyResource extends Resource
{
    protected static ?string $model = PrivacyPolicy::class;

    protected static ?string $navigationIcon = 'heroicon-o-shield-check';
    protected static ?string $navigationGroup = 'Manajemen Legal';
    protected static ?string $label = 'Privacy Policy';
    protected static ?string $pluralLabel = 'Privacy Policy';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Kebijakan')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->label('Judul Kebijakan')
                            ->required()
                            ->default('Kebijakan Privasi'),
                    ]),

                Forms\Components\Section::make('Data & Keamanan')
                    ->description('Organisasi rincian data dan perlindungan privasi yang dikelola sistem Kami secara in-house.')
                    ->schema([
                        Forms\Components\Repeater::make('content')
                            ->label('Pasal / Bagian Data')
                            ->schema([
                                Forms\Components\TextInput::make('heading')
                                    ->label('Heading / Nama Pasal')
                                    ->required()
                                    ->placeholder('Misal: KOMITMEN PRIVASI'),
                                Forms\Components\Textarea::make('body')
                                    ->label('Isi Pasal')
                                    ->required()
                                    ->rows(4)
                                    ->placeholder('Tuliskan rincian kebijakan di sini...'),
                                Forms\Components\Toggle::make('is_italic')
                                    ->label('Gunakan Tulisan Miring (Italic)')
                                    ->default(false),
                            ])
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => $state['heading'] ?? 'Rincian Baru')
                            ->grid(1)
                            ->reorderableWithButtons()
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Judul')
                    ->weight('bold')
                    ->searchable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Terakhir Diupdate')
                    ->dateTime('d M Y H:i')
                    ->color('gray')
                    ->sortable(),
            ])
            ->filters([])
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
                            ->title(__('Kebijakan Privasi diperbarui'))
                            ->body(__('Kebijakan Privasi telah berhasil diperbarui.'))
                    ),
                Tables\Actions\DeleteAction::make()
                    ->button()
                    ->color('danger')
                    ->size('lg')
                    ->successNotification(
                        Notification::make()
                            ->success()
                            ->title(__('Kebijakan Privasi dihapus'))
                            ->body(__('Kebijakan Privasi telah berhasil dihapus.'))
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
            'index' => Pages\ManagePrivacyPolicies::route('/'),
        ];
    }
}
