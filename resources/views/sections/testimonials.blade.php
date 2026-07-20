<section class="py-20 md:py-24 bg-white">
    <div class="container-custom">
        <div class="text-center max-w-2xl mx-auto mb-16 reveal">
            <span class="section-eyebrow">{{ setting('testimonials_eyebrow', 'Testimonials') }}</span>
            <h2 class="mt-3 text-gray-900">{{ setting('testimonials_heading', 'What Our Clients Say') }}</h2>
            <p class="text-muted-foreground mt-3">{{ setting('testimonials_sub', "Don't just take our word for it. Here's what our students and clients have to say about their experience with Latechify Digital Hub.") }}</p>
        </div>

        <div class="flex flex-col-reverse xl:flex-row gap-8 items-stretch">
            {{-- Testimonials carousel --}}
            @if ($testimonials->isNotEmpty())
                <div class="xl:w-1/2 bg-card rounded-2xl p-8 border border-border shadow-sm flex flex-col"
                     x-data="{
                        i: 0,
                        total: {{ $testimonials->count() }},
                        timer: null,
                        play() { if (this.total > 1 && !this.timer) this.timer = setInterval(() => { this.i = (this.i + 1) % this.total }, 7000) },
                        halt() { if (this.timer) { clearInterval(this.timer); this.timer = null } },
                        go(n) { this.i = n; this.halt(); this.play() },
                        init() { this.play() }
                     }"
                     x-intersect:enter="play()" x-intersect:leave="halt()">

                    <x-lucide name="Quote" class="w-10 h-10 text-primary/15 mb-2"/>

                    {{-- Slides stacked in one grid cell → the card is always as tall as
                         the longest quote, so changing slides never reflows the page. --}}
                    <div class="grid flex-1">
                        @foreach ($testimonials as $idx => $t)
                            <div class="col-start-1 row-start-1 flex flex-col transition-opacity duration-500 ease-in-out"
                                 style="opacity:{{ $idx === 0 ? 1 : 0 }}"
                                 :style="i === {{ $idx }} ? 'opacity:1' : 'opacity:0'"
                                 :class="i === {{ $idx }} ? '' : 'pointer-events-none'"
                                 :aria-hidden="i === {{ $idx }} ? 'false' : 'true'">
                                <div class="flex gap-1 text-amber-400 mb-4">
                                    @for ($s = 0; $s < ($t->rating ?: 5); $s++)<x-lucide name="Star" class="w-5 h-5 fill-current"/>@endfor
                                </div>
                                <p class="text-lg text-gray-700 leading-relaxed mb-6">"{{ $t->quote }}"</p>
                                <div class="flex items-center gap-4 mt-auto">
                                    <img src="{{ media_url($t->avatar, 'assets/imgs/latech.jpg') }}" alt="{{ $t->name }}" loading="lazy" decoding="async" width="56" height="56" class="w-14 h-14 rounded-full object-cover ring-2 ring-primary/10">
                                    <div>
                                        <div class="font-semibold text-gray-900">{{ $t->name }}</div>
                                        <div class="text-sm text-muted-foreground">{{ $t->designation }}</div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    @if ($testimonials->count() > 1)
                        <div class="flex gap-2 mt-8">
                            @foreach ($testimonials as $idx => $t)
                                <button @click="go({{ $idx }})" class="h-2 rounded-full transition-all duration-300" :class="i === {{ $idx }} ? 'w-8 bg-primary' : 'w-2 bg-gray-300 hover:bg-gray-400'" aria-label="Testimonial {{ $idx + 1 }}"></button>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endif

            {{-- Stats card --}}
            <div class="xl:w-1/2 bg-card rounded-2xl px-8 py-10 border border-border shadow-sm flex flex-col justify-center">
                <h3 class="mb-8 text-gray-900">{{ setting('testimonial_card_heading', 'Transforming Diverse Backgrounds Into Tech Careers') }}</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    @foreach ($testimonialStats as $stat)
                        <div class="flex items-start gap-3">
                            <div class="shrink-0 p-3 rounded-full bg-primary/10 text-primary"><x-lucide name="{{ $stat->icon }}" class="w-6 h-6"/></div>
                            <div>
                                <h4 class="font-semibold text-gray-900">{{ $stat->value }}</h4>
                                <p class="text-muted-foreground text-sm">{{ $stat->label }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</section>
