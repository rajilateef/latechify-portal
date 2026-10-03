<x-layouts.portal title="Assignments">
    @if ($groups->isEmpty())
        <div class="rounded-2xl border border-border bg-white p-10 text-center shadow-sm">
            <div class="inline-flex w-14 h-14 items-center justify-center rounded-full bg-primary/10 text-primary mb-4"><x-lucide name="ClipboardList" class="w-7 h-7"/></div>
            <h3 class="text-lg font-bold text-gray-900">No assignments yet</h3>
            <p class="text-muted-foreground mt-1">Assignments set by your instructors will appear here.</p>
        </div>
    @else
        @foreach ($groups as $programName => $items)
            <div class="mb-8">
                <h3 class="text-sm font-semibold uppercase tracking-wide text-muted-foreground mb-3">{{ $programName }}</h3>
                <div class="rounded-2xl border border-border bg-white shadow-sm divide-y divide-border overflow-hidden">
                    @foreach ($items as $a)
                        @php $sub = $a->submissions->first(); @endphp
                        <a href="{{ route('portal.assignments.show', $a) }}" class="flex items-center gap-4 p-4 hover:bg-gray-50 transition-colors">
                            <div class="shrink-0 grid place-items-center w-10 h-10 rounded-xl bg-primary/10 text-primary"><x-lucide name="FileText" class="w-5 h-5"/></div>
                            <div class="flex-1 min-w-0">
                                <div class="font-medium text-gray-900 truncate">{{ $a->title }}</div>
                                <div class="text-xs text-muted-foreground">{{ $a->course->name }} @if ($a->due_at) · Due {{ $a->due_at->format('M j, Y') }} @endif</div>
                            </div>
                            <div class="shrink-0">
                                @if ($sub && $sub->isGraded())
                                    <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-700">{{ $sub->score }}/{{ $a->max_score }}</span>
                                @elseif ($sub)
                                    <span class="inline-flex items-center gap-1 rounded-full bg-blue-100 px-2.5 py-1 text-xs font-medium text-blue-700"><x-lucide name="Clock" class="w-3.5 h-3.5"/> Submitted</span>
                                @elseif ($a->isOverdue())
                                    <span class="inline-flex items-center gap-1 rounded-full bg-red-100 px-2.5 py-1 text-xs font-semibold text-red-700">Overdue</span>
                                @else
                                    <span class="inline-flex items-center gap-1 rounded-full bg-amber-100 px-2.5 py-1 text-xs font-medium text-amber-700">To do</span>
                                @endif
                            </div>
                            <x-lucide name="ChevronRight" class="w-5 h-5 text-gray-300 shrink-0"/>
                        </a>
                    @endforeach
                </div>
            </div>
        @endforeach
    @endif
</x-layouts.portal>
