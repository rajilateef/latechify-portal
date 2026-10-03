<x-layouts.app :title="$success ? 'Payment received' : 'Payment not confirmed'">
    <section class="py-16 md:py-24 bg-gray-50">
        <div class="container-custom">
            <div class="max-w-xl mx-auto rounded-2xl border border-border bg-white shadow-sm p-8 md:p-10 text-center">
                @if ($success)
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-emerald-100 text-emerald-600 mb-5">
                        <x-lucide name="CircleCheck" class="w-9 h-9"/>
                    </div>
                    <h1 class="text-2xl md:text-3xl mb-3">Payment received 🎉</h1>
                    <p class="text-muted-foreground">
                        Thank you{{ $order ? ', '.\Illuminate\Support\Str::before($order->full_name, ' ') : '' }} — we've got your payment for
                        <strong>{{ $order?->itemName() }}</strong>.
                    </p>

                    <div class="mt-6 rounded-xl border border-border divide-y divide-border text-left">
                        <div class="flex items-center justify-between gap-4 px-4 py-3">
                            <span class="text-sm text-muted-foreground">Amount paid</span>
                            <span class="font-bold text-gray-900">₦{{ number_format($order?->amount ?? 0) }}</span>
                        </div>
                        <div class="flex items-center justify-between gap-4 px-4 py-3">
                            <span class="text-sm text-muted-foreground shrink-0">Reference</span>
                            <span class="font-mono text-sm text-gray-900 break-all text-right">{{ $order?->payment_reference }}</span>
                        </div>
                        <div class="flex items-center justify-between gap-4 px-4 py-3">
                            <span class="text-sm text-muted-foreground">Status</span>
                            <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold
                                {{ $order?->isConfirmed() ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">
                                <x-lucide :name="$order?->isConfirmed() ? 'CircleCheck' : 'Clock'" class="w-3.5 h-3.5"/>
                                {{ $order?->statusLabel() }}
                            </span>
                        </div>
                    </div>

                    @if (! $order?->isConfirmed())
                        <div class="mt-6 rounded-xl border border-amber-200 bg-amber-50 p-4 text-left">
                            <div class="flex items-start gap-2.5">
                                <x-lucide name="Info" class="w-4 h-4 text-amber-600 mt-0.5 shrink-0"/>
                                <p class="text-sm text-amber-800">
                                    <strong>What happens next:</strong> our admin team reviews and confirms your payment, then your
                                    trainee portal is created automatically. Your login details are emailed to
                                    <strong>{{ $order?->email }}</strong> — usually within a few hours.
                                </p>
                            </div>
                        </div>
                    @endif

                    <div class="mt-7 flex flex-wrap justify-center gap-3">
                        <a href="{{ route('home') }}" class="inline-flex items-center gap-2 bg-primary hover:bg-primary/90 text-white px-6 py-3 rounded-lg font-medium transition-colors">
                            Back to home <x-lucide name="ArrowRight" class="w-4 h-4"/>
                        </a>
                        @if ($order)
                            <a href="{{ route('checkout.status', $order) }}" class="inline-flex items-center gap-2 border border-border px-6 py-3 rounded-lg font-medium text-gray-700 hover:bg-gray-50 transition-colors">
                                <x-lucide name="RefreshCw" class="w-4 h-4"/> Check status
                            </a>
                        @endif
                    </div>
                @else
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-red-100 text-red-600 mb-5">
                        <x-lucide name="CircleAlert" class="w-9 h-9"/>
                    </div>
                    <h1 class="text-2xl md:text-3xl mb-3">We couldn't confirm that payment</h1>
                    <p class="text-muted-foreground">
                        If you were debited, don't worry — confirmations can take a moment to reach us. Check the status again
                        shortly, or send us your reference and we'll sort it out.
                    </p>

                    @if ($order?->payment_reference)
                        <p class="mt-4 font-mono text-sm text-gray-900 break-all">{{ $order->payment_reference }}</p>
                    @endif

                    <div class="mt-7 flex flex-wrap justify-center gap-3">
                        @if ($order)
                            <a href="{{ route('checkout.status', $order) }}" class="inline-flex items-center gap-2 bg-primary hover:bg-primary/90 text-white px-6 py-3 rounded-lg font-medium transition-colors">
                                <x-lucide name="RefreshCw" class="w-4 h-4"/> Check again
                            </a>
                            <a href="{{ route('checkout.transfer', $order) }}" class="inline-flex items-center gap-2 border border-border px-6 py-3 rounded-lg font-medium text-gray-700 hover:bg-gray-50 transition-colors">
                                <x-lucide name="Landmark" class="w-4 h-4"/> Pay by transfer
                            </a>
                        @endif
                        <a href="{{ route('contact') }}" class="inline-flex items-center gap-2 border border-border px-6 py-3 rounded-lg font-medium text-gray-700 hover:bg-gray-50 transition-colors">
                            <x-lucide name="LifeBuoy" class="w-4 h-4"/> Contact support
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </section>
</x-layouts.app>
