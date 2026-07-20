@if ($impactStats->isNotEmpty())
    <section class="relative py-20 bg-primary overflow-hidden">
        <div class="absolute inset-0 dot-pattern-white opacity-[0.06]"></div>
        <div class="absolute -top-24 -left-16 w-80 h-80 rounded-full bg-primary-0/20 blur-3xl aurora-blob"></div>
        <div class="absolute -bottom-24 -right-10 w-80 h-80 rounded-full bg-blue-500/20 blur-3xl aurora-blob" style="animation-delay:-8s"></div>

        <div class="container-custom relative">
            <div class="text-center max-w-2xl mx-auto mb-14 reveal">
                <span class="inline-block bg-white/10 text-white text-xs font-semibold uppercase tracking-wide px-3 py-1.5 rounded-full mb-3">Our Impact</span>
                <h2 class="text-white mb-3">Real Results, Real Careers</h2>
                <p class="text-white/70">Numbers that reflect our commitment to transforming lives through technology.</p>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-8">
                @foreach ($impactStats as $i => $stat)
                    @php preg_match('/^([\d.]+)(.*)$/', $stat->value, $m); $num = $m[1] ?? 0; $suffix = $m[2] ?? ''; @endphp
                    <div class="text-center reveal" data-reveal-delay="{{ $i * 100 }}">
                        <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-white/10 text-primary-0 mb-4">
                            <x-lucide name="{{ $stat->icon }}" class="w-7 h-7"/>
                        </div>
                        <div class="text-4xl md:text-5xl font-extrabold text-white tabular-nums" data-count="{{ $num }}" data-suffix="{{ $suffix }}">0{{ $suffix }}</div>
                        <div class="text-white/70 text-sm mt-2">{{ $stat->label }}</div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endif
