@php
    $waNumber = setting('whatsapp_number');
    $accentBg = ['blue' => 'bg-sky-50', 'dark' => 'bg-navy', 'plain' => 'bg-gray-50'];
@endphp

<section id="services" class="py-20 bg-muted/30">
    <div class="container-custom">
        {{-- Masonry (CSS multi-column) — mirrors the staggered bento layout --}}
        <div class="lg:columns-3 gap-6">

            {{-- Intro block --}}
            <div class="break-inside-avoid mb-6 reveal">
                <span class="inline-flex items-center gap-2 text-sm font-semibold text-primary">
                    <span class="w-2.5 h-2.5 rounded-full bg-primary-0"></span> {{ setting('services_eyebrow', 'Services') }}
                </span>
                <h2 class="mt-4 text-2xl md:text-3xl font-bold text-gray-900 leading-snug">
                    {{ setting('services_statement', 'We provide end-to-end solutions to help your business launch, grow, and scale with confidence.') }}
                </h2>

                <a href="https://wa.me/{{ $waNumber }}" target="_blank" rel="noopener"
                   class="group mt-6 inline-flex items-center gap-3 bg-white border border-border rounded-full pl-5 pr-2 py-2 shadow-sm hover:shadow-md transition-all">
                    <span class="text-sm font-medium text-gray-800">Let's chat</span>
                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-[#25D366] text-white group-hover:scale-110 transition-transform">
                        <x-social-icon network="whatsapp" class="w-4 h-4"/>
                    </span>
                </a>
            </div>

            {{-- Service cards --}}
            @foreach ($services as $service)
                @php
                    $imgs     = is_array($service->images) ? array_values(array_filter($service->images)) : [];
                    $count    = count($imgs);
                    $bg       = $accentBg[$service->media_accent] ?? $accentBg['plain'];
                    $mediaTop = ($service->media_position ?? 'bottom') === 'top';
                @endphp

                <div class="break-inside-avoid mb-6 bg-card border border-border rounded-2xl p-5 shadow-sm card-lift reveal-fade">
                    @if ($mediaTop)
                        @include('sections.partials.service-media', ['imgs' => $imgs, 'count' => $count, 'bg' => $bg, 'service' => $service])
                        <div class="mt-5">
                            <h3 class="text-xl font-bold text-gray-900">{{ $service->title }}</h3>
                            <p class="text-muted-foreground text-sm leading-relaxed mt-2">{{ $service->description }}</p>
                        </div>
                    @else
                        <div class="mb-5">
                            <h3 class="text-xl font-bold text-gray-900">{{ $service->title }}</h3>
                            <p class="text-muted-foreground text-sm leading-relaxed mt-2">{{ $service->description }}</p>
                        </div>
                        @include('sections.partials.service-media', ['imgs' => $imgs, 'count' => $count, 'bg' => $bg, 'service' => $service])
                    @endif
                </div>
            @endforeach
        </div>
    </div>
</section>
