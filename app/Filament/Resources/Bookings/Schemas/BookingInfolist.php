<?php

namespace App\Filament\Resources\Bookings\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class BookingInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Ringkasan Booking')
                    ->schema([
                        TextEntry::make('booking_code')
                            ->label('Kode Booking')
                            ->badge()
                            ->copyable()
                            ->copyMessage('Kode booking disalin')
                            ->icon('heroicon-m-qr-code')
                            ->color('primary'),
                        TextEntry::make('item.name')
                            ->label('Item')
                            ->placeholder('Belum ada item')
                            ->icon('heroicon-m-cube'),
                        TextEntry::make('status')
                            ->label('Status Booking')
                            ->badge()
                            ->color(fn (?string $state): string => match ($state) {
                                'pending' => 'warning',
                                'confirmed' => 'info',
                                'paid', 'completed' => 'success',
                                'cancelled' => 'danger',
                                'expired' => 'gray',
                                default => 'primary',
                            }),
                        TextEntry::make('payment_status')
                            ->label('Status Pembayaran')
                            ->badge()
                            ->color(fn (?string $state): string => match ($state) {
                                'paid' => 'success',
                                'refunded' => 'gray',
                                'failed' => 'danger',
                                'expired' => 'warning',
                                default => 'secondary',
                            }),
                        TextEntry::make('booking_date')
                            ->label('Tanggal Booking')
                            ->date()
                            ->placeholder('-'),
                    ])
                    ->columns(2),

                Section::make('Data Pemesan')
                    ->schema([
                        TextEntry::make('customer_name')
                            ->label('Nama Pemesan')
                            ->icon('heroicon-m-user'),
                        TextEntry::make('customer_phone')
                            ->label('Nomor WhatsApp')
                            ->copyable()
                            ->copyMessage('Nomor WhatsApp disalin')
                            ->icon('heroicon-m-phone-arrow-up-right')
                            ->url(
                                fn (?string $state): ?string => $state
                                    ? 'https://wa.me/' . preg_replace('/\D+/', '', $state)
                                    : null,
                                true,
                            ),
                        TextEntry::make('customer_email')
                            ->label('Email')
                            ->icon('heroicon-m-envelope')
                            ->placeholder('-'),
                        TextEntry::make('customer_note')
                            ->label('Catatan Pemesan')
                            ->columnSpanFull()
                            ->placeholder('Tidak ada catatan')
                            ->prose(),
                    ])
                    ->columns(2),

                Section::make('Jadwal & Durasi')
                    ->schema([
                        TextEntry::make('starts_at')
                            ->label('Mulai')
                            ->dateTime()
                            ->placeholder('-'),
                        TextEntry::make('ends_at')
                            ->label('Selesai')
                            ->dateTime()
                            ->placeholder('-'),
                        TextEntry::make('slot_duration')
                            ->label('Durasi Per Slot')
                            ->formatStateUsing(fn ($state): ?string => $state ? "{$state} menit" : null),
                        TextEntry::make('slot_count')
                            ->label('Jumlah Slot')
                            ->numeric(),
                        TextEntry::make('duration_in_minutes')
                            ->label('Total Durasi')
                            ->formatStateUsing(
                                fn ($state): ?string => $state
                                    ? ($state >= 60
                                        ? sprintf('%d menit (%.1f jam)', $state, $state / 60)
                                        : "{$state} menit")
                                    : null,
                            ),
                    ])
                    ->columns(3),

                Section::make('Pembayaran')
                    ->schema([
                        TextEntry::make('base_price')
                            ->label('Harga Dasar')
                            ->money('idr'),
                        TextEntry::make('total_amount')
                            ->label('Total Pembayaran')
                            ->money('idr'),
                        TextEntry::make('payment_reference')
                            ->label('Referensi Pembayaran')
                            ->copyable()
                            ->copyMessage('Referensi disalin')
                            ->placeholder('Belum ada referensi')
                            ->icon('heroicon-m-hashtag'),
                        TextEntry::make('meta.payment_channel')
                            ->label('Metode Pembayaran')
                            ->placeholder('Tidak ditentukan'),
                    ])
                    ->columns(2),
            ]);
    }
}
