<?php

namespace App\Filament\Pages;

use App\Models\Business;
use BackedEnum;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use UnitEnum;

class Pengaturan extends Page
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-cog';
    protected static string|UnitEnum|null $navigationGroup = 'Pengaturan';
    protected static ?string $navigationLabel = 'Pengaturan';

    protected static ?string $title = 'Pengaturan';

    protected string $view = 'filament.pages.pengaturan';

    public ?array $data = [];

    public function mount(): void
    {
        $business = Business::first();

        if ($business) {
            $this->form->fill($business->toArray());
        }
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Informasi Umum')
                    ->schema([
                        TextInput::make('nama_usaha')
                            ->label('Nama Usaha')
                            ->columnSpanFull()
                            ->required(),

                        Textarea::make('deskripsi_singkat')
                            ->label('Deskripsi Singkat')
                            ->columnSpanFull()
                            ->rows(2),

                        TextInput::make('alamat')
                            ->label('Alamat')
                            ->columnSpanFull(),

                        TextInput::make('telepon')
                            ->label('No. Telepon'),

                        TextInput::make('email')
                            ->label('Email')
                            ->email(),

                        FileUpload::make('logo')
                            ->label('Logo Usaha')
                            ->image()
                            ->directory('logo')
                            ->imageEditor()
                            ->columnSpanFull()
                            ->maxSize(2048),
                    ])
                    ->columns(2)
                    ->columnSpan(2),

                Section::make('Pengaturan Booking')
                    ->schema([
                        Select::make('jenis_booking')
                            ->label('Jenis Booking')
                            ->options([
                                'per_jam' => 'Per Jam',
                                'per_12_jam' => 'Per 12 Jam',
                                'per_hari' => 'Per Hari',
                                'custom' => 'Custom (menit)',
                            ])
                            ->live()
                            ->required(),

                        TextInput::make('durasi_booking')
                            ->label('Durasi Booking (menit)')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(1440)
                            ->visible(fn($get) => $get('jenis_booking') === 'custom'),

                        TimePicker::make('jam_buka')
                            ->label('Jam Buka')
                            ->seconds(false)
                            ->required(),

                        TimePicker::make('jam_tutup')
                            ->label('Jam Tutup')
                            ->seconds(false)
                            ->required(),

                        Toggle::make('is_booking_open')
                            ->label('Terima Booking?')
                            ->inline(false),
                    ])
                    ->columns(2),

                Section::make('Catatan & Info Tambahan')
                    ->schema([
                        Textarea::make('catatan_booking')
                            ->label('Catatan untuk User')
                            ->placeholder('Contoh: Harap datang 15 menit sebelum waktu mulai.')
                            ->columnSpanFull(),
                    ]),
            ])->columns(2)
            ->statePath('data');
    }

    public function save()
    {
        $data = $this->form->getState();

        // Simpan data ke database
        $business = Business::first();
        if (!$business) {
            $business = new Business();
        }
        $business->fill($data);
        $business->save();

        Notification::make()
            ->title('Pengaturan berhasil disimpan!')
            ->success()
            ->send();
    }
}
