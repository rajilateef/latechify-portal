<x-layouts.portal title="Certificates">
    <div class="mb-6">
        <h2 class="text-xl font-bold text-gray-900">Certificates &amp; achievements</h2>
        <p class="text-sm text-muted-foreground">Certificates are issued when you complete a program.</p>
    </div>

    {{-- Achievements --}}
    @if (!empty($badges))
        <div class="mb-8">
            <h3 class="text-sm font-semibold uppercase tracking-wide text-muted-foreground mb-3">Your badges</h3>
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
                @foreach ($badges as $b)
                    <div class="rounded-2xl border border-border bg-white p-4 text-center shadow-sm">
                        <div class="inline-flex w-12 h-12 items-center justify-center rounded-full bg-amber-100 text-amber-600 mb-2"><x-lucide :name="$b['icon']" class="w-6 h-6"/></div>
                        <div class="text-sm font-semibold text-gray-900">{{ $b['label'] }}</div>
                        <div class="text-xs text-muted-foreground mt-0.5">{{ $b['desc'] }}</div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Issued certificates --}}
    <h3 class="text-sm font-semibold uppercase tracking-wide text-muted-foreground mb-3">Certificates</h3>
    @if ($certificates->isNotEmpty())
        <div class="grid gap-5 sm:grid-cols-2">
            @foreach ($certificates as $cert)
                <div class="relative overflow-hidden rounded-2xl border-2 border-primary/20 bg-gradient-to-br from-primary/5 to-white p-6 shadow-sm">
                    <div class="absolute top-4 right-4 text-primary/10"><x-lucide name="Award" class="w-20 h-20"/></div>
                    <div class="relative">
                        <div class="inline-flex items-center gap-1.5 rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-700 mb-3"><x-lucide name="CircleCheck" class="w-3.5 h-3.5"/> {{ ucfirst($cert->status) }}</div>
                        <div class="text-xs font-semibold uppercase tracking-wide text-primary">Certificate of completion</div>
                        <h4 class="text-lg font-bold text-gray-900 mt-1">{{ $cert->course_name }}</h4>
                        <p class="text-sm text-muted-foreground mt-1">Awarded to {{ $cert->student_name }}</p>
                        <div class="flex items-center gap-4 mt-4 text-xs text-muted-foreground">
                            <span>ID: <span class="font-mono font-medium text-gray-700">{{ $cert->certificate_id }}</span></span>
                            <span>{{ $cert->issue_date?->format('M j, Y') }}</span>
                        </div>
                        <div class="flex items-center gap-2 mt-5">
                            <a href="{{ route('verify-certificate') }}?id={{ $cert->certificate_id }}" target="_blank" class="inline-flex items-center gap-1.5 rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-white hover:bg-primary-100"><x-lucide name="BadgeCheck" class="w-4 h-4"/> Verify</a>
                            @if ($cert->file_path)
                                <a href="{{ media_url($cert->file_path) }}" target="_blank" class="inline-flex items-center gap-1.5 rounded-lg border border-border px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"><x-lucide name="Download" class="w-4 h-4"/> Download</a>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="rounded-2xl border border-dashed border-border bg-white p-8 text-center">
            <div class="inline-flex w-12 h-12 items-center justify-center rounded-full bg-gray-100 text-gray-400 mb-3"><x-lucide name="Award" class="w-6 h-6"/></div>
            <p class="text-sm text-muted-foreground">No certificates yet. Complete a program to earn one.</p>
            @foreach ($enrollments as $e)
                <div class="mt-4 max-w-sm mx-auto">
                    <div class="flex items-center justify-between text-xs mb-1">
                        <span class="font-medium text-gray-700">{{ $e->program->name }}</span>
                        <span class="text-muted-foreground">{{ $e->progressPercent() }}%</span>
                    </div>
                    <div class="h-2 w-full rounded-full bg-gray-100 overflow-hidden">
                        <div class="h-full rounded-full bg-primary" style="width: {{ $e->progressPercent() }}%"></div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</x-layouts.portal>
