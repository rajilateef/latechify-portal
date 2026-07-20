{{-- Animated media for a service card: auto-rotating slider (2+ images),
     a single static image, or an icon fallback. --}}
@if ($count >= 2)
    <div class="relative aspect-[4/3] rounded-xl overflow-hidden {{ $bg }}"
         x-data="{
            i: 0,
            n: {{ $count }},
            timer: null,
            play() { if (this.n > 1 && !this.timer) this.timer = setInterval(() => { this.i = (this.i + 1) % this.n }, 3200) },
            halt() { if (this.timer) { clearInterval(this.timer); this.timer = null } }
         }"
         x-intersect:enter="play()" x-intersect:leave="halt()">
        @foreach ($imgs as $k => $img)
            <img src="{{ media_url($img) }}" alt="{{ $service->title }}" loading="lazy" decoding="async"
                 class="absolute inset-0 w-full h-full object-cover transition-opacity duration-700 ease-in-out"
                 style="opacity:{{ $k === 0 ? 1 : 0 }}"
                 :style="i === {{ $k }} ? 'opacity:1' : 'opacity:0'">
        @endforeach

        {{-- Progress dots --}}
        <div class="absolute bottom-3 left-1/2 -translate-x-1/2 flex items-center gap-1.5">
            @foreach ($imgs as $k => $img)
                <button type="button" @click="i = {{ $k }}" aria-label="Slide {{ $k + 1 }}"
                        class="h-1.5 rounded-full bg-white/60 transition-all duration-300"
                        :class="i === {{ $k }} ? 'w-5 bg-white' : 'w-1.5'"></button>
            @endforeach
        </div>
    </div>
@elseif ($count === 1)
    <div class="aspect-[4/3] rounded-xl overflow-hidden {{ $bg }}">
        <img src="{{ media_url($imgs[0]) }}" alt="{{ $service->title }}" loading="lazy" decoding="async"
             class="w-full h-full object-cover">
    </div>
@else
    <div class="aspect-[4/3] rounded-xl bg-gradient-to-br from-primary-0/15 to-primary/10 flex items-center justify-center">
        <x-lucide name="{{ $service->icon }}" class="w-16 h-16 text-primary/40"/>
    </div>
@endif
