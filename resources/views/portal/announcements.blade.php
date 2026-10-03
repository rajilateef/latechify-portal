<x-layouts.portal title="Announcements">
    <h1 class="text-2xl md:text-3xl font-bold text-gray-900 mb-6">Announcements</h1>

    <div class="space-y-4 max-w-3xl">
        @forelse ($announcements as $a)
            <div class="rounded-2xl border border-border bg-white p-6 shadow-sm">
                <div class="flex items-center gap-2 mb-1">
                    <h2 class="font-bold text-gray-900">{{ $a->title }}</h2>
                    @if ($a->program)<span class="rounded-full bg-primary/10 px-2.5 py-0.5 text-xs font-medium text-primary">{{ $a->program->name }}</span>@endif
                </div>
                <div class="text-xs text-gray-400 mb-3">{{ $a->published_at?->format('M j, Y') }} · {{ $a->published_at?->diffForHumans() }}</div>
                <p class="text-gray-700 leading-relaxed whitespace-pre-line">{{ $a->body }}</p>
            </div>
        @empty
            <p class="text-muted-foreground">No announcements yet.</p>
        @endforelse
    </div>

    <div class="mt-6 max-w-3xl">{{ $announcements->links() }}</div>
</x-layouts.portal>
