<x-layouts.app title="Checkout">
    <section class="py-14 md:py-20 bg-gray-50">
        <div class="container-custom">
            <div class="max-w-3xl mx-auto">
                <div class="text-center mb-10">
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-primary/10 px-3 py-1 text-xs font-semibold text-primary mb-4">
                        <x-lucide name="ShoppingCart" class="w-3.5 h-3.5"/> Checkout
                    </span>
                    <h1 class="text-3xl md:text-4xl mb-3">Secure your place</h1>
                    <p class="text-muted-foreground max-w-xl mx-auto">
                        Pick your program, pay online, and we'll set up your trainee portal as soon as the payment is confirmed.
                    </p>
                </div>

                {{-- How it works --}}
                <div class="grid sm:grid-cols-3 gap-3 mb-8">
                    @foreach ([
                        ['CreditCard', '1. Pay', 'Card or transfer via Monnify secure checkout.'],
                        ['BadgeCheck', '2. We confirm', 'Our team verifies your payment.'],
                        ['LayoutDashboard', '3. Portal opens', 'Login details land in your inbox.'],
                    ] as [$icon, $step, $desc])
                        <div class="rounded-xl border border-border bg-white p-4">
                            <x-lucide :name="$icon" class="w-5 h-5 text-primary mb-2"/>
                            <div class="font-semibold text-sm text-gray-900">{{ $step }}</div>
                            <p class="text-xs text-muted-foreground mt-0.5">{{ $desc }}</p>
                        </div>
                    @endforeach
                </div>

                <div class="rounded-2xl border border-border bg-white shadow-sm p-6 md:p-10">
                    @livewire('checkout-form', ['selectedSlug' => $selectedSlug, 'selectedFormat' => $format])
                </div>

                <p class="mt-6 text-center text-sm text-muted-foreground">
                    Already enrolled? <a href="{{ route('portal.login') }}" class="text-primary font-medium hover:underline">Sign in to your portal</a>
                </p>
            </div>
        </div>
    </section>
</x-layouts.app>
