<?php

namespace BlackpigCreatif\Magistere\Filament\Widgets;

use BlackpigCreatif\Magistere\Enums\BookingStatus;
use BlackpigCreatif\Magistere\Enums\PaymentStatus;
use BlackpigCreatif\Magistere\Models\Booking;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class BookingStatsWidget extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $pendingCount = Booking::query()->where('status', BookingStatus::Pending)->count();
        $confirmedCount = Booking::query()->where('status', BookingStatus::Confirmed)->count();

        $outstandingRevenue = Booking::query()
            ->whereNotIn('status', [BookingStatus::Cancelled, BookingStatus::NoShow])
            ->where('payment_status', '!=', PaymentStatus::Paid->value)
            ->where('payment_status', '!=', PaymentStatus::Overpaid->value)
            ->sum('subtotal');

        $totalRevenue = Booking::query()
            ->whereNotIn('status', [BookingStatus::Cancelled, BookingStatus::NoShow])
            ->sum('amount_paid');

        return [
            Stat::make('Pending Bookings', $pendingCount)
                ->description('Awaiting confirmation')
                ->color($pendingCount > 0 ? 'warning' : 'success')
                ->icon('heroicon-o-clock'),

            Stat::make('Confirmed Bookings', $confirmedCount)
                ->description('Active bookings')
                ->color('success')
                ->icon('heroicon-o-check-circle'),

            Stat::make('Revenue Collected', '€' . number_format((float) $totalRevenue, 2))
                ->description('€' . number_format((float) $outstandingRevenue, 2) . ' outstanding')
                ->color($outstandingRevenue > 0 ? 'warning' : 'success')
                ->icon('heroicon-o-banknotes'),
        ];
    }
}
