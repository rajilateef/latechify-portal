<?php

namespace App\Filament\Widgets;

use App\Models\AssignmentSubmission;
use App\Models\Certificate;
use App\Models\Enrollment;
use App\Models\FeePayment;
use App\Models\LiveSession;
use App\Models\TrainingProgram;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PortalStatsOverview extends BaseWidget
{
    protected ?string $heading = 'Training portal';

    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    protected function getStats(): array
    {
        $trainees      = User::where('is_student', true)->count();
        $activeStuds   = User::where('is_student', true)->where('is_active', true)->count();
        $awaiting      = $trainees - $activeStuds;
        $pending       = Enrollment::where('status', 'pending')->count();

        $collected     = (int) FeePayment::sum('amount');
        $thisMonth     = (int) FeePayment::where('paid_at', '>=', now()->startOfMonth())->sum('amount');
        $outstanding   = (int) Enrollment::active()->with('program')->get()->sum->outstanding();

        $toGrade       = AssignmentSubmission::where('status', 'submitted')->count();
        $upcoming      = LiveSession::query()->upcoming()->count();
        $certs         = Certificate::whereNotNull('user_id')->count();

        return [
            Stat::make('Trainees', $trainees)
                ->description($activeStuds.' active'.($awaiting ? ' · '.$awaiting.' to activate' : ''))
                ->descriptionIcon('heroicon-m-users')
                ->color($awaiting ? 'warning' : 'success'),

            Stat::make('Pending enrolments', $pending)
                ->description($pending ? 'Awaiting your approval' : 'All caught up')
                ->descriptionIcon('heroicon-m-clipboard-document-check')
                ->color($pending ? 'warning' : 'gray'),

            Stat::make('Active programs', TrainingProgram::where('is_active', true)->count())
                ->description(TrainingProgram::count().' total')
                ->descriptionIcon('heroicon-m-academic-cap')
                ->color('primary'),

            Stat::make('Fees collected', '₦'.number_format($collected))
                ->description('₦'.number_format($thisMonth).' this month')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success')
                ->chart($this->feeSparkline()),

            Stat::make('Outstanding fees', '₦'.number_format($outstanding))
                ->description($outstanding ? 'Across active enrolments' : 'Everyone is paid up')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($outstanding ? 'danger' : 'success'),

            Stat::make('Submissions to grade', $toGrade)
                ->description($toGrade ? 'Awaiting grading' : 'Nothing pending')
                ->descriptionIcon('heroicon-m-document-text')
                ->color($toGrade ? 'warning' : 'gray'),

            Stat::make('Upcoming sessions', $upcoming)
                ->description('Scheduled live classes')
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('info'),

            Stat::make('Certificates issued', $certs)
                ->description('Completed programs')
                ->descriptionIcon('heroicon-m-trophy')
                ->color('primary'),
        ];
    }

    /** Last 6 months of collected fees, for the stat sparkline. */
    protected function feeSparkline(): array
    {
        $data = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->startOfMonth()->subMonths($i);
            $data[] = (int) FeePayment::whereBetween('paid_at', [$month, (clone $month)->endOfMonth()])->sum('amount');
        }

        return $data;
    }
}
