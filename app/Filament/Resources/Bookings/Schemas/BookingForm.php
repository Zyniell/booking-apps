<?php

namespace App\Filament\Resources\Bookings\Schemas;

use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class BookingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Perbarui Status Booking')
                    ->schema([
                        Select::make('status')
                            ->label('Status Booking')
                            ->options([
                                'pending' => 'Pending',
                                'confirmed' => 'Confirmed',
                                'paid' => 'Paid',
                                'completed' => 'Completed',
                                'cancelled' => 'Cancelled',
                                'expired' => 'Expired',
                            ])
                            ->required()
                            ->native(false)
                            ->helperText('Hanya status booking yang bisa diubah dari halaman ini.'),
                        Select::make('payment_status')
                            ->label('Status Pembayaran')
                            ->options([
                                'pending' => 'Pending',
                                'paid' => 'Paid',
                                'refunded' => 'Refunded',
                                'failed' => 'Failed',
                                'expired' => 'Expired',
                            ])
                            ->required()
                            ->native(false)
                            ->helperText('Gunakan untuk menandai progres pembayaran customer.'),
                    ])
                    ->columns(2),
            ]);
    }
}
