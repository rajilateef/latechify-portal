@php $heroBg = media_url(setting('hero_background'), 'background/hero_bg.jpg'); @endphp

<section class="relative overflow-hidden bg-[#02061c] text-white"
         x-data="{
            current: 0,
            total: {{ $slides->count() }},
            timer: null,
            start() { if (this.total > 1 && !this.timer) this.timer = setInterval(() => { this.current = (this.current + 1) % this.total }, 6000) },
            stop() { if (this.timer) { clearInterval(this.timer); this.timer = null } },
            init() { this.start() }
         }"
         x-intersect:enter="start()"
         x-intersect:leave="stop()">
    {{-- Layered background: photo → branded scrim → subtle grid → toned aurora --}}
    <div class="absolute inset-0 bg-cover bg-center scale-105" style="background-image:url('{{ $heroBg }}')"></div>
    <div class="absolute inset-0 bg-gradient-to-br from-[#02061c] via-[#050f4a]/92 to-[#031273]/55"></div>
    <div class="absolute inset-0 bg-gradient-to-t from-[#02061c] via-transparent to-[#02061c]/40"></div>
    <div class="absolute inset-0 dot-pattern-white opacity-[0.06]"></div>
    <div class="absolute -top-32 -left-24 w-[28rem] h-[28rem] rounded-full bg-primary-0/10 blur-3xl aurora-blob"></div>
    <div class="absolute -bottom-40 right-0 w-[30rem] h-[30rem] rounded-full bg-primary/25 blur-3xl aurora-blob" style="animation-delay:-9s"></div>

    <div class="container-custom relative py-20 lg:py-28">
        <div class="grid lg:grid-cols-12 gap-12 lg:gap-16 items-center">
            {{-- Text — slides stacked in one grid cell so cross-fading never
                 changes layout height (no page jump). --}}
            <div class="lg:col-span-6 order-2 lg:order-1">
                <div class="grid">
                    @foreach ($slides as $i => $slide)
                        <div class="col-start-1 row-start-1 transition-all duration-700 ease-[cubic-bezier(.16,1,.3,1)]"
                             style="opacity:{{ $i === 0 ? 1 : 0 }}"
                             :style="current === {{ $i }} ? 'opacity:1;transform:translateY(0)' : 'opacity:0;transform:translateY(16px)'"
                             :class="current === {{ $i }} ? 'z-10' : 'pointer-events-none z-0'"
                             :aria-hidden="current === {{ $i }} ? 'false' : 'true'">
                            <span class="inline-flex items-center gap-2 rounded-full bg-white/10 border border-white/15 px-4 py-1.5 text-xs font-semibold uppercase tracking-wider text-white/90">
                                <span class="w-1.5 h-1.5 rounded-full bg-primary-0"></span> {{ $slide->subtitle }}
                            </span>
                            <h1 class="mt-6 font-bold leading-[1.05] tracking-tight text-4xl sm:text-5xl xl:text-6xl">{{ $slide->title }}</h1>
                            <p class="mt-6 max-w-xl text-lg leading-relaxed text-white/70">{{ $slide->description }}</p>

                            <div class="mt-9 flex flex-wrap items-center gap-4">
                                @if ($slide->button_text)
                                    <a href="{{ $slide->button_link ?: '#' }}"
                                       class="btn-shine group inline-flex items-center gap-2 rounded-xl bg-white px-7 py-3.5 font-semibold text-primary transition-transform hover:-translate-y-0.5">
                                        {{ $slide->button_text }}
                                        <x-lucide name="ArrowRight" class="w-4 h-4 group-hover:translate-x-1 transition-transform"/>
                                    </a>
                                @endif
                                <a href="{{ route('courses.index') }}"
                                   class="inline-flex items-center gap-2 rounded-xl border border-white/25 px-6 py-3.5 font-medium text-white transition-colors hover:bg-white/10">
                                    Explore Courses
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>

                @if ($slides->count() > 1)
                    <div class="flex items-center gap-4 mt-10">
                        <div class="flex gap-2">
                            @foreach ($slides as $i => $slide)
                                <button @click="current = {{ $i }}" aria-label="Go to slide {{ $i + 1 }}"
                                        class="h-1.5 rounded-full transition-all duration-300"
                                        :class="current === {{ $i }} ? 'w-8 bg-white' : 'w-4 bg-white/30 hover:bg-white/50'"></button>
                            @endforeach
                        </div>
                        <span class="text-white/50 text-sm font-medium tabular-nums"
                              x-text="String(current + 1).padStart(2,'0') + ' / ' + String(total).padStart(2,'0')"></span>
                    </div>
                @endif
            </div>

            {{-- Visual --}}
            <div class="lg:col-span-6 order-1 lg:order-2">
                <div class="relative mx-auto w-full max-w-[440px] aspect-square lg:mx-0 lg:max-w-none lg:aspect-auto lg:h-[540px]">
                    {{-- Offset accent blob behind (morphs on its own delay for a layered look) --}}
                    <div class="hero-blob absolute inset-0 bg-primary-0/15 translate-x-3 translate-y-3" style="animation-delay:-7s" aria-hidden="true"></div>

                    {{-- Image clipped to the morphing organic shape --}}
                    <div class="hero-blob relative h-full w-full overflow-hidden ring-1 ring-white/15 shadow-2xl">
                        @foreach ($slides as $i => $slide)
                            <div class="absolute inset-0 bg-cover bg-center transition-opacity duration-700"
                                 style="background-image:url('{{ media_url($slide->image, 'assets/imgs/workspace_1.jpg') }}')"
                                 :class="current === {{ $i }} ? 'opacity-100' : 'opacity-0'"></div>
                        @endforeach
                        <div class="absolute inset-0 bg-gradient-to-t from-black/45 via-transparent to-transparent"></div>
                    </div>

                    {{-- Floating brand badge --}}
                    <div class="absolute -bottom-2 left-1 sm:left-4 flex items-center gap-3 rounded-2xl bg-white px-5 py-3.5 shadow-xl">
                        <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-primary text-white">
                            <x-lucide name="Sparkles" class="w-5 h-5"/>
                        </span>
                        <div class="leading-tight">
                            <div class="font-bold text-gray-900">{{ setting('brand_badge_title', 'Latechify') }}</div>
                            <div class="text-primary text-xs font-medium">{{ setting('brand_badge_sub', 'Digital Hub') }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
