<x-layouts.portal :title="$assignment->title">
    <a href="{{ route('portal.assignments') }}" class="inline-flex items-center gap-1.5 text-sm text-muted-foreground hover:text-primary mb-4"><x-lucide name="ArrowLeft" class="w-4 h-4"/> All assignments</a>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2 space-y-6">
            {{-- Brief --}}
            <div class="rounded-2xl border border-border bg-white shadow-sm p-6 md:p-8">
                <div class="text-xs font-semibold uppercase tracking-wide text-primary">{{ $assignment->course->program->name }} · {{ $assignment->course->name }}</div>
                <h2 class="text-2xl font-bold text-gray-900 mt-1">{{ $assignment->title }}</h2>
                <div class="flex flex-wrap items-center gap-4 mt-3 text-sm text-muted-foreground">
                    <span class="inline-flex items-center gap-1.5"><x-lucide name="Target" class="w-4 h-4"/> {{ $assignment->max_score }} points</span>
                    @if ($assignment->due_at)
                        <span class="inline-flex items-center gap-1.5 {{ $assignment->isOverdue() ? 'text-red-600 font-medium' : '' }}"><x-lucide name="Clock" class="w-4 h-4"/> Due {{ $assignment->due_at->format('M j, Y · g:ia') }}</span>
                    @endif
                    @if ($assignment->trainingClass)
                        <span class="inline-flex items-center gap-1.5"><x-lucide name="BookOpen" class="w-4 h-4"/> {{ $assignment->trainingClass->title }}</span>
                    @endif
                </div>
                @if ($assignment->instructions)
                    <div class="mt-5 prose prose-sm max-w-none text-gray-700 whitespace-pre-line">{{ $assignment->instructions }}</div>
                @endif
            </div>

            {{-- Submission form --}}
            <div class="rounded-2xl border border-border bg-white shadow-sm p-6 md:p-8">
                <h3 class="text-lg font-bold text-gray-900 mb-1">{{ $submission ? 'Update your submission' : 'Submit your work' }}</h3>
                <p class="text-sm text-muted-foreground mb-5">Provide a written response, a link, an uploaded file — or any combination.</p>

                @error('content')<div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-2.5 text-sm text-red-700">{{ $message }}</div>@enderror

                <form method="POST" action="{{ route('portal.assignments.submit', $assignment) }}" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Written response</label>
                        <textarea name="content" rows="5" class="w-full rounded-lg border border-border px-3.5 py-2.5 text-sm focus:border-primary focus:ring-1 focus:ring-primary" placeholder="Type your answer…">{{ old('content', $submission->content ?? '') }}</textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Link (GitHub, Google Doc, etc.)</label>
                        <input type="url" name="link" value="{{ old('link', $submission->link ?? '') }}" class="w-full rounded-lg border border-border px-3.5 py-2.5 text-sm focus:border-primary focus:ring-1 focus:ring-primary" placeholder="https://…">
                        @error('link')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">File upload <span class="text-muted-foreground font-normal">(pdf, doc, zip, image — max 20MB)</span></label>
                        <input type="file" name="file" class="block w-full text-sm text-gray-600 file:mr-3 file:rounded-lg file:border-0 file:bg-primary/10 file:px-4 file:py-2 file:text-sm file:font-medium file:text-primary hover:file:bg-primary/20">
                        @error('file')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                        @if ($submission && $submission->file_path)
                            <p class="text-xs text-muted-foreground mt-1.5">Current: <a href="{{ media_url($submission->file_path) }}" target="_blank" class="text-primary hover:underline">view uploaded file</a> — uploading a new one replaces it.</p>
                        @endif
                    </div>
                    <button class="inline-flex items-center gap-2 rounded-lg bg-primary px-5 py-2.5 text-sm font-semibold text-white hover:bg-primary-100">
                        <x-lucide name="Upload" class="w-4 h-4"/> {{ $submission ? 'Resubmit' : 'Submit' }}
                    </button>
                </form>
            </div>
        </div>

        {{-- Status / grade --}}
        <div class="space-y-6">
            <div class="rounded-2xl border border-border bg-white shadow-sm p-6">
                <h3 class="font-bold text-gray-900 mb-4">Status</h3>
                @if (! $submission)
                    <div class="flex items-center gap-2 text-sm text-amber-700"><x-lucide name="AlertCircle" class="w-4 h-4"/> Not submitted yet</div>
                @elseif ($submission->isGraded())
                    <div class="text-center py-2">
                        <div class="text-4xl font-bold text-emerald-600">{{ $submission->score }}<span class="text-lg text-muted-foreground">/{{ $assignment->max_score }}</span></div>
                        <div class="inline-flex items-center gap-1.5 mt-2 rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700"><x-lucide name="CircleCheck" class="w-3.5 h-3.5"/> Graded</div>
                    </div>
                    @if ($submission->feedback)
                        <div class="mt-4 rounded-xl bg-gray-50 p-4">
                            <div class="text-xs font-semibold uppercase tracking-wide text-muted-foreground mb-1">Instructor feedback</div>
                            <p class="text-sm text-gray-700 whitespace-pre-line">{{ $submission->feedback }}</p>
                        </div>
                    @endif
                @else
                    <div class="flex items-center gap-2 text-sm text-blue-700"><x-lucide name="Clock" class="w-4 h-4"/> Submitted {{ $submission->submitted_at?->diffForHumans() }}</div>
                    <p class="text-xs text-muted-foreground mt-2">Awaiting grading by your instructor.</p>
                @endif
            </div>
        </div>
    </div>
</x-layouts.portal>
