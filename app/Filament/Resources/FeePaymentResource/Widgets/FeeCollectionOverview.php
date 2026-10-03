<?php

namespace App\Filament\Resources\FeePaymentResource\Widgets;

use App\Models\CheckoutOrder;
use App\Models\Enrollment;
use App\Models\FeePayment;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/** Header stats for the fee-payment ledger: what's in, what's owed, who's behind. */
class FeeCollectionOverview extends BaseWidget
{
    protected static ?string $pollingInterval = null;

    protected function getStats(): array
    {
        $enrollments = Enrollment::active()->with('program')->get();

        $billed = (int) $enrollments->sum->feeAmount();
        $collected = (int) FeePayment::sum('amount');
        $outstanding = (int) $enrollments->sum->outstanding();
        $behind = $enrollments->filter(fn (Enrollment $e) => $e->outstanding() > 0)->count();

        $thisMonth = (int) FeePayment::where('paid_at', '>=', now()->startOfMonth())->sum('amount');
        $lastMonth = (int) FeePayment::whereBetween('paid_at', [
            now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth(),
        ])->sum('amount');

        $online = (int) FeePayment::whereIn('method', ['monnify', 'paystack', 'card'])->sum('amount');
        $awaitingConfirmation = CheckoutOrder::awaitingConfirmation()->where('kind', 'enrolment')->count();

        $rate = $billed > 0 ? (int) round(min($collected, $billed) / $billed * 100) : 0;

        return [
            Stat::make('Collected', '₦'.number_format($collected))
                ->description('₦'.number_format($thisMonth).' this month'
                    .($lastMonth ? ' · ₦'.number_format($lastMonth).' last month' : ''))
                ->descriptionIcon($thisMonth >= $lastMonth ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color('success')
                ->chart($this->sparkline()),

            Stat::make('Outstanding', '₦'.number_format($outstanding))
                ->description($behind ? $behind.' trainee(s) with a balance' : 'Everyone is paid up')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($outstanding ? 'danger' : 'success'),

            Stat::make('Collection rate', $rate.'%')
                ->description('₦'.number_format($collected).' of ₦'.number_format($billed).' billed')
                ->descriptionIcon('heroicon-m-chart-pie')
                ->color($rate >= 80 ? 'success' : ($rate >= 50 ? 'warning' : 'danger')),

            Stat::make('Paid online', '₦'.number_format($online))
                ->description($awaitingConfirmation
                    ? $awaitingConfirmation.' checkout order(s) awaiting confirmation'
                    : 'Via Monnify / card checkout')
                ->descriptionIcon('heroicon-m-credit-card')
                ->color($awaitingConfirmation ? 'warning' : 'info'),
        ];
    }

    /** Last 6 months of collected fees. */
    protected function sparkline(): array
    {
        $data = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->startOfMonth()->subMonths($i);
            $data[] = (int) FeePayment::whereBetween('paid_at', [$month, (clone $month)->endOfMonth()])->sum('amount');
        }

        return $data;
    }
}
