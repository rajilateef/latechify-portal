<section class="py-20 bg-primary relative overflow-hidden">
    <div class="absolute inset-0 dot-pattern-white opacity-[0.07]"></div>
    <div class="container-custom relative">
        <div class="grid lg:grid-cols-2 gap-12 items-center">
            <div class="text-white reveal-left">
                <h2 class="mb-4">{{ setting('contact_cta_heading', 'Ready to Start Your Tech Journey?') }}</h2>
                <p class="text-white/80 text-lg mb-8">{{ setting('contact_cta_sub', "Join our community of learners and professionals. Enrol in a course, request a custom digital solution, or simply reach out — we're here to help.") }}</p>
                <div class="flex flex-wrap gap-4">
                    <a href="{{ route('apply') }}" class="inline-flex items-center gap-2 bg-white text-primary hover:bg-white/90 px-7 py-3.5 rounded-lg font-medium transition-colors">Enrol Today <x-lucide name="ArrowRight" class="w-4 h-4"/></a>
                    <a href="{{ route('contact') }}" class="inline-flex items-center gap-2 border border-white/40 text-white hover:bg-white/10 px-7 py-3.5 rounded-lg font-medium transition-colors">Contact Us</a>
                </div>
            </div>

            <div class="grid sm:grid-cols-3 gap-4 reveal-right">
                <a href="tel:{{ setting('contact_phone') }}" class="bg-white/10 hover:bg-white/15 rounded-xl p-5 text-white text-center transition-colors">
                    <x-lucide name="Phone" class="w-7 h-7 mx-auto mb-3 text-primary-0"/>
                    <div class="text-sm text-white/70">Call us</div>
                    <div class="font-medium text-sm mt-1">{{ setting('contact_phone') }}</div>
                </a>
                <a href="mailto:{{ setting('contact_email') }}" class="bg-white/10 hover:bg-white/15 rounded-xl p-5 text-white text-center transition-colors">
                    <x-lucide name="Mail" class="w-7 h-7 mx-auto mb-3 text-primary-0"/>
                    <div class="text-sm text-white/70">Email us</div>
                    <div class="font-medium text-xs mt-1 break-all">{{ setting('contact_email') }}</div>
                </a>
                <a href="https://wa.me/{{ setting('whatsapp_number') }}" target="_blank" rel="noopener" class="bg-white/10 hover:bg-white/15 rounded-xl p-5 text-white text-center transition-colors">
                    <x-lucide name="MessageCircle" class="w-7 h-7 mx-auto mb-3 text-primary-0"/>
                    <div class="text-sm text-white/70">WhatsApp</div>
                    <div class="font-medium text-sm mt-1">Chat now</div>
                </a>
            </div>
        </div>
    </div>
</section>
