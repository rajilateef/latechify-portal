<x-filament-panels::page>
    @php
        $summary = $this->summary();
        $breakdown = $this->classBreakdown();
    @endphp

    {{-- Program picker --}}
    <div class="fi-section rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
        <label for="cm-program" class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">Program</label>
        <select id="cm-program" wire:model.live="programId"
                class="fi-input block w-full max-w-md rounded-lg border-none bg-white py-2 pe-8 ps-3 text-base text-gray-950 shadow-sm ring-1 ring-gray-950/10 transition focus:ring-2 focus:ring-primary-600 dark:bg-white/5 dark:text-white dark:ring-white/20 sm:text-sm">
            @foreach ($this->programOptions() as $id => $name)
                <option value="{{ $id }}">{{ $name }}</option>
            @endforeach
        </select>
    </div>

    {{-- Cohort summary --}}
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        @php
            $cards = [
                ['Trainees', $summary['trainees'], $summary['classes'].' classes in syllabus', 'heroicon-o-user-group', 'text-primary-600'],
                ['Average progress', $summary['avgProgress'].'%', $summary['completed'].' finished the program', 'heroicon-o-chart-bar', $summary['avgProgress'] >= 60 ? 'text-success-600' : 'text-warning-600'],
                ['Average attendance', $summary['avgAttendance'] === null ? '—' : $summary['avgAttendance'].'%', 'Across marked sessions', 'heroicon-o-calendar-days', 'text-info-600'],
                ['Needs attention', $summary['atRisk'], $summary['owing'].' with an unpaid balance', 'heroicon-o-exclamation-triangle', $summary['atRisk'] ? 'text-danger-600' : 'text-success-600'],
            ];
        @endphp
        @foreach ($cards as [$label, $value, $hint, $icon, $tone])
            <div class="fi-section rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <div class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ $label }}</div>
                        <div class="mt-1 text-2xl font-semibold tracking-tight text-gray-950 dark:text-white">{{ $value }}</div>
                        <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $hint }}</div>
                    </div>
                    <x-filament::icon :icon="$icon" @class(['h-6 w-6 shrink-0', $tone]) />
                </div>
            </div>
        @endforeach
    </div>

    {{-- Trainee table --}}
    <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
        {{ $this->table }}
    </div>

    {{-- Syllabus heat list: which classes the cohort has actually got through --}}
    @if (! empty($breakdown))
        <div class="fi-section rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <h3 class="text-base font-semibold text-gray-950 dark:text-white">Syllabus coverage</h3>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">How much of the cohort has completed each class — the low bars are where the group is stuck.</p>

            <div class="mt-4 space-y-3">
                @foreach ($breakdown as $row)
                    <div>
                        <div class="flex items-center justify-between gap-3 text-sm">
                            <div class="min-w-0 truncate">
                                <span class="font-medium text-gray-950 dark:text-white">{{ $row['title'] }}</span>
                                <span class="text-gray-500 dark:text-gray-400">· {{ $row['course'] }}</span>
                            </div>
                            <span class="shrink-0 tabular-nums text-gray-500 dark:text-gray-400">{{ $row['completed'] }}/{{ $row['total'] }} · {{ $row['pct'] }}%</span>
                        </div>
                        <div class="mt-1.5 h-2 w-full overflow-hidden rounded-full bg-gray-100 dark:bg-white/10">
                            <div @class([
                                    'h-full rounded-full',
                                    'bg-success-500' => $row['pct'] >= 75,
                                    'bg-warning-500' => $row['pct'] >= 35 && $row['pct'] < 75,
                                    'bg-danger-500'  => $row['pct'] < 35,
                                ]) style="width: {{ max($row['pct'], 1) }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</x-filament-panels::page>
