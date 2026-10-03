<x-layouts.portal title="Dashboard">
    <div class="mb-6">
        <h2 class="text-2xl md:text-3xl font-bold text-gray-900">Welcome back, {{ \Illuminate\Support\Str::before($user->displayName(), ' ') }} 👋</h2>
        <p class="text-muted-foreground mt-1">Here's an overview of your training.</p>
    </div>

    @if ($enrollments->isEmpty())
        <div class="rounded-2xl border border-border bg-white p-10 text-center shadow-sm">
            <div class="inline-flex w-14 h-14 items-center justify-center rounded-full bg-primary/10 text-primary mb-4"><x-lucide name="GraduationCap" class="w-7 h-7"/></div>
            <h3 class="text-lg font-bold text-gray-900">You're not in an active program yet</h3>
            <p class="text-muted-foreground mt-1">Once an administrator activates your enrolment, your program will appear here.</p>
            <a href="{{ route('portal.enroll') }}" class="mt-5 inline-flex items-center gap-2 rounded-lg bg-primary px-5 py-2.5 text-sm font-semibold text-white hover:bg-primary-100">Browse programs</a>
        </div>
    @else
        {{-- Stat cards --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            @php
                $cards = [
                    ['icon' => 'TrendingUp', 'label' => 'Overall progress', 'value' => $stats['progress'].'%', 'tint' => 'text-primary bg-primary/10'],
                    ['icon' => 'CalendarCheck', 'label' => 'Attendance', 'value' => $stats['attendance'] !== null ? $stats['attendance'].'%' : '—', 'tint' => 'text-emerald-600 bg-emerald-50'],
                    ['icon' => 'ClipboardList', 'label' => 'Assignments due', 'value' => $stats['dueCount'], 'tint' => 'text-amber-600 bg-amber-50'],
                    ['icon' => 'Wallet', 'label' => 'Outstanding', 'value' => '₦'.number_format($stats['outstanding']), 'tint' => ($stats['outstanding'] > 0 ? 'text-red-600 bg-red-50' : 'text-emerald-600 bg-emerald-50')],
                ];
            @endphp
            @foreach ($cards as $c)
                <div class="rounded-2xl border border-border bg-white p-4 md:p-5 shadow-sm">
                    <div class="inline-flex w-10 h-10 items-center justify-center rounded-lg {{ $c['tint'] }} mb-3"><x-lucide :name="$c['icon']" class="w-5 h-5"/></div>
                    <div class="text-2xl font-bold text-gray-900">{{ $c['value'] }}</div>
                    <div class="text-xs text-muted-foreground mt-0.5">{{ $c['label'] }}</div>
                </div>
            @endforeach
        </div>

        <div class="grid gap-6 lg:grid-cols-3">
            <div class="lg:col-span-2 space-y-6">
                {{-- Continue learning (per program) --}}
                @foreach ($enrollments as $enrollment)
                    @php
                        $program = $enrollment->program;
                        $done = $enrollment->completedClassesCount();
                        $total = $enrollment->totalClasses();
                        $pct = $enrollment->progressPercent();
                        $next = $enrollment->nextClass();
                    @endphp
                    <div class="rounded-2xl border border-border bg-white p-6 shadow-sm">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <span class="text-xs font-semibold uppercase tracking-wide text-primary">{{ $program->isComposite() ? 'Bundle program' : 'Program' }}</span>
                                <h3 class="text-xl font-bold text-gray-900 mt-1">{{ $program->name }}</h3>
                                <p class="text-sm text-muted-foreground mt-1">{{ $program->duration_weeks }}-week training · {{ $program->effectiveCoursesCount() }} courses · {{ $total }} classes</p>
                                @if ($program->isComposite())
                                    <p class="text-xs text-muted-foreground mt-1">Includes {{ $program->components->pluck('name')->join(' + ') }}</p>
                                @endif
                            </div>
                            <a href="{{ route('portal.program', $program) }}" class="shrink-0 inline-flex items-center gap-1.5 rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-white hover:bg-primary-100">
                                Open <x-lucide name="ArrowRight" class="w-4 h-4"/>
                            </a>
                        </div>

                        <div class="mt-5">
                            <div class="flex items-center justify-between text-sm mb-1.5">
                                <span class="font-medium text-gray-700">Classes completed</span>
                                <span class="text-muted-foreground">{{ $done }} / {{ $total }} ({{ $pct }}%)</span>
                            </div>
                            <div class="h-2.5 w-full rounded-full bg-gray-100 overflow-hidden">
                                <div class="h-full rounded-full bg-gradient-to-r from-primary to-primary-0" style="width: {{ $pct }}%"></div>
                            </div>
                        </div>

                        @if ($next)
                            <a href="{{ route('portal.class', $next) }}" class="mt-4 flex items-center gap-3 rounded-xl border border-primary/20 bg-primary/5 p-3.5 hover:bg-primary/10 transition-colors">
                                <div class="shrink-0 grid place-items-center w-9 h-9 rounded-lg bg-primary text-white"><x-lucide name="Play" class="w-4 h-4"/></div>
                                <div class="min-w-0">
                                    <div class="text-xs text-muted-foreground">Continue where you left off</div>
                                    <div class="font-medium text-gray-900 truncate">{{ $next->course->name }} · {{ $next->title }}</div>
                                </div>
                                <x-lucide name="ArrowRight" class="w-4 h-4 text-primary ml-auto shrink-0"/>
                            </a>
                        @else
                            <div class="mt-4 flex items-center gap-2 rounded-xl border border-emerald-200 bg-emerald-50 p-3.5 text-sm text-emerald-700">
                                <x-lucide name="PartyPopper" class="w-4 h-4"/> All classes complete — well done!
                            </div>
                        @endif

                        <div class="mt-5 grid grid-cols-3 gap-3">
                            <div class="rounded-xl bg-gray-50 p-3">
                                <div class="text-xs text-muted-foreground">Time spent</div>
                                <div class="mt-1 font-bold text-gray-900">{{ $enrollment->daysSpent() !== null ? $enrollment->daysSpent().'d' : '—' }}</div>
                            </div>
                            <div class="rounded-xl bg-gray-50 p-3">
                                <div class="text-xs text-muted-foreground">Remaining</div>
                                <div class="mt-1 font-bold {{ ($enrollment->daysRemaining() ?? 1) <= 7 ? 'text-red-600' : 'text-gray-900' }}">{{ $enrollment->daysRemaining() !== null ? $enrollment->daysRemaining().'d' : '—' }}</div>
                            </div>
                            <div class="rounded-xl bg-gray-50 p-3">
                                <div class="text-xs text-muted-foreground">Fees</div>
                                <div class="mt-1 font-bold {{ $enrollment->outstanding() > 0 ? 'text-red-600' : 'text-emerald-600' }}">{{ $enrollment->outstanding() > 0 ? '₦'.number_format($enrollment->outstanding()) : 'Paid' }}</div>
                            </div>
                        </div>
                    </div>
                @endforeach

                {{-- Assignments due --}}
                <div class="rounded-2xl border border-border bg-white p-6 shadow-sm">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2"><x-lucide name="ClipboardList" class="w-5 h-5 text-primary"/> Assignments to do</h3>
                        <a href="{{ route('portal.assignments') }}" class="text-xs text-primary hover:underline">All assignments</a>
                    </div>
                    @forelse ($dueAssignments as $a)
                        <a href="{{ route('portal.assignments.show', $a) }}" class="flex items-center gap-3 py-3 border-t border-border first:border-0 first:pt-0 hover:bg-gray-50 -mx-2 px-2 rounded-lg">
                            <div class="shrink-0 grid place-items-center w-9 h-9 rounded-lg bg-amber-50 text-amber-600"><x-lucide name="FileText" class="w-4 h-4"/></div>
                            <div class="min-w-0 flex-1">
                                <div class="font-medium text-gray-900 truncate">{{ $a->title }}</div>
                                <div class="text-xs text-muted-foreground">{{ $a->course->name }}</div>
                            </div>
                            @if ($a->due_at)
                                <span class="shrink-0 text-xs {{ $a->isOverdue() ? 'text-red-600 font-semibold' : 'text-muted-foreground' }}">{{ $a->isOverdue() ? 'Overdue' : 'Due '.$a->due_at->format('M j') }}</span>
                            @endif
                        </a>
                    @empty
                        <p class="text-sm text-muted-foreground">Nothing due right now — you're all caught up. 🎉</p>
                    @endforelse
                </div>
            </div>

            {{-- Right column --}}
            <div class="space-y-6">
                {{-- Upcoming sessions --}}
                <div class="rounded-2xl border border-border bg-white p-6 shadow-sm">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2"><x-lucide name="CalendarDays" class="w-5 h-5 text-primary"/> Upcoming</h3>
                        <a href="{{ route('portal.schedule') }}" class="text-xs text-primary hover:underline">Schedule</a>
                    </div>
                    @forelse ($upcomingSessions as $s)
                        <div class="py-3 border-t border-border first:border-0 first:pt-0">
                            <div class="flex items-start gap-3">
                                <div class="shrink-0 text-center rounded-lg bg-primary/10 text-primary w-11 py-1">
                                    <div class="text-[10px] font-semibold uppercase">{{ $s->starts_at->format('M') }}</div>
                                    <div class="text-lg font-bold leading-none">{{ $s->starts_at->format('j') }}</div>
                                </div>
                                <div class="min-w-0">
                                    <div class="text-sm font-medium text-gray-900 truncate">{{ $s->title }}</div>
                                    <div class="text-xs text-muted-foreground">{{ $s->starts_at->format('D, g:ia') }}</div>
                                    @if ($s->join_url)
                                        <a href="{{ $s->join_url }}" target="_blank" rel="noopener" class="inline-flex items-center gap-1 text-xs text-primary font-medium mt-1 hover:underline"><x-lucide name="Video" class="w-3.5 h-3.5"/> Join link</a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-muted-foreground">No sessions scheduled.</p>
                    @endforelse
                </div>

                {{-- Announcements --}}
                <div class="rounded-2xl border border-border bg-white p-6 shadow-sm">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2"><x-lucide name="Megaphone" class="w-5 h-5 text-primary"/> Announcements</h3>
                        <a href="{{ route('portal.announcements') }}" class="text-xs text-primary hover:underline">View all</a>
                    </div>
                    @forelse ($announcements as $a)
                        <div class="py-3 border-t border-border first:border-0 first:pt-0">
                            <div class="text-sm font-semibold text-gray-900">{{ $a->title }}</div>
                            <p class="text-sm text-muted-foreground mt-1 line-clamp-2">{{ $a->body }}</p>
                            <div class="text-xs text-gray-400 mt-1">{{ $a->published_at?->diffForHumans() }}</div>
                        </div>
                    @empty
                        <p class="text-sm text-muted-foreground">No announcements yet.</p>
                    @endforelse
                </div>

                {{-- Recent activity --}}
                @if ($activity->isNotEmpty())
                    <div class="rounded-2xl border border-border bg-white p-6 shadow-sm">
                        <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2 mb-4"><x-lucide name="Activity" class="w-5 h-5 text-primary"/> Recent activity</h3>
                        <div class="space-y-3">
                            @foreach ($activity as $c)
                                <div class="flex items-start gap-2.5 text-sm">
                                    <x-lucide name="CircleCheck" class="w-4 h-4 text-emerald-500 mt-0.5 shrink-0"/>
                                    <div>
                                        <span class="text-gray-900">Completed <span class="font-medium">{{ $c->trainingClass?->title }}</span></span>
                                        <div class="text-xs text-gray-400">{{ $c->completed_at?->diffForHumans() }}</div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
    @endif
</x-layouts.portal>
