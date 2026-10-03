<x-layouts.portal :title="$course->name">
    <a href="{{ route('portal.program', $course->program) }}" class="inline-flex items-center gap-1.5 text-sm text-muted-foreground hover:text-primary mb-4"><x-lucide name="ArrowLeft" class="w-4 h-4"/> {{ $course->program->name }}</a>

    <div class="flex items-center gap-3 mb-6">
        <div class="shrink-0 inline-flex w-12 h-12 items-center justify-center rounded-xl bg-primary/10 text-primary"><x-lucide name="{{ $course->icon ?: 'BookOpen' }}" class="w-6 h-6"/></div>
        <div>
            <h1 class="text-2xl font-bold text-gray-900">{{ $course->name }}</h1>
            <p class="text-sm text-muted-foreground">{{ $course->classes->count() }} classes</p>
        </div>
    </div>

    @if ($materials->isNotEmpty())
        <h2 class="mb-3 text-lg font-bold text-gray-900 flex items-center gap-2"><x-lucide name="FolderOpen" class="w-5 h-5 text-primary"/> Course documents</h2>
        <div class="rounded-2xl border border-border bg-white shadow-sm divide-y divide-border overflow-hidden mb-8">
            @foreach ($materials as $m)
                <a href="{{ $m->accessUrl() }}" @if (! $m->isFile()) target="_blank" rel="noopener" @endif
                   class="flex items-center gap-4 p-4 hover:bg-gray-50 transition-colors">
                    <div class="shrink-0 grid place-items-center w-10 h-10 rounded-lg bg-primary/10 text-primary"><x-lucide :name="$m->icon()" class="w-5 h-5"/></div>
                    <div class="flex-1 min-w-0">
                        <div class="font-medium text-gray-900 truncate">{{ $m->title }}</div>
                        <div class="text-xs text-muted-foreground">
                            @if ($m->category){{ $m->category }} · @endif
                            @if ($m->isFile()){{ $m->extension() }}@if ($m->humanSize()) · {{ $m->humanSize() }}@endif @else External {{ ucfirst($m->type) }} @endif
                            @if ($m->access === 'selected') · <span class="text-amber-600">Restricted</span>@endif
                        </div>
                    </div>
                    <x-lucide :name="$m->isFile() ? 'Download' : 'ArrowUpRight'" class="w-4 h-4 text-gray-400 shrink-0"/>
                </a>
            @endforeach
        </div>
    @endif

    <h2 class="mb-3 text-lg font-bold text-gray-900 flex items-center gap-2"><x-lucide name="ListChecks" class="w-5 h-5 text-primary"/> Classes</h2>
    <div class="rounded-2xl border border-border bg-white shadow-sm divide-y divide-border overflow-hidden">
        @foreach ($course->classes as $class)
            @php $isDone = in_array($class->id, $completedIds); @endphp
            <a href="{{ route('portal.class', $class) }}" class="flex items-center gap-4 p-4 hover:bg-gray-50 transition-colors">
                <div class="shrink-0 grid place-items-center w-9 h-9 rounded-full {{ $isDone ? 'bg-green-100 text-green-600' : 'bg-gray-100 text-gray-400' }}">
                    @if ($isDone)<x-lucide name="Check" class="w-5 h-5"/>@else<span class="text-sm font-semibold">{{ $loop->iteration }}</span>@endif
                </div>
                <div class="flex-1 min-w-0">
                    <div class="font-medium text-gray-900 truncate">{{ $class->title }}</div>
                    <div class="text-xs text-muted-foreground">{{ $class->resources->where('is_published', true)->count() }} materials {{ $isDone ? '· Completed' : '' }}</div>
                </div>
                <x-lucide name="ChevronRight" class="w-5 h-5 text-gray-300 shrink-0"/>
            </a>
        @endforeach
    </div>

    @if ($course->assignments->where('is_published', true)->isNotEmpty())
        <h2 class="mt-8 mb-3 text-lg font-bold text-gray-900 flex items-center gap-2"><x-lucide name="ClipboardList" class="w-5 h-5 text-primary"/> Assignments</h2>
        <div class="rounded-2xl border border-border bg-white shadow-sm divide-y divide-border overflow-hidden">
            @foreach ($course->assignments->where('is_published', true) as $a)
                <a href="{{ route('portal.assignments.show', $a) }}" class="flex items-center gap-4 p-4 hover:bg-gray-50 transition-colors">
                    <div class="shrink-0 grid place-items-center w-9 h-9 rounded-lg bg-amber-50 text-amber-600"><x-lucide name="FileText" class="w-4 h-4"/></div>
                    <div class="flex-1 min-w-0">
                        <div class="font-medium text-gray-900 truncate">{{ $a->title }}</div>
                        <div class="text-xs text-muted-foreground">{{ $a->max_score }} points @if ($a->due_at) · Due {{ $a->due_at->format('M j') }} @endif</div>
                    </div>
                    <x-lucide name="ChevronRight" class="w-5 h-5 text-gray-300 shrink-0"/>
                </a>
            @endforeach
        </div>
    @endif
</x-layouts.portal>
