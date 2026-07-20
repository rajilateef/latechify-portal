<x-layouts.app :title="$success ? 'Application Submitted' : 'Payment Not Completed'">
    <section class="py-20 bg-white">
        <div class="container-custom">
            <div class="max-w-xl mx-auto">
                <div class="rounded-xl border border-border bg-white shadow-sm p-8 md:p-10 text-center">
                    @if ($success)
                        <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-green-100 text-green-600 mb-6">
                            <x-lucide name="CheckCircle" class="w-10 h-10"/>
                        </div>
                        <h1 class="text-2xl md:text-3xl mb-4">Application Submitted Successfully!</h1>
                        <p class="text-muted-foreground mb-8">Thank you for applying. Your payment has been confirmed and we will be in touch with you shortly.</p>
                        <a href="{{ route('home') }}" class="inline-flex items-center gap-2 bg-primary hover:bg-primary/90 text-white px-6 py-3 rounded-lg font-medium transition-colors">
                            <x-lucide name="Home" class="w-5 h-5"/> Return to Home
                        </a>
                    @else
                        <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-red-100 text-red-600 mb-6">
                            <x-lucide name="X" class="w-10 h-10"/>
                        </div>
                        <h1 class="text-2xl md:text-3xl mb-4">Payment Not Completed</h1>
                        <p class="text-muted-foreground mb-8">We could not confirm your payment. If you were charged, please contact us and we'll sort it out. You can also try again.</p>
                        <div class="flex flex-wrap justify-center gap-4">
                            <a href="{{ route('apply') }}" class="inline-flex items-center gap-2 bg-primary hover:bg-primary/90 text-white px-6 py-3 rounded-lg font-medium transition-colors">
                                <x-lucide name="RotateCcw" class="w-5 h-5"/> Try Again
                            </a>
                            <a href="{{ route('contact') }}" class="inline-flex items-center gap-2 border border-primary text-primary hover:bg-primary/5 px-6 py-3 rounded-lg font-medium transition-colors">
                                Contact Us
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>
</x-layouts.app>
