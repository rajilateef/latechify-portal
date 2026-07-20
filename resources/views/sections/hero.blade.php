@php $heroBg = media_url(setting('hero_background'), 'background/hero_bg.jpg'); @endphp

<section class="relative overflow-hidden bg-[#f5f7fb]"
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
    {{-- Background --}}
    <div class="absolute inset-0 bg-cover bg-center" style="background-image:url('{{ $heroBg }}')"></div>
    <div class="absolute inset-0 bg-black/60"></div>
    <div class="absolute inset-0 hero-canvas-pattern-light opacity-40"></div>
    <div class="absolute -top-24 -left-20 w-96 h-96 rounded-full bg-primary/30 blur-3xl aurora-blob"></div>
    <div class="absolute -bottom-32 right-0 w-96 h-96 rounded-full bg-primary-0/20 blur-3xl aurora-blob" style="animation-delay:-9s"></div>

    <div class="container-custom relative py-16 lg:py-24">
        <div class="grid lg:grid-cols-12 gap-10 items-center">
            {{-- Text card — slides are stacked in a single grid cell so the
                 container is always as tall as the tallest slide: cross-fading
                 never changes layout height, so the page below can't jump. --}}
            <div class="lg:col-span-5 order-2 lg:order-1">
                <div class="grid">
                    @foreach ($slides as $i => $slide)
                        <div class="col-start-1 row-start-1 hero-card-solid rounded-2xl p-8 lg:p-10 transition-all duration-700 ease-[cubic-bezier(.16,1,.3,1)]"
                             style="opacity:{{ $i === 0 ? 1 : 0 }}"
                             :style="current === {{ $i }} ? 'opacity:1;transform:translateY(0)' : 'opacity:0;transform:translateY(12px)'"
                             :class="current === {{ $i }} ? 'z-10' : 'pointer-events-none z-0'"
                             :aria-hidden="current === {{ $i }} ? 'false' : 'true'">
                            <span class="inline-block bg-primary/10 text-primary border border-primary/15 text-xs font-semibold uppercase tracking-wide px-3 py-1.5 rounded-full mb-5">{{ $slide->subtitle }}</span>
                            <h1 class="text-gray-900 mb-5 leading-tight">{{ $slide->title }}</h1>
                            <p class="text-gray-500 text-lg mb-8">{{ $slide->description }}</p>
                            @if ($slide->button_text)
                                <a href="{{ $slide->button_link ?: '#' }}"
                                   class="btn-shine inline-flex items-center gap-2 bg-gradient-to-r from-primary to-[#1a3ad4] hover:from-[#1a3ad4] hover:to-primary text-white px-7 py-3.5 rounded-lg shadow-lg shadow-primary/20 font-medium transition-all group">
                                    {{ $slide->button_text }}
                                    <x-lucide name="ArrowRight" class="w-4 h-4 group-hover:translate-x-1 transition-transform"/>
                                </a>
                            @endif
                        </div>
                    @endforeach
                </div>

                @if ($slides->count() > 1)
                    <div class="flex items-center gap-4 mt-8">
                        <div class="flex gap-2">
                            @foreach ($slides as $i => $slide)
                                <button @click="current = {{ $i }}" aria-label="Go to slide {{ $i + 1 }}"
                                        class="h-2 rounded-full transition-all duration-300"
                                        :class="current === {{ $i }} ? 'w-8 bg-gradient-to-r from-primary to-[#1a3ad4]' : 'w-2 bg-white/50 hover:bg-white/70'"></button>
                            @endforeach
                        </div>
                        <span class="text-white/80 text-sm font-medium tabular-nums"
                              x-text="String(current + 1).padStart(2,'0') + '/' + String(total).padStart(2,'0')"></span>
                    </div>
                @endif
            </div>

            {{-- Image stage --}}
            <div class="lg:col-span-7 order-1 lg:order-2">
                <div class="relative rounded-2xl overflow-hidden shadow-2xl aspect-[16/10] lg:h-[500px]">
                    @foreach ($slides as $i => $slide)
                        <div class="absolute inset-0 bg-cover bg-center transition-opacity duration-700"
                             style="background-image:url('{{ media_url($slide->image, 'assets/imgs/workspace_1.jpg') }}')"
                             :class="current === {{ $i }} ? 'opacity-100' : 'opacity-0'"></div>
                    @endforeach
                    <div class="absolute inset-0 bg-gradient-to-t from-black/30 to-transparent"></div>
                    <div class="absolute bottom-5 right-5 bg-gradient-to-r from-primary to-[#1a3ad4] text-white rounded-xl px-5 py-3 shadow-lg">
                        <div class="font-bold leading-none">{{ setting('brand_badge_title', 'Latechify') }}</div>
                        <div class="text-primary-0 text-xs mt-1">{{ setting('brand_badge_sub', 'Digital Hub') }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="absolute bottom-0 inset-x-0 h-24 bg-gradient-to-t from-[#f5f7fb] to-transparent"></div>
</section>
