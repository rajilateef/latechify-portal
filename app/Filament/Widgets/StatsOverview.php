<?php

namespace App\Filament\Widgets;

use App\Models\CheckoutOrder;
use App\Models\ContactMessage;
use App\Models\Course;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected ?string $heading = 'Website & sales';

    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = 'full';

    protected function getStats(): array
    {
        $enrolments = CheckoutOrder::where('kind', 'enrolment');

        $started = (clone $enrolments)->count();
        $awaiting = (clone $enrolments)->where('status', 'paid')->count();
        $confirmed = (clone $enrolments)->where('status', 'confirmed')->count();

        // Money actually taken through checkout (paid, whether or not it's confirmed yet).
        $revenue = (int) CheckoutOrder::whereIn('status', ['paid', 'confirmed'])->sum('amount');

        return [
            Stat::make('Checkout orders', $started)
                ->description($awaiting ? $awaiting.' awaiting confirmation' : 'None awaiting confirmation')
                ->descriptionIcon('heroicon-m-shopping-cart')
                ->color($awaiting ? 'warning' : 'gray'),

            Stat::make('Portals opened', $confirmed)
                ->description('₦'.number_format($revenue).' taken at checkout')
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
