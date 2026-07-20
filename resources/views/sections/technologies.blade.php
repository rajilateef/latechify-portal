@if ($technologies->isNotEmpty())
    <section class="py-12 md:py-16 bg-white">
        <div class="container-custom">
            {{-- Heading --}}
            <div class="max-w-2xl mx-auto text-center reveal">
                <span class="section-eyebrow">{{ setting('tech_eyebrow', 'Tech Stack') }}</span>
                <h2 class="text-xl md:text-2xl mt-3 text-gray-900">
                    {{ setting('tech_heading', "Technologies & Tools You'll Master") }}
                </h2>
                <p class="text-muted-foreground text-sm mt-2">{{ setting('tech_sub', 'The industry-standard tools our learners build with every day.') }}</p>
            </div>

            {{-- Centered, contained marquee panel (does not span the full page) --}}
            <div class="max-w-4xl mx-auto mt-8 rounded-2xl border border-border/60 bg-gradient-to-b from-gray-50 to-white px-3 py-4 shadow-sm reveal-scale">
                <div class="marquee" style="--marquee-duration: 32s">
                    <div class="marquee-track py-1">
                        @foreach ($technologies->concat($technologies) as $tech)
                            <div class="shrink-0 inline-flex items-center gap-2 pl-2 pr-4 py-1.5 rounded-full bg-white border border-border/70 shadow-sm transition-all duration-300 hover:border-primary/40 hover:shadow-md hover:-translate-y-0.5"
                                 @if ($loop->index >= $technologies->count()) aria-hidden="true" @endif>
                                <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-gray-50 border border-border/50">
                                    <x-tech-icon :name="$tech->name" :logo="$tech->logo" class="w-4 h-4"/>
                                </span>
                                <span class="text-sm font-medium text-gray-700 whitespace-nowrap">{{ $tech->name }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </section>
@endif
