<div class="max-w-xl mx-auto">
    <form wire:submit="verify" class="bg-white rounded-2xl border border-border shadow-sm p-6 sm:p-8">
        <label class="block text-sm font-medium text-gray-700 mb-1.5">Certificate ID</label>
        <div class="flex flex-col sm:flex-row gap-3">
            <input type="text" wire:model="certificate_id" placeholder="e.g. LAT-2025-0001"
                   class="flex-1 rounded-lg border border-gray-300 px-4 py-3 focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none uppercase">
            <button type="submit" class="btn-shine inline-flex items-center justify-center gap-2 bg-primary hover:bg-primary/90 text-white px-6 py-3 rounded-lg font-medium transition-colors whitespace-nowrap" wire:loading.attr="disabled" wire:target="verify">
                <span wire:loading.remove wire:target="verify" class="flex items-center gap-2"><x-lucide name="Search" class="w-4 h-4"/> Verify</span>
                <span wire:loading wire:target="verify">Checking…</span>
            </button>
        </div>
        @error('certificate_id') <p class="text-red-500 text-sm mt-2">{{ $message }}</p> @enderror
    </form>

    @if ($searched)
        <div class="mt-6 reveal reveal-visible" wire:key="result-{{ $certificate?->id ?? 'none' }}">
            @if ($certificate && $certificate->status === 'valid')
                <div class="bg-green-50 border border-green-200 rounded-2xl p-6 sm:p-8">
                    <div class="flex items-center gap-3 mb-5">
                        <div class="w-12 h-12 rounded-full bg-green-100 text-green-600 flex items-center justify-center"><x-lucide name="BadgeCheck" class="w-7 h-7"/></div>
                        <div>
                            <h3 class="text-xl font-bold text-green-800">Certificate Verified</h3>
                            <p class="text-green-700 text-sm">This is a genuine Latechify Digital Hub certificate.</p>
                        </div>
                    </div>
                    <dl class="grid sm:grid-cols-2 gap-x-6 gap-y-4 text-sm">
                        <div><dt class="text-muted-foreground">Certificate ID</dt><dd class="font-semibold text-gray-900">{{ $certificate->certificate_id }}</dd></div>
                        <div><dt class="text-muted-foreground">Student</dt><dd class="font-semibold text-gray-900">{{ $certificate->student_name }}</dd></div>
                        <div><dt class="text-muted-foreground">Course</dt><dd class="font-semibold text-gray-900">{{ $certificate->course_name }}</dd></div>
                        <div><dt class="text-muted-foreground">Issued</dt><dd class="font-semibold text-gray-900">{{ $certificate->issue_date?->format('F j, Y') ?? '—' }}</dd></div>
                        @if ($certificate->grade)
                            <div><dt class="text-muted-foreground">Grade</dt><dd class="font-semibold text-gray-900">{{ $certificate->grade }}</dd></div>
                        @endif
                    </dl>
                </div>
            @elseif ($certificate && $certificate->status === 'revoked')
                <div class="bg-yellow-50 border border-yellow-200 rounded-2xl p-6 sm:p-8 flex items-start gap-3">
                    <div class="text-yellow-600 mt-0.5"><x-lucide name="AlertTriangle" class="w-7 h-7"/></div>
                    <div>
                        <h3 class="text-xl font-bold text-yellow-800">Certificate Revoked</h3>
                        <p class="text-yellow-700 text-sm mt-1">A certificate with this ID exists but has been revoked and is no longer valid.</p>
                    </div>
                </div>
            @else
                <div class="bg-red-50 border border-red-200 rounded-2xl p-6 sm:p-8 flex items-start gap-3">
                    <div class="text-red-600 mt-0.5"><x-lucide name="XCircle" class="w-7 h-7"/></div>
                    <div>
                        <h3 class="text-xl font-bold text-red-800">No Certificate Found</h3>
                        <p class="text-red-700 text-sm mt-1">We couldn't find a certificate with that ID. Please double-check the ID and try again.</p>
                    </div>
                </div>
            @endif
        </div>
    @endif
</div>
