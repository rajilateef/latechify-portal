<?php

namespace App\Filament\Pages;

use App\Models\ClassCompletion;
use App\Models\TrainingClass;
use App\Models\TrainingProgram;
use App\Models\User;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

/**
 * One screen to monitor a cohort: where every trainee is in the syllabus, how they
 * are attending, what they still owe, and which classes are lagging behind.
 */
class ClassMonitor extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-presentation-chart-line';

    protected static ?string $navigationGroup = 'Training Portal';

    protected static ?string $navigationLabel = 'Class monitor';

    protected static ?string $title = 'Class monitor';

    protected static ?int $navigationSort = 5;

    protected static string $view = 'filament.pages.class-monitor';

    /** Selected program (null = every program the trainee is on). */
    public ?int $programId = null;

    public function mount(): void
    {
        $this->programId ??= TrainingProgram::active()->value('id');
    }

    /** Program picker rendered above the table. */
    public function programOptions(): array
    {
        return TrainingProgram::orderBy('name')->pluck('name', 'id')->toArray();
    }

    public function getProgram(): ?TrainingProgram
    {
        return $this->programId ? TrainingProgram::find($this->programId) : null;
    }

    /**
     * Programs whose trainees should appear: the selected one plus any composite
     * (bundle) program that includes it.
     */
    protected function scopedProgramIds(): array
    {
        $program = $this->getProgram();

        if (! $program) {
            return TrainingProgram::pluck('id')->all();
        }

        return array_values(array_unique([
            $program->id,
            ...$program->partOf()->pluck('training_programs.id')->all(),
        ]));
    }

    /* ── Cohort summary cards ── */

    public function summary(): array
    {
        $program = $this->getProgram();
        $students = $this->students()->get();
        $totalClasses = $program?->effectiveClassesCount() ?? 0;

        $progress = $students->map(fn (User $u) => $this->progressFor($u));
        $attendance = $students->map(fn (User $u) => $u->attendanceRate())->filter(fn ($r) => $r !== null);

        return [
            'trainees'     => $students->count(),
            'classes'      => $totalClasses,
            'avgProgress'  => $progress->isNotEmpty() ? (int) round($progress->avg()) : 0,
            'avgAttendance'=> $attendance->isNotEmpty() ? (int) round($attendance->avg()) : null,
            'completed'    => $students->filter(fn (User $u) => $totalClasses > 0 && $this->completedCount($u) >= $totalClasses)->count(),
            'atRisk'       => $students->filter(fn (User $u) => $this->progressFor($u) < 25)->count(),
            'owing'        => $students->filter(fn (User $u) => $u->totalOutstanding() > 0)->count(),
        ];
    }

    /**
     * Class-by-class completion across the cohort — surfaces the lessons the group
     * is stuck on. Returns [class, completed, total, pct] ordered by the syllabus.
     */
    public function classBreakdown(): array
    {
        $program = $this->getProgram();

        if (! $program) {
            return [];
        }

        $cohortSize = $this->students()->count();

        $classes = TrainingClass::query()
            ->whereHas('course', fn ($q) => $q->whereIn('training_program_id', $program->effectiveProgramIds()))
            ->join('training_courses', 'training_courses.id', '=', 'training_classes.training_course_id')
            ->orderBy('training_courses.sort_order')->orderBy('training_classes.sort_order')
            ->select('training_classes.*', 'training_courses.name as course_name')
            ->get();

        $counts = ClassCompletion::whereIn('training_class_id', $classes->pluck('id'))
            ->whereIn('user_id', $this->students()->pluck('users.id'))
            ->select('training_class_id', DB::raw('count(*) as total'))
            ->groupBy('training_class_id')
            ->pluck('total', 'training_class_id');

        return $classes->map(fn (TrainingClass $c) => [
            'title'     => $c->title,
            'course'    => $c->course_name,
            'completed' => (int) ($counts[$c->id] ?? 0),
            'total'     => $cohortSize,
            'pct'       => $cohortSize ? (int) round(((int) ($counts[$c->id] ?? 0)) / $cohortSize * 100) : 0,
        ])->all();
    }

    /* ── Table ── */

    protected function students(): Builder
    {
        return User::query()
            ->where('is_student', true)
            ->whereHas('activeEnrollments', fn ($q) => $q->whereIn('training_program_id', $this->scopedProgramIds()));
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(fn () => $this->students())
            ->defaultSort('name')
            ->emptyStateHeading('No trainees on this program yet')
            ->emptyStateIcon('heroicon-o-user-group')
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Trainee')->searchable()->sortable()
                    ->description(fn (User $r) => $r->email),
                Tables\Columns\TextColumn::make('classes')->label('Classes done')
                    ->state(fn (User $r) => $this->completedCount($r).' / '.($this->getProgram()?->effectiveClassesCount() ?? 0))
                    ->badge()->color('gray'),
                Tables\Columns\TextColumn::make('progress')->label('Progress')
                    ->state(fn (User $r) => $this->progressFor($r).'%')
                    ->badge()
                    ->color(fn (User $r) => match (true) {
                        $this->progressFor($r) >= 80 => 'success',
                        $this->progressFor($r) >= 40 => 'warning',
                        default                      => 'danger',
                    }),
                Tables\Columns\TextColumn::make('attendance')->label('Attendance')
                    ->state(fn (User $r) => $r->attendanceRate() === null ? '—' : $r->attendanceRate().'%')
                    ->badge()
                    ->color(fn (User $r) => match (true) {
                        $r->attendanceRate() === null => 'gray',
                        $r->attendanceRate() >= 75    => 'success',
                        $r->attendanceRate() >= 50    => 'warning',
                        default                       => 'danger',
                    }),
                Tables\Columns\TextColumn::make('submissions')->label('Submitted')
                    ->state(fn (User $r) => $r->submissions()->count())->badge()->color('info')->toggleable(),
                Tables\Columns\TextColumn::make('outstanding')->label('Owing')->money('NGN')
                    ->state(fn (User $r) => $r->totalOutstanding())
                    ->color(fn (User $r) => $r->totalOutstanding() > 0 ? 'danger' : 'success'),
                Tables\Columns\TextColumn::make('lastActivity')->label('Last activity')
                    ->state(fn (User $r) => $r->classCompletions()->latest('completed_at')->value('completed_at')?->diffForHumans() ?? '—')
                    ->color('gray')->toggleable(),
                Tables\Columns\IconColumn::make('is_active')->label('Active')->boolean()->toggleable(),
            ])
            ->filters([
                Tables\Filters\Filter::make('behind')
                    ->label('Behind (under 40%)')
                    ->query(fn (Builder $query) => $query->whereIn('id', $this->studentIdsBelow(40))),
                Tables\Filters\Filter::make('owing')
                    ->label('Has an outstanding balance')
                    ->query(fn (Builder $query) => $query->whereIn('id', $this->studentIdsOwing())),
                Tables\Filters\TernaryFilter::make('is_active')->label('Account active'),
            ])
            ->actions([
                Tables\Actions\Action::make('classes')
                    ->label('Class sheet')->icon('heroicon-o-list-bullet')->color('gray')
                    ->modalHeading(fn (User $record) => $record->displayName().' — class sheet')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close')
                    ->modalContent(fn (User $record) => view('filament.pages.partials.trainee-class-sheet', [
                        'rows' => $this->classSheetFor($record),
                    ])),
                Tables\Actions\Action::make('open')
                    ->label('Open')->icon('heroicon-o-arrow-top-right-on-square')->color('gray')
                    ->url(fn (User $record) => \App\Filament\Resources\StudentResource::getUrl('edit', ['record' => $record])),
            ]);
    }

    /* ── Per-trainee helpers ── */

    protected function completedCount(User $user): int
    {
        $ids = $this->getProgram()?->effectiveClassIds() ?? [];

        return empty($ids) ? 0 : ClassCompletion::where('user_id', $user->id)->whereIn('training_class_id', $ids)->count();
    }

    protected function progressFor(User $user): int
    {
        $total = $this->getProgram()?->effectiveClassesCount() ?? 0;

        return $total ? (int) round($this->completedCount($user) / $total * 100) : 0;
    }

    /** Ids of trainees on this program with money still due. */
    protected function studentIdsOwing(): array
    {
        return $this->students()->get()
            ->filter(fn (User $u) => $u->totalOutstanding() > 0)
            ->pluck('id')->all();
    }

    /** Ids of trainees whose progress is under the given percentage. */
    protected function studentIdsBelow(int $percent): array
    {
        return $this->students()->get()
            ->filter(fn (User $u) => $this->progressFor($u) < $percent)
            ->pluck('id')->all();
    }

    /** Every class in the program with this trainee's completion state. */
    protected function classSheetFor(User $user): array
    {
        $program = $this->getProgram();

        if (! $program) {
            return [];
        }

        $completed = ClassCompletion::where('user_id', $user->id)
            ->whereIn('training_class_id', $program->effectiveClassIds())
            ->pluck('completed_at', 'training_class_id');

        return TrainingClass::query()
            ->whereHas('course', fn ($q) => $q->whereIn('training_program_id', $program->effectiveProgramIds()))
            ->join('training_courses', 'training_courses.id', '=', 'training_classes.training_course_id')
            ->orderBy('training_courses.sort_order')->orderBy('training_classes.sort_order')
            ->select('training_classes.*', 'training_courses.name as course_name')
            ->get()
            ->map(fn (TrainingClass $c) => [
                'title'        => $c->title,
                'course'       => $c->course_name,
                'completed'    => isset($completed[$c->id]),
                'completed_at' => $completed[$c->id] ?? null,
            ])->all();
    }

    /** Re-render everything when the program picker changes. */
    public function updatedProgramId(): void
    {
        $this->resetTable();
    }
}
