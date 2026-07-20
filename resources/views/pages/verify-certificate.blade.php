<x-layouts.app title="Verify Certificate">
    <section class="relative py-16 bg-gradient-to-br from-primary/5 to-primary-0/10 overflow-hidden">
        <div class="absolute -top-16 right-0 w-72 h-72 rounded-full bg-primary/10 blur-3xl aurora-blob"></div>
        <div class="container-custom relative text-center max-w-2xl mx-auto reveal reveal-visible">
            <span class="inline-block bg-primary/10 text-primary text-xs font-semibold uppercase tracking-wide px-3 py-1.5 rounded-full mb-4">Certificate Verification</span>
            <h1 class="mb-4">Verify a <span class="gradient-text">Certificate</span></h1>
            <p class="text-muted-foreground text-lg">Enter a certificate ID below to confirm it was genuinely issued by Latechify Digital Hub.</p>
        </div>
    </section>

    <section class="py-14 bg-white">
        <div class="container-custom">
            <livewire:verify-certificate />
        </div>
    </section>
</x-layouts.app>
