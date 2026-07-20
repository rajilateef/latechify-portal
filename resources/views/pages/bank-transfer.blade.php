<x-layouts.app title="Complete Your Payment">
    <section class="py-16 bg-white">
        <div class="container-custom">
            <div class="max-w-xl mx-auto">
                <div class="rounded-xl border border-border bg-white shadow-sm p-8 md:p-10">
                    {{-- Header --}}
                    <div class="text-center mb-8">
                        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-primary/10 text-primary mb-4">
                            <x-lucide name="Landmark" class="w-8 h-8"/>
                        </div>
                        <h1 class="text-2xl md:text-3xl mb-3">Complete Your Payment</h1>
                        <p class="text-muted-foreground">Please transfer the exact amount below to secure your enrollment, then send your proof of payment to us.</p>
                    </div>

                    {{-- Details box --}}
                    <div class="bg-primary/5 rounded-lg border border-primary/20 p-6 divide-y divide-primary/10">
                        <div class="flex items-center justify-between gap-4 py-3 first:pt-0">
                            <span class="text-sm text-muted-foreground">Bank Name</span>
                            <span class="font-semibold text-gray-900 text-right">{{ setting('bank_name') }}</span>
                        </div>
                        <div class="flex items-center justify-between gap-4 py-3">
                            <span class="text-sm text-muted-foreground">Account Name</span>
                            <span class="font-semibold text-gray-900 text-right">{{ setting('bank_account_name') }}</span>
                        </div>
                        <div class="flex items-center justify-between gap-4 py-3" x-data="{ copied: false }">
                            <span class="text-sm text-muted-foreground">Account Number</span>
                            <button type="button"
                                    @click="navigator.clipboard.writeText('{{ setting('bank_account_number') }}'); copied = true; setTimeout(() => copied = false, 1500)"
                                    class="inline-flex items-center gap-2 font-bold text-gray-900 hover:text-primary transition-colors">
                                <span class="font-mono tracking-wide">{{ setting('bank_account_number') }}</span>
                                <x-lucide name="Copy" class="w-4 h-4" x-show="!copied"/>
                                <x-lucide name="Check" class="w-4 h-4 text-green-600" x-show="copied" x-cloak/>
                            </button>
                        </div>
                        <div class="flex items-center justify-between gap-4 py-3">
                            <span class="text-sm text-muted-foreground">Amount</span>
                            <span class="font-bold text-primary text-lg text-right">₦{{ number_format($application->price) }}</span>
                        </div>
                        <div class="flex items-center justify-between gap-4 py-3 last:pb-0">
                            <span class="text-sm text-muted-foreground shrink-0">Reference</span>
                            <span class="font-semibold text-gray-900 text-right break-all">{{ $application->full_name }} ({{ $application->email }})</span>
                        </div>
                    </div>

                    <p class="text-sm text-muted-foreground italic mt-4 text-center">Please include your name and email as the transfer reference.</p>

                    <p class="text-sm text-muted-foreground mt-6 text-center">
                        Questions? Contact us at
                        <a href="mailto:{{ setting('contact_email') }}" class="text-primary font-medium hover:underline">{{ setting('contact_email') }}</a>
                        or
                        <a href="https://wa.me/{{ setting('whatsapp_number') }}" target="_blank" rel="noopener" class="text-primary font-medium hover:underline">WhatsApp</a>.
                    </p>

                    {{-- Actions --}}
                    <div class="flex flex-wrap justify-center gap-4 mt-8">
                        <a href="{{ route('home') }}" class="inline-flex items-center gap-2 border border-primary text-primary hover:bg-primary/5 px-6 py-3 rounded-lg font-medium transition-colors">
                            Return to Home
                        </a>
                        <a href="{{ route('courses.index') }}" class="inline-flex items-center gap-2 bg-primary hover:bg-primary/90 text-white px-6 py-3 rounded-lg font-medium transition-colors">
                            Browse More Courses <x-lucide name="ArrowRight" class="w-4 h-4"/>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>
</x-layouts.app>
