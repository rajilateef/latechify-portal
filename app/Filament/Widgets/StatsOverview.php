<?php

namespace App\Filament\Widgets;

use App\Models\Application;
use App\Models\ContactMessage;
use App\Models\Course;
use App\Models\Payment;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $revenue = Payment::whereIn('status', ['success', 'verified'])->sum('amount');

        return [
            Stat::make('Applications', Application::count())
                ->description(Application::where('status', 'pending')->count().' pending')
                ->descriptionIcon('heroicon-m-inbox-arrow-down')
                ->color('warning'),

            Stat::make('Paid enrollments', Application::where('status', 'paid')->count())
                ->description('₦'.number_format($revenue).' collected')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),

            Stat::make('New messages', ContactMessage::where('status', 'new')->count())
                ->description('Contact form submissions')
                ->descriptionIcon('heroicon-m-envelope')
                ->color('info'),

            Stat::make('Active courses', Course::where('is_active', true)->count())
                ->description(Course::count().' total')
                ->descriptionIcon('heroicon-m-academic-cap')
                ->color('primary'),
        ];
    }
}
