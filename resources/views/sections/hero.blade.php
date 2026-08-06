<section class="relative overflow-hidden bg-black isolate"
         x-data="{
            current: 0,
            total: {{ $slides->count() }},
            timer: null,
            start() { if (this.total > 1 && !this.timer) this.timer = setInterval(() => { this.current = (this.current + 1) % this.total }, 7000) },
            stop() { if (this.timer) { clearInterval(this.timer); this.timer = null } },
            init() { this.start() }
         }"
         x-intersect:enter="start()" x-intersect:leave="stop()">

    {{-- Black hole WebGL backdrop (falls back to plain black if WebGL is unavailable).
         Sits behind the whole hero; its lensed disc glows around the two columns. --}}
    <div data-blackhole
         data-focus="0.56,0.42"  data-focus-mobile="0.5,0.82"
         data-scrim="left"       data-scrim-mobile="top"       data-scrim-strength="0.72"
         data-fov="48"           data-fov-mobile="60"
         data-steps="230"        data-steps-mobile="170"
         data-resolution="0.6"   data-resolution-mobile="0.55"
         data-elevation="-5.5"
         class="absolute inset-0 z-0">
        <canvas aria-hidden="true" class="absolute inset-0 block h-full w-full"></canvas>
    </div>

    {{-- Legibility scrim (left on desktop, top on mobile) over the shader's own --}}
    <div class="absolute inset-0 z-[1] pointer-events-none bg-gradient-to-b from-black/85 via-black/25 to-black/55 md:bg-gradient-to-r md:from-black md:via-black/20 md:to-black/35"></div>

    <div class="container-custom relative z-10 flex min-h-[84vh] md:min-h-[42rem] items-center py-20 md:py-24">
        <div class="grid w-full items-center gap-10 lg:grid-cols-12 lg:gap-8">

            {{-- Text (left) --}}
            <div class="order-2 lg:order-1 lg:col-span-6">
                <div class="grid">
                    @foreach ($slides as $i => $slide)
                        <div class="col-start-1 row-start-1 max-w-xl transition-all duration-700 ease-[cubic-bezier(.16,1,.3,1)]"
                             style="opacity:{{ $i === 0 ? 1 : 0 }}"
                             :style="current === {{ $i }} ? 'opacity:1;transform:translateY(0)' : 'opacity:0;transform:translateY(14px)'"
                             :class="current === {{ $i }} ? '' : 'pointer-events-none'"
                             :aria-hidden="current === {{ $i }} ? 'false' : 'true'">
                            @if ($slide->subtitle)
                                <span class="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/5 px-4 py-1.5 text-xs font-semibold uppercase tracking-wide text-white/80 backdrop-blur-sm">
                                    <span class="h-1.5 w-1.5 rounded-full bg-primary-0"></span> {{ $slide->subtitle }}
                                </span>
                            @endif

                            <h1 class="mt-6 font-light leading-[1.05] tracking-tight text-white">{{ $slide->title }}</h1>

                            @if ($slide->description)
                                <p class="mt-6 max-w-lg text-lg leading-relaxed text-white/60">{{ $slide->description }}</p>
                            @endif

                            <div class="mt-9 flex flex-wrap items-center gap-3">
                                @if ($slide->button_text)
                                    <a href="{{ $slide->button_link ?: route('apply') }}"
                                       class="btn-shine group inline-flex items-center gap-2 rounded-full bg-white px-7 py-3.5 text-sm font-medium text-black transition hover:bg-white/90">
                                        {{ $slide->button_text }}
                                        <x-lucide name="ArrowRight" class="w-4 h-4 group-hover:translate-x-1 transition-transform"/>
                                    </a>
                                @endif
                                <a href="{{ route('consultation') }}"
                                   class="inline-flex items-center gap-2 rounded-full border border-white/20 px-7 py-3.5 text-sm text-white/80 transition hover:border-white/40 hover:text-white">
                                    Book a Consultation
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Slide dots --}}
                @if ($slides->count() > 1)
                    <div class="mt-10 flex items-center gap-4">
                        <div class="flex gap-2">
                            @foreach ($slides as $i => $slide)
                                <button @click="current = {{ $i }}" aria-label="Go to slide {{ $i + 1 }}"
                                        class="h-2 rounded-full transition-all duration-300"
                                        :class="current === {{ $i }} ? 'w-8 bg-white' : 'w-2 bg-white/40 hover:bg-white/60'"></button>
                            @endforeach
                        </div>
                        <span class="text-white/50 text-sm font-medium tabular-nums"
                              x-text="String(current + 1).padStart(2,'0') + ' / ' + String(total).padStart(2,'0')"></span>
                    </div>
                @endif
            </div>

            {{-- Image slider (right) — an oval that gently floats over the black hole --}}
            <div class="order-1 lg:order-2 lg:col-span-6">
                <div class="relative mx-auto w-full max-w-xl lg:max-w-2xl animate-float">
                    {{-- slowly rotating halo ring, echoing the accretion disc --}}
                    <div class="absolute -inset-5 rounded-[50%] border border-white/10 hero-orbit pointer-events-none"></div>

                    {{-- horizontal (landscape) oval image frame --}}
                    <div class="relative aspect-[3/2] w-full overflow-hidden rounded-[50%] ring-1 ring-white/20 shadow-[0_25px_70px_-25px_rgba(0,0,0,.8),0_0_90px_-15px_rgba(255,150,60,.4)]">
                        @foreach ($slides as $i => $slide)
                            <div class="absolute inset-0 bg-cover bg-center transition-opacity duration-700 ease-in-out"
                                 style="background-image:url('{{ media_url($slide->image, 'assets/imgs/workspace_1.jpg') }}')"
                                 :class="current === {{ $i }} ? 'opacity-100' : 'opacity-0'"></div>
                        @endforeach
                        <div class="absolute inset-0 bg-gradient-to-t from-black/40 via-transparent to-black/10"></div>
                    </div>

                    {{-- Brand badge on the lower edge of the oval --}}
                    <div class="absolute -bottom-2 left-1/2 -translate-x-1/2 rounded-xl border border-white/15 bg-white/10 px-5 py-2.5 text-center text-white backdrop-blur-md shadow-lg">
                        <div class="text-sm font-bold leading-none">{{ setting('brand_badge_title', 'Latechify') }}</div>
                        <div class="mt-1 text-xs text-primary-0">{{ setting('brand_badge_sub', 'Digital Hub') }}</div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>
