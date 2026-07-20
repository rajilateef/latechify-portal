@props(['faqs'])

<div class="space-y-4" x-data="{ open: 0 }">
    @foreach ($faqs as $i => $faq)
        <div class="border border-border rounded-lg overflow-hidden bg-white">
            <button type="button" @click="open === {{ $i }} ? open = null : open = {{ $i }}"
                    class="w-full flex justify-between items-center gap-4 text-left p-5 text-lg font-medium text-gray-900 hover:bg-gray-50 transition-colors">
                <span>{{ $faq->question }}</span>
                <span class="shrink-0 transition-transform duration-200" :class="open === {{ $i }} ? 'rotate-180' : ''">
                    <x-lucide name="ChevronDown" class="w-5 h-5 text-primary"/>
                </span>
            </button>
            <div x-show="open === {{ $i }}" x-collapse.duration.300ms x-cloak>
                <div class="px-5 pb-5 text-muted-foreground">{{ $faq->answer }}</div>
            </div>
        </div>
    @endforeach
</div>
