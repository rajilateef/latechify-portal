@php
    $highlights = collect(preg_split('/\r\n|\r|\n/', (string) setting('camp_highlights')))
        ->map(fn ($l) => trim($l))->filter()->values();
    $feePhysical = (int) setting('camp_fee_physical', 0);
    $feeVirtual  = (int) setting('camp_fee_virtual', 0);
    $campImage = media_url(setting('camp_image'), 'assets/imgs/session.jpg');
@endphp

<x-layouts.app :title="setting('camp_title', 'Summer Coding Camp')" :metaDescription="setting('camp_subtitle')">
    {{-- Hero --}}
    <section class="relative overflow-hidden bg-primary text-white">
        <div class="absolute inset-0 dot-pattern-white opacity-[0.06]"></div>
        <div class="container-custom relative py-16 md:py-24">
            <div class="grid lg:grid-cols-2 gap-10 lg:gap-14 items-center">
                <div class="reveal-fade">
                    <span class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1.5 text-xs font-semibold uppercase tracking-wide">
                        <x-lucide name="Sun" class="w-4 h-4 text-primary-0"/> {{ setting('camp_badge', 'Registration Open') }}
                    </span>
                    <h1 class="mt-5 leading-tight">{{ setting('camp_title', 'Summer Coding Camp') }}</h1>
                    <p class="mt-5 text-lg text-white/80">{{ setting('camp_subtitle', 'A hands-on holiday coding experience where learners build real projects, make friends, and discover the joy of technology.') }}</p>

                    <div class="mt-8 flex flex-wrap gap-x-8 gap-y-4 text-sm">
                        @if (setting('camp_dates'))
                            <div class="flex items-center gap-2"><x-lucide name="CalendarDays" class="w-5 h-5 text-primary-0"/> <span class="text-white/90">{{ setting('camp_dates') }}</span></div>
                        @endif
                        @if (setting('camp_duration'))
                            <div class="flex items-center gap-2"><x-lucide name="Clock" class="w-5 h-5 text-primary-0"/> <span class="text-white/90">{{ setting('camp_duration') }}</span></div>
                        @endif
                        @if (setting('camp_location'))
                            <div class="flex items-center gap-2"><x-lucide name="MapPin" class="w-5 h-5 text-primary-0"/> <span class="text-white/90">{{ setting('camp_location') }}</span></div>
                        @endif
                    </div>

                    @if ($feePhysical > 0 || $feeVirtual > 0)
                        <div class="mt-8 flex flex-wrap items-center gap-3 text-sm">
                            <span class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3.5 py-1.5">
                                <x-lucide name="Building2" class="w-4 h-4 text-primary-0"/> Physical <span class="font-bold text-white">₦{{ number_format($feePhysical) }}</span>
                            </span>
                            <span class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3.5 py-1.5">
                                <x-lucide name="Monitor" class="w-4 h-4 text-primary-0"/> Virtual <span class="font-bold text-white">₦{{ number_format($feeVirtual) }}</span>
                            </span>
                        </div>
                    @endif

                    <div class="mt-8">
                        <a href="#register" class="btn-shine inline-flex items-center gap-2 rounded-lg bg-white px-7 py-3.5 font-semibold text-primary transition-transform hover:-translate-y-0.5">
                            Register Now <x-lucide name="ArrowRight" class="w-4 h-4"/>
                        </a>
                    </div>
                </div>

                <div class="reveal-fade">
                    <div class="relative rounded-2xl overflow-hidden shadow-2xl aspect-[4/3]">
                        <img src="{{ $campImage }}" alt="{{ setting('camp_title', 'Summer Coding Camp') }}" decoding="async" class="absolute inset-0 w-full h-full object-cover">
                    </div>
                </div>
            </div>
        </div>
        <div class="absolute bottom-0 inset-x-0 h-16 bg-gradient-to-t from-white to-transparent"></div>
    </section>

    {{-- What's included --}}
    @if ($highlights->isNotEmpty())
        <section class="py-16 md:py-20 bg-white">
            <div class="container-custom">
                <div class="max-w-2xl mx-auto text-center mb-12 reveal-fade">
                    <span class="section-eyebrow">What's Included</span>
                    <h2 class="mt-3 text-gray-900">Everything Your Camper Needs to <span class="text-primary">Thrive</span></h2>
                </div>
                <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($highlights as $i => $highlight)
                        <div class="flex items-start gap-3 rounded-xl border border-border bg-card p-5 shadow-sm card-lift reveal-fade" data-reveal-delay="{{ ($i % 3) * 90 }}">
                            <div class="shrink-0 inline-flex items-center justify-center w-10 h-10 rounded-lg bg-primary/10 text-primary"><x-lucide name="Check" class="w-5 h-5"/></div>
                            <p class="text-gray-700 leading-relaxed">{{ $highlight }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- Registration --}}
    <section id="register" class="py-16 md:py-24 bg-muted/30">
        <div class="container-custom">
            <div class="max-w-3xl mx-auto">
                <div class="text-center mb-10 reveal-fade">
                    <span class="section-eyebrow">Register</span>
                    <h2 class="mt-3 text-gray-900">Reserve Your <span class="text-primary">Spot</span></h2>
                    <p class="text-muted-foreground mt-3">Fill in the details below and choose how you'd like to pay — online for instant confirmation, or by bank transfer.</p>
                </div>

                <div class="rounded-2xl border border-border bg-white shadow-sm p-6 md:p-8">
                    <livewire:camp-registration-form />
                </div>
            </div>
        </div>
    </section>
</x-layouts.app>
