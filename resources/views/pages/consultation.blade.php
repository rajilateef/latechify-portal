<x-layouts.app title="Schedule a Free Consultation">
    <section class="py-16 bg-muted/10">
        <div class="container-custom">
            {{-- Page header --}}
            <div class="text-center max-w-2xl mx-auto mb-12">
                <h1 class="mb-4">Schedule a <span class="gradient-text">Free Consultation</span></h1>
                <p class="text-lg text-muted-foreground">Book a time with our experts to discuss your project, needs, or questions. We're here to help you navigate your digital journey.</p>
            </div>

            <div class="grid md:grid-cols-2 gap-8">
                {{-- LEFT: Booking form --}}
                <div class="rounded-xl border border-border bg-white shadow-sm hover:shadow-lg transition-shadow p-8">
                    <h2 class="text-2xl mb-6">Book Your Session</h2>
                    <livewire:consultation-form />
                </div>

                {{-- RIGHT: Sidebar --}}
                <div class="space-y-8">
                    {{-- What to Expect --}}
                    <div class="rounded-xl border border-primary/10 bg-primary/5 p-8">
                        <h2 class="text-2xl mb-6">What to Expect</h2>
                        <ul class="space-y-6">
                            <li class="flex gap-4">
                                <div class="shrink-0 flex items-center justify-center w-8 h-8 rounded-full bg-primary text-white font-semibold text-sm">1</div>
                                <div>
                                    <h3 class="text-base font-semibold text-gray-900">Initial Discussion</h3>
                                    <p class="text-muted-foreground text-sm mt-1">We'll learn about your business, goals, and challenges.</p>
                                </div>
                            </li>
                            <li class="flex gap-4">
                                <div class="shrink-0 flex items-center justify-center w-8 h-8 rounded-full bg-primary text-white font-semibold text-sm">2</div>
                                <div>
                                    <h3 class="text-base font-semibold text-gray-900">Expert Recommendations</h3>
                                    <p class="text-muted-foreground text-sm mt-1">Our team will provide tailored solutions and approaches for your needs.</p>
                                </div>
                            </li>
                            <li class="flex gap-4">
                                <div class="shrink-0 flex items-center justify-center w-8 h-8 rounded-full bg-primary text-white font-semibold text-sm">3</div>
                                <div>
                                    <h3 class="text-base font-semibold text-gray-900">Action Plan</h3>
                                    <p class="text-muted-foreground text-sm mt-1">You'll receive a clear path forward with options and next steps.</p>
                                </div>
                            </li>
                        </ul>
                    </div>

                    {{-- FAQ --}}
                    <div class="rounded-xl border border-primary/10 bg-primary/5 p-8">
                        <h2 class="text-2xl mb-6">FAQ</h2>
                        <div class="space-y-5">
                            <div>
                                <h3 class="text-base font-semibold text-gray-900">How long is the consultation?</h3>
                                <p class="text-muted-foreground text-sm mt-1">Our initial consultations typically last 30-45 minutes.</p>
                            </div>
                            <div>
                                <h3 class="text-base font-semibold text-gray-900">Is there any obligation after the consultation?</h3>
                                <p class="text-muted-foreground text-sm mt-1">None at all. The consultation is completely free with no strings attached.</p>
                            </div>
                            <div>
                                <h3 class="text-base font-semibold text-gray-900">How do we meet?</h3>
                                <p class="text-muted-foreground text-sm mt-1">Consultations are conducted via Zoom. You'll receive a link after booking.</p>
                            </div>
                        </div>
                    </div>

                    {{-- Immediate assistance --}}
                    <div class="rounded-xl bg-primary text-white shadow-sm p-8">
                        <h3 class="text-xl font-bold mb-2">Need Immediate Assistance?</h3>
                        <p class="text-white/80 mb-6">Our support team is available during business hours:</p>
                        <div class="space-y-3">
                            <a href="tel:{{ setting('contact_phone') }}" class="flex items-center gap-3 hover:text-primary-0 transition-colors">
                                <x-lucide name="Phone" class="w-5 h-5"/>
                                <span>{{ setting('contact_phone') }}</span>
                            </a>
                            <a href="mailto:{{ setting('contact_email') }}" class="flex items-center gap-3 hover:text-primary-0 transition-colors">
                                <x-lucide name="Mail" class="w-5 h-5"/>
                                <span class="break-all">{{ setting('contact_email') }}</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</x-layouts.app>
