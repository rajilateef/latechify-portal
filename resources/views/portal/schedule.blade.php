<x-layouts.portal title="Schedule">
    @php
        $statusBadge = [
            'present' => ['Present', 'bg-emerald-100 text-emerald-700'],
            'late'    => ['Late', 'bg-amber-100 text-amber-700'],
            'absent'  => ['Absent', 'bg-red-100 text-red-700'],
            'excused' => ['Excused', 'bg-gray-100 text-gray-600'],
        ];
    @endphp

    <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
        <div>
            <h2 class="text-xl font-bold text-gray-900">Live sessions</h2>
            <p class="text-sm text-muted-foreground">Your cohort's scheduled classes and attendance.</p>
        </div>
        @if ($rate !== null)
            <div class="rounded-xl border border-border bg-white px-4 py-2.5 shadow-sm">
                <span class="text-xs text-muted-foreground">Attendance rate</span>
                <span class="ml-2 text-lg font-bold {{ $rate >= 75 ? 'text-emerald-600' : 'text-amber-600' }}">{{ $rate }}%</span>
            </div>
        @endif
    </div>

    {{-- Upcoming --}}
    <h3 class="text-sm font-semibold uppercase tracking-wide text-muted-foreground mb-3">Upcoming</h3>
    <div class="space-y-3 mb-8">
        @forelse ($upcoming as $s)
            <div class="rounded-2xl border border-border bg-white shadow-sm p-4 md:p-5 flex flex-col sm:flex-row sm:items-center gap-4">
                <div class="shrink-0 text-center rounded-xl bg-primary/10 text-primary w-16 py-2">
                    <div class="text-[11px] font-semibold uppercase">{{ $s->starts_at->format('M') }}</div>
                    <div class="text-2xl font-bold leading-none">{{ $s->starts_at->format('j') }}</div>
                    <div class="text-[11px] mt-0.5">{{ $s->starts_at->format('D') }}</div>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2">
                        <h4 class="font-bold text-gray-900">{{ $s->title }}</h4>
                        @if ($s->isLive())<span class="inline-flex items-center gap-1 rounded-full bg-red-100 px-2 py-0.5 text-[11px] font-semibold text-red-600"><span class="w-1.5 h-1.5 rounded-full bg-red-500 animate-pulse"></span> Live</span>@endif
                    </div>
                    <div class="text-sm text-muted-foreground mt-0.5">{{ $s->program->name }}@if ($s->course) · {{ $s->course->name }}@endif</div>
                    <div class="text-xs text-muted-foreground mt-1 flex flex-wrap items-center gap-x-4 gap-y-1">
                        <span class="inline-flex items-center gap-1"><x-lucide name="Clock" class="w-3.5 h-3.5"/> {{ $s->starts_at->format('g:ia') }}@if ($s->ends_at) – {{ $s->ends_at->format('g:ia') }}@endif</span>
                        @if ($s->location)<span class="inline-flex items-center gap-1"><x-lucide name="MapPin" class="w-3.5 h-3.5"/> {{ $s->location }}</span>@endif
                        <span>{{ $s->starts_at->diffForHumans() }}</span>
                    </div>
                    @if ($s->description)<p class="text-sm text-gray-600 mt-2">{{ $s->description }}</p>@endif
                </div>
                @if ($s->join_url)
                    <a href="{{ $s->join_url }}" target="_blank" rel="noopener" class="shrink-0 inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2.5 text-sm font-semibold text-white hover:bg-primary-100"><x-lucide name="Video" class="w-4 h-4"/> Join</a>
                @endif
            </div>
        @empty
            <div class="rounded-2xl border border-dashed border-border bg-white p-8 text-center text-sm text-muted-foreground">No upcoming sessions scheduled.</div>
        @endforelse
    </div>

    {{-- Past + attendance --}}
    @if ($past->isNotEmpty())
        <h3 class="text-sm font-semibold uppercase tracking-wide text-muted-foreground mb-3">Past sessions</h3>
        <div class="rounded-2xl border border-border bg-white shadow-sm divide-y divide-border overflow-hidden">
            @foreach ($past as $s)
                @php $att = $attendance->get($s->id); $badge = $att ? ($statusBadge[$att->status] ?? null) : null; @endphp
                <div class="flex items-center gap-4 p-4">
                    <div class="shrink-0 grid place-items-center w-10 h-10 rounded-lg bg-gray-100 text-gray-500"><x-lucide name="CalendarDays" class="w-5 h-5"/></div>
                    <div class="flex-1 min-w-0">
                        <div class="font-medium text-gray-900 truncate">{{ $s->title }}</div>
                        <div class="text-xs text-muted-foreground">{{ $s->starts_at->format('D, M j · g:ia') }} · {{ $s->program->name }}</div>
                    </div>
                    @if ($badge)
                        <span class="shrink-0 rounded-full px-2.5 py-1 text-xs font-semibold {{ $badge[1] }}">{{ $badge[0] }}</span>
                    @else
                        <span class="shrink-0 rounded-full bg-gray-100 px-2.5 py-1 text-xs text-gray-400">Not marked</span>
                    @endif
                </div>
            @endforeach
        </div>
    @endif
</x-layouts.portal>
