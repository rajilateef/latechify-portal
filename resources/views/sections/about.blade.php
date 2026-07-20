<section class="py-20 bg-white">
    <div class="container-custom">
        <div class="grid md:grid-cols-2 gap-12 items-center">
            <div class="relative reveal-left">
                <div class="rounded-lg overflow-hidden shadow-xl relative z-10">
                    <img src="{{ media_url(setting('about_image'), 'assets/imgs/banner.jpg') }}" alt="{{ setting('site_name') }}" loading="lazy" decoding="async" class="w-full h-full object-cover">
                </div>
                <div class="absolute -bottom-6 -right-6 w-40 h-40 bg-primary rounded-full opacity-20 z-0"></div>
            </div>

            <div class="reveal-right">
                <h2 class="mb-6">{{ setting('about_heading', 'About Latechify') }}</h2>
                <p class="text-lg text-muted-foreground mb-6">{{ setting('about_body_1') }}</p>
                <p class="text-lg text-muted-foreground mb-8">{{ setting('about_body_2') }}</p>
                <div class="flex flex-wrap gap-4">
                    <a href="{{ route('about') }}" class="inline-flex items-center gap-2 bg-primary hover:bg-primary/90 text-white px-6 py-3 rounded-lg font-medium transition-colors">
                        Learn More <x-lucide name="ArrowRight" class="w-4 h-4"/>
                    </a>
                    <a href="{{ route('contact') }}" class="inline-flex items-center gap-2 border border-primary text-primary hover:bg-primary/5 px-6 py-3 rounded-lg font-medium transition-colors">
                        Contact Us
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>
