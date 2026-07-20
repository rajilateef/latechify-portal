<x-layouts.app title="Our Services">
    <section class="py-20">
        <div class="container-custom">
            {{-- Page header --}}
            <div class="text-center max-w-2xl mx-auto mb-12">
                <h1 class="mb-4">Our <span class="gradient-text">Services</span></h1>
                <p class="text-muted-foreground text-lg">Comprehensive digital solutions and training programs designed to transform your technology career and business</p>
            </div>

            {{-- Services grid --}}
            <div class="max-w-6xl mx-auto grid grid-cols-1 md:grid-cols-2 gap-8">
                @foreach ($services as $service)
                    <div id="{{ $service->slug }}" class="rounded-xl border border-border bg-white shadow-sm hover:shadow-lg transition-shadow p-8 scroll-mt-28">
                        <div class="flex items-center justify-center w-12 h-12 rounded-lg bg-brand-purple/10 text-brand-purple mb-5">
                            <x-lucide :name="$service->icon" class="w-6 h-6"/>
                        </div>
                        <h3 class="text-xl font-semibold mb-2">{{ $service->title }}</h3>
                        <p class="text-muted-foreground">{{ $service->description }}</p>
                    </div>
                @endforeach
            </div>

            {{-- CTA band --}}
            <div class="max-w-6xl mx-auto mt-16">
                <div class="rounded-2xl bg-primary text-white relative overflow-hidden p-10 lg:p-14 text-center">
                    <div class="absolute inset-0 dot-pattern-white opacity-[0.07]"></div>
                    <div class="relative">
                        <h2 class="mb-4">Need a Tailored Solution?</h2>
                        <p class="text-white/80 text-lg mb-8 max-w-2xl mx-auto">Let's build something that fits your goals. Reach out and our team will craft a service package around your needs.</p>
                        <a href="{{ route('contact') }}" class="inline-flex items-center gap-2 bg-white text-primary hover:bg-white/90 px-6 py-3 rounded-lg font-medium transition-colors">
                            Request a Custom Service <x-lucide name="ArrowRight" class="w-4 h-4"/>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>
</x-layouts.app>
