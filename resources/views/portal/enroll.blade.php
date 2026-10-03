<x-layouts.portal title="Enrol in a program">
    <h1 class="text-2xl md:text-3xl font-bold text-gray-900">Enrol in another program</h1>
    <p class="text-muted-foreground mt-1">Request to join a program — an administrator will review and approve it.</p>

    @if ($pending->isNotEmpty())
        <div class="mt-8">
            <h2 class="text-sm font-semibold uppercase tracking-wide text-muted-foreground mb-3">Pending requests</h2>
            <div class="space-y-2 max-w-2xl">
                @foreach ($pending as $p)
                    <div class="flex items-center justify-between rounded-xl border border-amber-200 bg-amber-50 px-4 py-3">
                        <span class="font-medium text-gray-900">{{ $p->program->name }}</span>
                        <span class="inline-flex items-center gap-1.5 text-sm text-amber-700"><x-lucide name="Clock" class="w-4 h-4"/> Awaiting approval</span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <div class="mt-8 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
        @forelse ($available as $program)
            <div class="rounded-2xl border border-border bg-white p-6 shadow-sm flex flex-col">
                <h3 class="font-bold text-gray-900">{{ $program->name }}</h3>
                <p class="text-sm text-muted-foreground mt-1 flex-1">{{ $program->description }}</p>
                <div class="text-xs text-muted-foreground mt-3">{{ $program->duration_weeks }} weeks · {{ $program->courses()->count() }} courses</div>
                <form method="POST" action="{{ route('portal.enroll.store') }}" class="mt-4">
                    @csrf
                    <input type="hidden" name="training_program_id" value="{{ $program->id }}">
                    <div class="text-xs font-semibold uppercase tracking-wide text-muted-foreground mb-1.5">Enrolment type</div>
                    <div class="space-y-1.5">
                        <label class="flex items-center justify-between gap-2 rounded-lg border border-border px-3 py-2 text-sm cursor-pointer has-[:checked]:border-primary has-[:checked]:bg-primary/5">
                            <span class="flex items-center gap-2"><input type="radio" name="type" value="full_time" class="text-primary focus:ring-primary/30" checked> Full-time</span>
                            <span class="font-semibold text-gray-900">₦{{ number_format($program->fee) }}</span>
                        </label>
                        <label class="flex items-center justify-between gap-2 rounded-lg border border-border px-3 py-2 text-sm cursor-pointer has-[:checked]:border-primary has-[:checked]:bg-primary/5">
                            <span class="flex items-center gap-2"><input type="radio" name="type" value="it_siwes" class="text-primary focus:ring-primary/30"> IT / SIWES</span>
                            <span class="font-semibold text-gray-900">₦{{ number_format($program->feeForType('it_siwes')) }}</span>
                        </label>
                    </div>
                    <button class="mt-3 w-full rounded-lg bg-primary px-5 py-2.5 text-sm font-semibold text-white hover:bg-primary-100">Request to enrol</button>
                </form>
            </div>
        @empty
            <p class="text-muted-foreground col-span-full">You’re already enrolled in (or have requested) all available programs.</p>
        @endforelse
    </div>
</x-layouts.portal>
