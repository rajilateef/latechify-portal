@php
    $logoChip = fn ($p) => $p->logo;
@endphp

{{-- Accreditations & partnerships (only shown once the admin adds them) --}}
@if ($accreditations->isNotEmpty())
    <section class="py-20 bg-gray-50">
        <div class="container-custom">
            <div class="text-center max-w-2xl mx-auto mb-12 reveal">
                <span class="inline-block bg-primary/10 text-primary text-xs font-semibold uppercase tracking-wide px-3 py-1.5 rounded-full mb-3">{{ setting('partners_eyebrow', 'Recognition') }}</span>
                <h2 class="mb-3">{{ setting('partners_heading', 'Accreditations & Partnerships') }}</h2>
                <p class="text-muted-foreground">{{ setting('partners_sub', 'Proudly recognised and supported by leading institutions.') }}</p>
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-6">
                @foreach ($accreditations as $i => $item)
                    <div class="reveal card-lift bg-white rounded-2xl border border-border p-6 flex items-center justify-center h-28" data-reveal-delay="{{ ($i % 4) * 80 }}">
                        @if ($item->logo)
                            <img src="{{ media_url($item->logo) }}" alt="{{ $item->name }}" loading="lazy" decoding="async" class="max-h-14 w-auto object-contain">
                        @else
                            <span class="font-semibold text-gray-700 text-center">{{ $item->name }}</span>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endif

{{-- Where our graduates work (only shown once the admin adds employer logos) --}}
@if ($employers->isNotEmpty())
    <section class="py-16 bg-white">
        <div class="container-custom">
            <p class="text-center text-sm font-semibold uppercase tracking-widest text-muted-foreground mb-8 reveal">
                Our Graduates Work With Industry-Leading Teams
            </p>
        </div>
        <div class="marquee" style="--marquee-duration: 42s">
            <div class="marquee-track">
                @foreach ($employers->concat($employers) as $emp)
                    <div class="shrink-0 flex items-center px-8 py-4 rounded-xl bg-gray-50 border border-border/70">
                        @if ($emp->logo)
                            <img src="{{ media_url($emp->logo) }}" alt="{{ $emp->name }}" loading="lazy" decoding="async" class="h-8 w-auto object-contain grayscale hover:grayscale-0 transition">
                        @else
                            <span class="font-bold text-gray-500 whitespace-nowrap">{{ $emp->name }}</span>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endif
