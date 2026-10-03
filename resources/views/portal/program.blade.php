<x-layouts.portal :title="$program->name">
    <a href="{{ route('portal.dashboard') }}" class="inline-flex items-center gap-1.5 text-sm text-muted-foreground hover:text-primary mb-4"><x-lucide name="ArrowLeft" class="w-4 h-4"/> Dashboard</a>

    <div class="rounded-2xl bg-primary text-white p-6 md:p-8 mb-8">
        <div class="flex flex-wrap items-center gap-2">
            <h1 class="text-white text-2xl md:text-3xl font-bold">{{ $program->name }}</h1>
            @if ($isComposite)
                <span class="inline-flex items-center gap-1.5 rounded-full bg-white/15 px-3 py-1 text-xs font-semibold"><x-lucide name="Layers" class="w-3.5 h-3.5"/> Bundle</span>
            @endif
        </div>
        @if ($program->description)<p class="text-white/70 mt-2 max-w-2xl">{{ $program->description }}</p>@endif
        @if ($isComposite)
            <p class="text-white/70 mt-2 text-sm">Includes: {{ $program->components->pluck('name')->join(', ') }}</p>
        @endif
        <div class="mt-5 flex flex-wrap gap-x-8 gap-y-2 text-sm text-white/80">
            <span>{{ $program->duration_weeks }} weeks</span>
            <span>{{ $program->effectiveCoursesCount() }} courses</span>
            <span>{{ $stats['done'] }} / {{ $stats['total'] }} classes done ({{ $stats['pct'] }}%)</span>
        </div>
    </div>

    @foreach ($groups as $group)
        @if ($isComposite)
            <div class="flex items-center gap-2 mb-4 mt-2">
                <h2 class="text-lg font-bold text-gray-900">{{ $group['program']->name }}</h2>
                @if ($group['program']->id !== $program->id)
                    <span class="rounded-full bg-primary/10 px-2.5 py-0.5 text-xs font-medium text-primary">Included program</span>
                @endif
            </div>
        @else
            <h2 class="text-lg font-bold text-gray-900 mb-4">Courses</h2>
        @endif

        <div class="grid gap-5 sm:grid-cols-2 {{ ! $loop->last ? 'mb-8' : '' }}">
            @foreach ($group['courses'] as $course)
                @php
                    $classIds = $course->classes->pluck('id');
                    $doneCount = $classIds->intersect($completedIds)->count();
                    $totalCount = $classIds->count();
                    $cpct = $totalCount ? round($doneCount / $totalCount * 100) : 0;
                @endphp
                <a href="{{ route('portal.course', $course) }}" class="block rounded-2xl border border-border bg-white p-5 shadow-sm card-lift">
                    <div class="flex items-start gap-3">
                        <div class="shrink-0 inline-flex w-11 h-11 items-center justify-center rounded-xl bg-primary/10 text-primary">
                            <x-lucide name="{{ $course->icon ?: 'BookOpen' }}" class="w-6 h-6"/>
                        </div>
                        <div class="flex-1">
                            <h3 class="font-bold text-gray-900">{{ $course->name }}</h3>
                            <p class="text-sm text-muted-foreground">{{ $totalCount }} classes</p>
                            <div class="mt-3 flex items-center gap-3">
                                <div class="h-2 flex-1 rounded-full bg-gray-100 overflow-hidden">
                                    <div class="h-full rounded-full bg-gradient-to-r from-primary to-primary-0" style="width: {{ $cpct }}%"></div>
                                </div>
                                <span class="text-xs font-medium text-muted-foreground">{{ $doneCount }}/{{ $totalCount }}</span>
                            </div>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    @endforeach
</x-layouts.portal>
