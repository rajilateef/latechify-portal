<?php

namespace App\Filament\Widgets;

use App\Models\Enrollment;
use Filament\Widgets\ChartWidget;

class EnrollmentTrendChart extends ChartWidget
{
    protected static ?string $heading = 'New enrolments';

    protected static ?string $description = 'Enrolment requests over the last 6 months';

    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = 1;

    protected static ?string $maxHeight = '260px';

    protected function getData(): array
    {
        $labels = [];
        $values = [];

        for ($i = 5; $i >= 0; $i--) {
            $month = now()->startOfMonth()->subMonths($i);
            $labels[] = $month->format('M');
            $values[] = Enrollment::whereBetween('created_at', [$month, (clone $month)->endOfMonth()])->count();
        }

        return [
            'datasets' => [[
                'label'           => 'Enrolments',
                'data'            => $values,
                'backgroundColor' => '#2563eb',
                'borderRadius'    => 6,
            ]],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
