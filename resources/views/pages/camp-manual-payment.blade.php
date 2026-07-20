<x-layouts.app title="Complete Your Camp Registration">
    <section class="py-16 bg-white">
        <div class="container-custom">
            <div class="max-w-xl mx-auto">
                <div class="rounded-2xl border border-border bg-white shadow-sm p-8 md:p-10">
                    <div class="text-center mb-8">
                        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-primary/10 text-primary mb-4">
                            <x-lucide name="Landmark" class="w-8 h-8"/>
                        </div>
                        <h1 class="text-2xl md:text-3xl mb-3">Almost there, {{ \Illuminate\Support\Str::before($registration->full_name, ' ') }}!</h1>
                        <p class="text-muted-foreground">Your spot for the <strong>{{ $registration->track }}</strong> track is reserved. Transfer the fee below and send your proof of payment to confirm.</p>
                    </div>

                    <div class="rounded-lg border border-primary/20 bg-primary/5 p-6 divide-y divide-primary/10">
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
                            <span class="font-bold text-primary text-lg text-right">{{ $registration->amount > 0 ? '₦'.number_format($registration->amount) : 'Free' }}</span>
                        </div>
                        <div class="flex items-center justify-between gap-4 py-3 last:pb-0">
                            <span class="text-sm text-muted-foreground shrink-0">Reference</span>
                            <span class="font-semibold text-gray-900 text-right break-all">{{ $registration->full_name }} ({{ $registration->email }})</span>
                        </div>
                    </div>

                    <p class="text-sm text-muted-foreground italic mt-4 text-center">Please use your name as the transfer narration, then send your receipt to us.</p>

                    <div class="mt-6 flex flex-wrap justify-center gap-3">
                        <a href="https://wa.me/{{ setting('whatsapp_number') }}" target="_blank" rel="noopener" class="inline-flex items-center gap-2 rounded-lg bg-[#25D366] px-5 py-3 font-medium text-white transition-colors hover:bg-[#1ebe5b]">
                            <x-social-icon network="whatsapp" class="w-4 h-4"/> Send Proof on WhatsApp
                        </a>
                        <a href="mailto:{{ setting('contact_email') }}" class="inline-flex items-center gap-2 rounded-lg border border-primary px-5 py-3 font-medium text-primary transition-colors hover:bg-primary/5">
                            <x-lucide name="Mail" class="w-4 h-4"/> Email Us
                        </a>
                    </div>

                    <div class="mt-8 text-center">
                        <a href="{{ route('camp.index') }}" class="text-sm text-muted-foreground hover:text-primary">← Back to the camp</a>
                    </div>
                </div>
            </div>
        </div>
    </section>
</x-layouts.app>
