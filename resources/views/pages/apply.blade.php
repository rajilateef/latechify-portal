<x-layouts.app title="Apply">
    <section class="py-12 bg-muted/10">
        <div class="container-custom">
            <div class="max-w-3xl mx-auto">
                @if (session('notice'))
                    <div class="mb-8 bg-yellow-50 border border-yellow-200 rounded-lg p-4 text-yellow-800">
                        {{ session('notice') }}
                    </div>
                @endif

                {{-- Heading --}}
                <div class="text-center mb-8">
                    <h1 class="mb-4">Apply For Our <span class="gradient-text">Cohort</span></h1>
                    <p class="text-lg text-muted-foreground">Fill in your details below to secure your spot.</p>
                </div>

                {{-- Form card --}}
                <div class="bg-white p-8 rounded-lg shadow-md border border-border">
                    <livewire:apply-form :selectedSlug="$selectedSlug" :classFormat="$classFormat" />
                </div>

                {{-- Legal footer --}}
                <p class="text-sm text-muted-foreground text-center mt-6">
                    By submitting this application, you agree to our <a href="{{ route('privacy') }}" class="text-primary underline">Privacy Policy</a> and <a href="{{ route('terms') }}" class="text-primary underline">Terms of Service</a>.
                </p>
            </div>
        </div>
    </section>
</x-layouts.app>
