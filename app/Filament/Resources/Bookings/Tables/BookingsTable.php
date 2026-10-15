<?php

namespace App\Filament\Resources\Bookings\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class BookingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('item.name')
                    ->label('Item')
                    ->sortable()
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('customer_name')
                    ->label('Nama Pemesan')
                    ->sortable()
                    ->searchable(isIndividual: true),
                TextColumn::make('customer_phone')
                    ->label('Nomor WhatsApp')
                    ->description(
                        fn ($record) => $record->customer_email ? "Email: {$record->customer_email}" : null,
                        position: 'below',
                    )
                    ->copyable()
                    ->copyMessage('Nomor WhatsApp disalin')
                    ->icon('heroicon-m-phone')
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (?string $state) => match ($state) {
                        'pending' => 'warning',
                        'confirmed', 'paid' => 'success',
                        'cancelled' => 'danger',
                        'expired' => 'gray',
                        default => 'info',
                    })
                    ->sortable(),
                TextColumn::make('booking_date')
                    ->label('Tanggal Booking')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('total_amount')
                    ->label('Total Bayar')
                    ->money('idr', true)
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Pending',
                        'confirmed' => 'Confirmed',
                        'paid' => 'Paid',
                        'cancelled' => 'Cancelled',
                        'expired' => 'Expired',
                    ]),
                SelectFilter::make('item')
                    ->label('Item')
                    ->relationship('item', 'name')
                    ->searchable(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
