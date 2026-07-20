@php $floatingAd = \App\Models\Advert::live()->forFloating()->first(); @endphp

@if ($floatingAd)
    @php $adExternal = $floatingAd->link_url && \Illuminate\Support\Str::startsWith($floatingAd->link_url, 'http'); @endphp

    <div x-data="{
            show: false,
            key: 'floating_advert_{{ $floatingAd->id }}',
            init() {
                // Dismissed only for the current browser session — it shows again
                // next time the visitor enters the site (new session).
                if (! sessionStorage.getItem(this.key)) {
                    setTimeout(() => { this.show = true; window.renderIcons && window.renderIcons() }, 1200)
                }
            },
            close() { this.show = false; sessionStorage.setItem(this.key, '1') }
         }"
         x-show="show" x-cloak
         x-transition:enter="transition ease-out duration-500"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="fixed z-40 left-1/2 -translate-x-1/2 top-24 md:top-28 w-[calc(100%-2rem)] max-w-2xl"
         role="complementary" aria-label="Advertisement">

        <div class="relative bg-white rounded-2xl border border-border shadow-xl overflow-hidden">
            {{-- Close --}}
            <button @click="close()" aria-label="Close advert"
                    class="absolute top-2.5 right-2.5 z-10 inline-flex items-center justify-center w-8 h-8 rounded-full bg-black/45 hover:bg-black/65 text-white transition-colors">
                <x-lucide name="X" class="w-4 h-4"/>
            </button>

            <div class="flex flex-col sm:flex-row sm:items-stretch">
                {{-- Flier --}}
                <a href="{{ route('advert.click', $floatingAd) }}" @if ($adExternal) target="_blank" rel="noopener" @endif
                   class="relative block shrink-0 aspect-[16/9] sm:aspect-auto sm:w-52 md:w-60 overflow-hidden bg-muted/30">
                    <img src="{{ media_url($floatingAd->image) }}" alt="{{ $floatingAd->title }}" decoding="async"
                         class="absolute inset-0 w-full h-full object-cover">
                    <span class="absolute left-3 top-3 inline-flex items-center gap-1.5 rounded-full bg-white/90 px-2.5 py-1 text-[11px] font-semibold uppercase tracking-wide text-primary shadow-sm">
                        <span class="w-1.5 h-1.5 rounded-full bg-primary-0"></span> Advertisement
                    </span>
                </a>

                {{-- Details --}}
                <div class="flex flex-1 flex-col justify-center p-5 sm:p-6 sm:pr-10">
                    <h4 class="text-lg font-bold leading-snug text-gray-900">{{ $floatingAd->title }}</h4>
                    @if ($floatingAd->description)
                        <p class="mt-2 line-clamp-2 text-sm leading-relaxed text-muted-foreground">{{ $floatingAd->description }}</p>
                    @endif

                    @if ($floatingAd->link_url)
                        <a href="{{ route('advert.click', $floatingAd) }}" @if ($adExternal) target="_blank" rel="noopener" @endif
                           class="btn-shine mt-4 inline-flex w-full sm:w-auto sm:self-start items-center justify-center gap-2 rounded-lg bg-primary px-6 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-primary-100">
                            {{ setting('advert_cta_label', 'Check it out') }}
                            <x-lucide name="ArrowRight" class="w-4 h-4"/>
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endif
