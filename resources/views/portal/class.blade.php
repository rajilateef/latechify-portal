<x-layouts.portal :title="$class->title">
    <a href="{{ route('portal.course', $class->course) }}" class="inline-flex items-center gap-1.5 text-sm text-muted-foreground hover:text-primary mb-4"><x-lucide name="ArrowLeft" class="w-4 h-4"/> {{ $class->course->name }}</a>

    <div class="rounded-2xl border border-border bg-white shadow-sm p-6 md:p-8">
        <div class="flex items-start justify-between gap-4">
            <div>
                <div class="text-xs font-semibold uppercase tracking-wide text-primary">{{ $class->course->program->name }} · {{ $class->course->name }}</div>
                <h1 class="text-2xl font-bold text-gray-900 mt-1">{{ $class->title }}</h1>
            </div>
            @if ($completed)
                <span class="shrink-0 inline-flex items-center gap-1.5 rounded-full bg-green-100 px-3 py-1.5 text-sm font-semibold text-green-700"><x-lucide name="CheckCircle" class="w-4 h-4"/> Completed</span>
            @else
                <span class="shrink-0 inline-flex items-center gap-1.5 rounded-full bg-gray-100 px-3 py-1.5 text-sm font-medium text-gray-500">In progress</span>
            @endif
        </div>

        @if ($class->description)<p class="text-muted-foreground mt-3">{{ $class->description }}</p>@endif

        @php
            $published = $class->resources->where('is_published', true);
            $slides = $published->where('type', '!=', 'link');
            $links  = $published->where('type', 'link');
        @endphp

        <h2 class="mt-8 mb-3 text-lg font-bold text-gray-900 flex items-center gap-2"><x-lucide name="Presentation" class="w-5 h-5 text-primary"/> Slides &amp; materials</h2>
        @if ($slides->isEmpty())
            <p class="text-sm text-muted-foreground">No slides have been added for this class yet.</p>
        @else
            <div class="grid gap-3 sm:grid-cols-2">
                @foreach ($slides as $resource)
                    <a href="{{ $resource->accessUrl() }}" @if ($resource->isExternal()) target="_blank" rel="noopener" @endif
                       class="flex items-start gap-3 rounded-xl border border-border p-4 hover:border-primary/40 hover:bg-primary/5 transition-colors">
                        <div class="shrink-0 grid place-items-center w-10 h-10 rounded-lg bg-primary/10 text-primary">
                            <x-lucide :name="$resource->icon()" class="w-5 h-5"/>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="font-medium text-gray-900 truncate">{{ $resource->title }}</div>
                            @if ($resource->description)
                                <p class="text-xs text-muted-foreground line-clamp-2 mt-0.5">{{ $resource->description }}</p>
                            @endif
                            <div class="text-xs text-muted-foreground mt-0.5">
                                @if ($resource->category){{ $resource->category }} · @endif
                                {{ $resource->actionLabel() }}@if ($resource->isFile() && $resource->humanSize()) · {{ $resource->humanSize() }}@endif
                            </div>
                        </div>
                        <x-lucide :name="$resource->isFile() ? 'Download' : 'ArrowUpRight'" class="w-4 h-4 text-gray-300 shrink-0"/>
                    </a>
                @endforeach
            </div>
        @endif

        @if ($links->isNotEmpty())
            <h2 class="mt-8 mb-3 text-lg font-bold text-gray-900 flex items-center gap-2"><x-lucide name="Link" class="w-5 h-5 text-primary"/> Additional resources</h2>
            <div class="rounded-xl border border-border divide-y divide-border overflow-hidden">
                @foreach ($links as $resource)
                    <a href="{{ $resource->accessUrl() }}" target="_blank" rel="noopener"
                       class="flex items-start gap-3 p-4 hover:bg-primary/5 transition-colors">
                        <div class="shrink-0 grid place-items-center w-9 h-9 rounded-lg bg-sky-50 text-sky-600">
                            <x-lucide name="ExternalLink" class="w-4 h-4"/>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="font-medium text-gray-900 truncate">{{ $resource->title }}</div>
                            @if ($resource->description)
                                <p class="text-xs text-muted-foreground line-clamp-2 mt-0.5">{{ $resource->description }}</p>
                            @endif
                            <div class="text-xs text-gray-400 truncate mt-0.5">{{ $resource->url }}</div>
                        </div>
                        <x-lucide name="ArrowUpRight" class="w-4 h-4 text-gray-300 shrink-0"/>
                    </a>
                @endforeach
            </div>
        @endif

        @if ($class->assignments->where('is_published', true)->isNotEmpty())
            <h2 class="mt-8 mb-3 text-lg font-bold text-gray-900 flex items-center gap-2"><x-lucide name="ClipboardList" class="w-5 h-5 text-primary"/> Assignments</h2>
            <div class="grid gap-3 sm:grid-cols-2">
                @foreach ($class->assignments->where('is_published', true) as $a)
                    <a href="{{ route('portal.assignments.show', $a) }}" class="flex items-center gap-3 rounded-xl border border-border p-4 hover:border-primary/40 hover:bg-primary/5 transition-colors">
                        <div class="shrink-0 grid place-items-center w-10 h-10 rounded-lg bg-amber-50 text-amber-600"><x-lucide name="FileText" class="w-5 h-5"/></div>
                        <div class="flex-1 min-w-0">
                            <div class="font-medium text-gray-900 truncate">{{ $a->title }}</div>
                            <div class="text-xs text-muted-foreground">{{ $a->max_score }} points @if ($a->due_at) · Due {{ $a->due_at->format('M j') }} @endif</div>
                        </div>
                        <x-lucide name="ArrowUpRight" class="w-4 h-4 text-gray-300"/>
                    </a>
                @endforeach
            </div>
        @endif

        <p class="mt-8 text-xs text-muted-foreground">Your instructor marks classes as completed as you progress through the program.</p>
    </div>
</x-layouts.portal>
