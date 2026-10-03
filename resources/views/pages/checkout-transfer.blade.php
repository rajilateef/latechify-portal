<x-layouts.app title="Complete your enrolment">
    <section class="py-16 bg-gray-50">
        <div class="container-custom">
            <div class="max-w-xl mx-auto">
                @if (session('notice'))
                    {{-- e.g. the gateway was unreachable and we fell back to transfer --}}
                    <div class="mb-5 flex items-start gap-2.5 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                        <x-lucide name="Info" class="w-4 h-4 mt-0.5 shrink-0"/>
                        <span>{{ session('notice') }}</span>
                    </div>
                @endif

                <div class="rounded-2xl border border-border bg-white shadow-sm p-8 md:p-10">
                    <div class="text-center mb-8">
                        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-primary/10 text-primary mb-4">
                            <x-lucide name="Landmark" class="w-8 h-8"/>
                        </div>
                        <h1 class="text-2xl md:text-3xl mb-3">Almost there, {{ \Illuminate\Support\Str::before($order->full_name, ' ') }}!</h1>
                        <p class="text-muted-foreground">
                            Your place on <strong>{{ $order->itemName() }}</strong> is reserved. Transfer the fee below and send
                            your proof of payment — we'll confirm it and open your portal.
                        </p>
                    </div>

                    <div class="rounded-lg border border-primary/20 bg-primary/5 p-6 divide-y divide-primary/10">
                        <div class="flex items-center justify-between gap-4 py-3 first:pt-0">
                            <span class="text-sm text-muted-foreground">Bank name</span>
                            <span class="font-semibold text-gray-900 text-right">{{ setting('bank_name') }}</span>
                        </div>
                        <div class="flex items-center justify-between gap-4 py-3">
                            <span class="text-sm text-muted-foreground">Account name</span>
                            <span class="font-semibold text-gray-900 text-right">{{ setting('bank_account_name') }}</span>
                        </div>
                        <div class="flex items-center justify-between gap-4 py-3" x-data="{ copied: false }">
                            <span class="text-sm text-muted-foreground">Account number</span>
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
                            <span class="font-bold text-primary text-lg text-right">{{ $order->amount > 0 ? '₦'.number_format($order->amount) : 'Free' }}</span>
                        </div>
                        <div class="flex items-start justify-between gap-4 py-3 last:pb-0" x-data="{ copied: false }">
                            <span class="text-sm text-muted-foreground shrink-0">Use this reference</span>
                            <button type="button"
                                    @click="navigator.clipboard.writeText('{{ $order->payment_reference }}'); copied = true; setTimeout(() => copied = false, 1500)"
                                    class="inline-flex items-start gap-2 font-semibold text-gray-900 hover:text-primary transition-colors text-right">
                                <span class="font-mono text-sm break-all">{{ $order->payment_reference }}</span>
                                <x-lucide name="Copy" class="w-4 h-4 shrink-0 mt-0.5" x-show="!copied"/>
                                <x-lucide name="Check" class="w-4 h-4 text-green-600 shrink-0 mt-0.5" x-show="copied" x-cloak/>
                            </button>
                        </div>
                    </div>

                    <div class="mt-6 rounded-xl border border-amber-200 bg-amber-50 p-4">
                        <div class="flex items-start gap-2.5">
                            <x-lucide name="Info" class="w-4 h-4 text-amber-600 mt-0.5 shrink-0"/>
                            <p class="text-sm text-amber-800">
                                Quote the reference above on your transfer so we can match it instantly. Once our team confirms the
                                payment, your portal login is emailed to <strong>{{ $order->email }}</strong>.
                            </p>
                        </div>
                    </div>

                    <div class="mt-7 flex flex-wrap gap-3">
                        @if (setting('whatsapp_number'))
                            <a href="https://wa.me/{{ preg_replace('/\D/', '', setting('whatsapp_number')) }}?text={{ urlencode('Hi Latechify, I have paid for '.$order->itemName().'. Reference: '.$order->payment_reference) }}"
                               target="_blank" rel="noopener"
                               class="flex-1 inline-flex items-center justify-center gap-2 bg-primary hover:bg-primary/90 text-white px-6 py-3 rounded-lg font-medium transition-colors">
                                <x-lucide name="MessageCircle" class="w-4 h-4"/> Send proof of payment
                            </a>
                        @else
                            <a href="{{ route('contact') }}" class="flex-1 inline-flex items-center justify-center gap-2 bg-primary hover:bg-primary/90 text-white px-6 py-3 rounded-lg font-medium transition-colors">
                                <x-lucide name="Mail" class="w-4 h-4"/> Send proof of payment
                            </a>
                        @endif
                        <a href="{{ route('checkout.status', $order) }}" class="inline-flex items-center justify-center gap-2 border border-border px-6 py-3 rounded-lg font-medium text-gray-700 hover:bg-gray-50 transition-colors">
                            <x-lucide name="RefreshCw" class="w-4 h-4"/> Check status
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>
</x-layouts.app>
