@if ($benefits->isNotEmpty())
    <section class="py-20 bg-white">
        <div class="container-custom">
            <div class="text-center max-w-2xl mx-auto mb-14 reveal">
                <span class="inline-block bg-primary/10 text-primary text-xs font-semibold uppercase tracking-wide px-3 py-1.5 rounded-full mb-3">{{ setting('benefits_eyebrow', 'Why Latechify') }}</span>
                <h2 class="mb-4">{{ setting('benefits_heading', 'Why Learners Choose Us') }}</h2>
                <p class="text-muted-foreground">{{ setting('benefits_sub', 'Everything you need to go from curious beginner to hired tech professional — under one roof.') }}</p>
            </div>

            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($benefits as $i => $benefit)
                    <div class="reveal card-lift hover-glow group bg-card border border-border rounded-2xl p-6 shadow-sm" data-reveal-delay="{{ ($i % 4) * 90 }}">
                        <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-gradient-to-br from-primary to-[#1a3ad4] text-white mb-4 group-hover:scale-110 transition-transform">
                            <x-lucide name="{{ $benefit->icon }}" class="w-6 h-6"/>
                        </div>
                        <h3 class="text-lg font-bold text-gray-900 mb-2">{{ $benefit->title }}</h3>
                        <p class="text-muted-foreground text-sm leading-relaxed">{{ $benefit->description }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endif
