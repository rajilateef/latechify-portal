<x-layouts.app title="Contact Us">
    {{-- HERO --}}
    <section class="py-16 bg-gradient-to-br from-primary/5 to-primary-0/10">
        <div class="container-custom">
            <div class="text-center max-w-2xl mx-auto">
                <h1 class="mb-4">Get In <span class="gradient-text">Touch</span></h1>
                <p class="text-lg text-muted-foreground">Have questions or want to learn more about our services? We're here to help. Reach out to us and our team will get back to you shortly.</p>
            </div>
        </div>
    </section>

    {{-- MAIN --}}
    <section class="py-16 bg-white">
        <div class="container-custom">
            <div class="grid lg:grid-cols-3 gap-8">
                {{-- LEFT: Contact information + Follow us --}}
                <div class="lg:col-span-1 space-y-8">
                    <div class="rounded-xl border border-border bg-white shadow-sm hover:shadow-lg transition-shadow p-8">
                        <h2 class="text-2xl mb-6">Contact Information</h2>
                        <ul class="space-y-6">
                            <li class="flex gap-4">
                                <div class="shrink-0 p-3 rounded-lg bg-primary/10 text-primary"><x-lucide name="Mail" class="w-5 h-5"/></div>
                                <div>
                                    <h3 class="text-base font-semibold text-gray-900">Email Us</h3>
                                    <a href="mailto:{{ setting('contact_email') }}" class="text-muted-foreground hover:text-primary transition-colors break-all">{{ setting('contact_email') }}</a>
                                </div>
                            </li>
                            <li class="flex gap-4">
                                <div class="shrink-0 p-3 rounded-lg bg-primary/10 text-primary"><x-lucide name="Phone" class="w-5 h-5"/></div>
                                <div>
                                    <h3 class="text-base font-semibold text-gray-900">Call Us</h3>
                                    <a href="tel:{{ setting('contact_phone') }}" class="text-muted-foreground hover:text-primary transition-colors">{{ setting('contact_phone') }}</a>
                                </div>
                            </li>
                            <li class="flex gap-4">
                                <div class="shrink-0 p-3 rounded-lg bg-primary/10 text-primary"><x-lucide name="MapPin" class="w-5 h-5"/></div>
                                <div>
                                    <h3 class="text-base font-semibold text-gray-900">Visit Our Office</h3>
                                    <p class="text-muted-foreground">{{ setting('contact_address') }}</p>
                                </div>
                            </li>
                            <li class="flex gap-4">
                                <div class="shrink-0 p-3 rounded-lg bg-primary/10 text-primary"><x-lucide name="Clock" class="w-5 h-5"/></div>
                                <div>
                                    <h3 class="text-base font-semibold text-gray-900">Business Hours</h3>
                                    <div class="text-muted-foreground">
                                        @foreach (array_filter(array_map('trim', explode('|', setting('business_hours', '')))) as $hours)
                                            <p>{{ $hours }}</p>
                                        @endforeach
                                    </div>
                                </div>
                            </li>
                        </ul>
                    </div>

                    {{-- Follow us --}}
                    <div class="rounded-xl border border-border bg-white shadow-sm hover:shadow-lg transition-shadow p-8">
                        <h2 class="text-2xl mb-2">Follow Us</h2>
                        <p class="text-muted-foreground mb-6">Connect with us on social media for updates and tech insights.</p>
                        <div class="flex flex-wrap gap-3">
                            @if (setting('instagram_url'))
                                <a href="{{ setting('instagram_url') }}" target="_blank" rel="noopener" aria-label="Instagram" class="inline-flex items-center justify-center w-11 h-11 rounded-lg bg-primary/10 text-primary hover:bg-primary hover:text-white transition-colors"><x-lucide name="Instagram" class="w-5 h-5"/></a>
                            @endif
                            @if (setting('facebook_url'))
                                <a href="{{ setting('facebook_url') }}" target="_blank" rel="noopener" aria-label="Facebook" class="inline-flex items-center justify-center w-11 h-11 rounded-lg bg-primary/10 text-primary hover:bg-primary hover:text-white transition-colors"><x-lucide name="Facebook" class="w-5 h-5"/></a>
                            @endif
                            @if (setting('twitter_url'))
                                <a href="{{ setting('twitter_url') }}" target="_blank" rel="noopener" aria-label="Twitter" class="inline-flex items-center justify-center w-11 h-11 rounded-lg bg-primary/10 text-primary hover:bg-primary hover:text-white transition-colors"><x-lucide name="Twitter" class="w-5 h-5"/></a>
                            @endif
                            @if (setting('linkedin_url'))
                                <a href="{{ setting('linkedin_url') }}" target="_blank" rel="noopener" aria-label="LinkedIn" class="inline-flex items-center justify-center w-11 h-11 rounded-lg bg-primary/10 text-primary hover:bg-primary hover:text-white transition-colors"><x-lucide name="Linkedin" class="w-5 h-5"/></a>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- RIGHT: Contact form --}}
                <div class="lg:col-span-2">
                    <div class="rounded-xl border border-border bg-white shadow-sm hover:shadow-lg transition-shadow p-8">
                        <h2 class="text-2xl mb-6">Send Us a Message</h2>
                        <livewire:contact-form />
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- MAP --}}
    <section class="py-16 bg-gradient-to-r from-blue-50 to-indigo-50">
        <div class="container-custom">
            <div class="text-center max-w-2xl mx-auto mb-10">
                <h2 class="mb-4">Our <span class="gradient-text">Location</span></h2>
                <p class="text-muted-foreground">Planning to visit us? We'd love to welcome you to our office.</p>
            </div>
            <div class="rounded-xl overflow-hidden border border-border shadow-sm h-[400px]">
                <iframe src="{{ setting('map_embed_url') }}" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen title="Latechify office location" class="w-full h-full border-0"></iframe>
            </div>
            @if (setting('directions_url'))
                <div class="text-center mt-8">
                    <a href="{{ setting('directions_url') }}" target="_blank" rel="noopener" class="inline-flex items-center gap-2 bg-primary hover:bg-primary/90 text-white px-6 py-3 rounded-lg font-medium transition-colors">
                        <x-lucide name="MapPin" class="w-5 h-5"/> Get Directions
                    </a>
                </div>
            @endif
        </div>
    </section>

    {{-- FAQ --}}
    <section class="py-16 bg-white">
        <div class="container-custom">
            <div class="text-center max-w-2xl mx-auto mb-10">
                <h2 class="mb-4">Frequently Asked <span class="gradient-text">Questions</span></h2>
            </div>
            <div class="max-w-3xl mx-auto">
                <x-faq-accordion :faqs="$faqs" />
            </div>
        </div>
    </section>
</x-layouts.app>
