<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\TermsOfServiceResource\Pages;
use App\Models\TermsOfService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class TermsOfServiceResource extends Resource
{
    protected static ?string $model = TermsOfService::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'Manajemen Legal';
    protected static ?string $label = 'Terms of Service';
    protected static ?string $pluralLabel = 'Terms of Service';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Dokumen')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->label('Judul Dokumen')
                            ->required()
                            ->default('Syarat & Ketentuan'),
                    ]),

                Forms\Components\Section::make('Konten Legal (1, 2, 3...)')
                    ->description('Kelola setiap pasal atau bagian dokumen Anda di sini. Urutan dapat digeser.')
                    ->schema([
                        Forms\Components\Repeater::make('content')
                            ->label('Pasal / Bagian')
                            ->schema([
                                Forms\Components\TextInput::make('heading')
                                    ->label('Heading / Kepala Pasal')
                                    ->required()
                                    ->placeholder('Misal: PENDAHULUAN'),
                                Forms\Components\Textarea::make('body')
                                    ->label('Isi Pasal')
                                    ->required()
                                    ->rows(4)
                                    ->placeholder('Tuliskan rincian hukum di sini...'),
                                Forms\Components\Toggle::make('is_italic')
                                    ->label('Gunakan Tulisan Miring (Italic)')
                                    ->default(false),
                            ])
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => $state['heading'] ?? 'Section Baru')
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
                            ->title(__('Ketentuan Layanan diperbarui'))
                            ->body(__('Ketentuan Layanan telah berhasil diperbarui.'))
                    ),
                Tables\Actions\DeleteAction::make()
                    ->button()
                    ->color('danger')
                    ->size('lg')
                    ->successNotification(
                        Notification::make()
                            ->success()
                            ->title(__('Ketentuan Layanan dihapus'))
                            ->body(__('Ketentuan Layanan telah berhasil dihapus.'))
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
            'index' => Pages\ManageTermsOfServices::route('/'),
        ];
    }
}
