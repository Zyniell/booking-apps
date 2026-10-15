<?php

namespace App\Filament\Widgets;

use App\Models\Booking;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Widgets\TableWidget as BaseTableWidget;
use Illuminate\Database\Eloquent\Builder;

class PendingBookingsTable extends BaseTableWidget
{
    protected int | string | array $columnSpan = 'full';

    protected function getTableQuery(): Builder
    {
        return Booking::query()
            ->with('item')
            ->where('status', 'pending')
            ->latest('booking_date');
    }

    protected function getTableColumns(): array
    {
        return [
            TextColumn::make('booking_code')
                ->label('Kode Booking')
                ->badge()
                ->copyable()
                ->copyMessage('Kode booking disalin')
                ->icon('heroicon-m-qr-code'),
            TextColumn::make('customer_name')
                ->label('Nama Pemesan')
                ->searchable(),
            TextColumn::make('customer_phone')
                ->label('WhatsApp')
                ->icon('heroicon-m-phone-arrow-up-right')
                ->toggleable()
                ->copyable(),
            TextColumn::make('item.name')
                ->label('Item')
                ->placeholder('-')
                ->wrap(),
            TextColumn::make('booking_date')
                ->label('Tanggal Booking')
                ->date()
                ->sortable(),
        ];
    }

    protected function getTableActions(): array
    {
        return [
            ViewAction::make(),
            Action::make('mark-confirmed')
                ->label('Konfirmasi')
                ->icon('heroicon-m-check')
                ->color('success')
                ->requiresConfirmation()
                ->action(fn(Booking $record) => $record->update(['status' => 'confirmed'])),
        ];
    }
}
