<x-layouts.portal title="Materials">
    <div class="mb-6">
        <h2 class="text-xl font-bold text-gray-900">Slides &amp; materials</h2>
        <p class="text-sm text-muted-foreground">Every class slide, course document and link resource you have access to.</p>
    </div>

    {{-- Filters --}}
    <form method="GET" class="rounded-2xl border border-border bg-white shadow-sm p-4 mb-6">
        <div class="flex flex-col gap-3 md:flex-row md:items-center">
            <div class="relative flex-1">
                <x-lucide name="Search" class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"/>
                <input type="search" name="q" value="{{ $filters['q'] }}" placeholder="Search slides, documents, links…"
                       class="w-full rounded-lg border border-gray-300 pl-9 pr-4 py-2.5 text-sm focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none">
            </div>

            @if ($programs->count() > 1)
                <select name="program" class="rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none">
                    <option value="">All programs</option>
                    @foreach ($programs as $p)
                        <option value="{{ $p->id }}" @selected($filters['program'] === $p->id)>{{ $p->name }}</option>
                    @endforeach
                </select>
            @endif

            <select name="kind" class="rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none">
                <option value="">All types ({{ $counts['all'] }})</option>
                <option value="files" @selected($filters['kind'] === 'files')>Files &amp; slides ({{ $counts['files'] }})</option>
                <option value="links" @selected($filters['kind'] === 'links')>Link resources ({{ $counts['links'] }})</option>
            </select>

            <div class="flex gap-2">
                <button class="inline-flex items-center gap-1.5 rounded-lg bg-primary px-4 py-2.5 text-sm font-semibold text-white hover:bg-primary/90 transition-colors">
                    <x-lucide name="Filter" class="w-4 h-4"/> Apply
                </button>
                @if ($filters['q'] || $filters['kind'] || $filters['program'])
                    <a href="{{ route('portal.materials') }}" class="inline-flex items-center gap-1.5 rounded-lg border border-border px-4 py-2.5 text-sm font-medium text-gray-600 hover:bg-gray-50 transition-colors">
                        <x-lucide name="X" class="w-4 h-4"/> Clear
                    </a>
                @endif
            </div>
        </div>
    </form>

    @forelse ($groups as $courseName => $courseItems)
        <div class="mb-6">
            <div class="flex items-center gap-2 mb-3">
                <x-lucide name="BookOpen" class="w-4 h-4 text-primary"/>
                <h3 class="font-bold text-gray-900">{{ $courseName }}</h3>
                <span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-600">{{ $courseItems->count() }}</span>
                <span class="text-xs text-muted-foreground">· {{ $courseItems->first()['program'] }}</span>
            </div>

            <div class="rounded-2xl border border-border bg-white shadow-sm divide-y divide-border overflow-hidden">
                @foreach ($courseItems as $item)
                    <a href="{{ $item['url'] }}" @if (! $item['is_file']) target="_blank" rel="noopener" @endif
                       class="flex items-center gap-4 p-4 hover:bg-gray-50 transition-colors">
                        <div class="shrink-0 grid place-items-center w-10 h-10 rounded-lg {{ $item['is_file'] ? 'bg-primary/10 text-primary' : 'bg-sky-50 text-sky-600' }}">
                            <x-lucide :name="$item['icon']" class="w-5 h-5"/>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="font-medium text-gray-900 truncate">{{ $item['title'] }}</div>
                            @if ($item['description'])
                                <p class="text-xs text-muted-foreground line-clamp-1 mt-0.5">{{ $item['description'] }}</p>
                            @endif
                            <div class="text-xs text-muted-foreground mt-0.5 flex flex-wrap items-center gap-x-1.5">
                                <span class="rounded bg-gray-100 px-1.5 py-0.5 font-medium text-gray-600">{{ $item['category'] }}</span>
                                <span>· {{ $item['context'] }}</span>
                                <span>· {{ $item['meta'] }}</span>
                                @if ($item['restricted'])<span class="text-amber-600">· Restricted</span>@endif
                            </div>
                        </div>
                        <x-lucide :name="$item['is_file'] ? 'Download' : 'ArrowUpRight'" class="w-4 h-4 text-gray-400 shrink-0"/>
                    </a>
                @endforeach
            </div>
        </div>
    @empty
        <div class="rounded-2xl border border-border bg-white p-10 text-center shadow-sm">
            <div class="inline-flex w-14 h-14 items-center justify-center rounded-full bg-primary/10 text-primary mb-4"><x-lucide name="FolderOpen" class="w-7 h-7"/></div>
            <h3 class="text-lg font-bold text-gray-900">
                {{ $filters['q'] || $filters['kind'] || $filters['program'] ? 'Nothing matches those filters' : 'No materials yet' }}
            </h3>
            <p class="text-muted-foreground mt-1">
                {{ $filters['q'] || $filters['kind'] || $filters['program']
                    ? 'Try a different search or clear the filters.'
                    : 'Slides and documents appear here as your instructor publishes them.' }}
            </p>
        </div>
    @endforelse
</x-layouts.portal>
