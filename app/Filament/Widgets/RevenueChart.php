<?php

namespace App\Filament\Widgets;

use App\Models\FeePayment;
use Filament\Widgets\ChartWidget;

class RevenueChart extends ChartWidget
{
    protected static ?string $heading = 'Training fees collected';

    protected static ?string $description = 'Payments recorded over the last 6 months';

    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 1;

    protected static ?string $maxHeight = '260px';

    protected function getData(): array
    {
        $labels = [];
        $values = [];

        for ($i = 5; $i >= 0; $i--) {
            $month = now()->startOfMonth()->subMonths($i);
            $labels[] = $month->format('M');
            $values[] = (int) FeePayment::whereBetween('paid_at', [$month, (clone $month)->endOfMonth()])->sum('amount');
        }

        return [
            'datasets' => [[
                'label'           => 'Fees (₦)',
                'data'            => $values,
                'borderColor'     => '#031273',
                'backgroundColor' => 'rgba(3, 18, 115, 0.12)',
                'fill'            => true,
                'tension'         => 0.35,
            ]],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
