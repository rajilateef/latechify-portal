@if ($sectionAdverts->isNotEmpty())
    <section class="py-16 bg-white">
        <div class="container-custom">
            <div class="text-center max-w-2xl mx-auto mb-10 reveal">
                <span class="inline-block bg-primary-icon/10 text-primary-icon text-xs font-semibold uppercase tracking-wide px-3 py-1.5 rounded-full mb-3">Promotions</span>
                <h2 class="mb-3">{{ setting('promo_heading', 'Latest Promotions') }}</h2>
                <p class="text-muted-foreground">{{ setting('promo_sub') }}</p>
            </div>

            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($sectionAdverts as $advert)
                    <a href="{{ $advert->link_url ? route('advert.click', $advert) : '#' }}"
                       @if($advert->link_url && str_starts_with($advert->link_url, 'http')) target="_blank" rel="noopener" @endif
                       data-reveal-delay="{{ $loop->index * 100 }}"
                       class="reveal group block rounded-2xl overflow-hidden border border-border bg-card shadow-sm card-lift">
                        <div class="relative aspect-[16/10] overflow-hidden">
                            <img src="{{ media_url($advert->image) }}" alt="{{ $advert->title }}" loading="lazy" decoding="async"
                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                        </div>
                        <div class="p-5">
                            <h3 class="text-lg font-bold text-gray-900 group-hover:text-primary transition-colors">{{ $advert->title }}</h3>
                            @if ($advert->description)
                                <p class="text-muted-foreground text-sm mt-2">{{ $advert->description }}</p>
                            @endif
                            @if ($advert->link_url)
                                <span class="inline-flex items-center gap-1.5 text-primary text-sm font-medium mt-4">
                                    Learn more <x-lucide name="ArrowRight" class="w-4 h-4 group-hover:translate-x-1 transition-transform"/>
                                </span>
                            @endif
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    </section>
@endif
