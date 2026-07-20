<x-layouts.app :title="$success ? 'Registration Confirmed' : 'Payment Not Completed'">
    <section class="py-20 bg-white">
        <div class="container-custom">
            <div class="max-w-xl mx-auto">
                <div class="rounded-2xl border border-border bg-white shadow-sm p-8 md:p-10 text-center">
                    @if ($success)
                        <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-green-100 text-green-600 mb-6">
                            <x-lucide name="PartyPopper" class="w-10 h-10"/>
                        </div>
                        <h1 class="text-2xl md:text-3xl mb-4">You're in! 🎉</h1>
                        <p class="text-muted-foreground mb-8">Your payment is confirmed and your spot in the Summer Coding Camp is secured@if($registration) , {{ \Illuminate\Support\Str::before($registration->full_name, ' ') }}@endif. We've emailed the details and will be in touch soon.</p>
                        <a href="{{ route('home') }}" class="inline-flex items-center gap-2 rounded-lg bg-primary px-6 py-3 font-medium text-white transition-colors hover:bg-primary-100">
                            <x-lucide name="Home" class="w-5 h-5"/> Return to Home
                        </a>
                    @else
                        <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-red-100 text-red-600 mb-6">
                            <x-lucide name="X" class="w-10 h-10"/>
                        </div>
                        <h1 class="text-2xl md:text-3xl mb-4">Payment Not Completed</h1>
                        <p class="text-muted-foreground mb-8">We couldn't confirm your payment. If you were charged, contact us and we'll sort it out — otherwise you can try again or pay by bank transfer.</p>
                        <div class="flex flex-wrap justify-center gap-4">
                            <a href="{{ route('camp.index') }}#register" class="inline-flex items-center gap-2 rounded-lg bg-primary px-6 py-3 font-medium text-white transition-colors hover:bg-primary-100">
                                <x-lucide name="RotateCcw" class="w-5 h-5"/> Try Again
                            </a>
                            @if ($registration)
                                <a href="{{ route('camp.manual', $registration) }}" class="inline-flex items-center gap-2 rounded-lg border border-primary px-6 py-3 font-medium text-primary transition-colors hover:bg-primary/5">
                                    Pay by Bank Transfer
                                </a>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>
</x-layouts.app>
