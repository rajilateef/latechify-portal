<section class="py-20 bg-white">
    <div class="container-custom">
        <div class="text-center max-w-2xl mx-auto mb-16 reveal">
            <h2 class="mb-4">{{ setting('faq_heading', 'Frequently Asked Questions') }}</h2>
            <p class="text-muted-foreground">{{ setting('faq_sub', 'Find answers to the most common questions about our courses and services') }}</p>
        </div>
        <div class="max-w-3xl mx-auto reveal">
            <x-faq-accordion :faqs="$faqs" />
        </div>
    </div>
</section>
