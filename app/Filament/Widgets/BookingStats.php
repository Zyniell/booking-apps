<?php

namespace App\Filament\Widgets;

use App\Models\Booking;
use App\Models\Item;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class BookingStats extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $totalItems = Item::query()->count();
        $currentMonth = Carbon::now();
        $monthlyBookings = Booking::query()
            ->whereBetween('booking_date', [
                $currentMonth->copy()->startOfMonth(),
                $currentMonth->copy()->endOfMonth(),
            ])
            ->count();
        $pendingBookings = Booking::query()
            ->where('status', 'pending')
            ->count();
        $completedBookings = Booking::query()
            ->whereIn('status', ['completed', 'paid'])
            ->count();

        return [
            Stat::make('Jumlah Item', $totalItems)
                ->icon('heroicon-m-cube')
                ->color('primary'),
            Stat::make('Booking Pending', $pendingBookings)
                ->icon('heroicon-m-clock')
                ->color('warning')
                ->description('Menunggu konfirmasi')
                ->descriptionIcon('heroicon-m-information-circle'),
            Stat::make('Booking Selesai', $completedBookings)
                ->icon('heroicon-m-check-badge')
                ->color('success'),
            Stat::make('Booking Bulan Ini', $monthlyBookings)
                ->icon('heroicon-m-clipboard-document-check')
                ->color('info'),
        ];
    }
}
