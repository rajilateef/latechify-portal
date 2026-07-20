<section class="py-20 bg-gradient-to-r from-blue-50 to-indigo-50">
    <div class="container-custom">
        <div class="text-center max-w-2xl mx-auto mb-16 reveal">
            <h2 class="mb-4">{{ setting('cohort_heading', 'Current Cohort Experience') }}</h2>
            <p class="text-muted-foreground">{{ setting('cohort_sub', 'Join our vibrant learning community and experience the interactive tech training environment firsthand') }}</p>
        </div>

        {{-- Stats --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-6 mb-16">
            @foreach ($cohortStats as $stat)
                <div class="reveal card-lift bg-white rounded-xl p-6 text-center shadow-sm" data-reveal-delay="{{ $loop->index * 90 }}">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-primary/10 text-primary mb-3"><x-lucide name="{{ $stat->icon }}" class="w-6 h-6"/></div>
                    <div class="text-3xl font-bold text-primary">{{ $stat->value }}</div>
                    <div class="text-muted-foreground text-sm mt-1">{{ $stat->label }}</div>
                </div>
            @endforeach
        </div>

        <div class="grid md:grid-cols-3 gap-12">
            {{-- Activities --}}
            <div class="md:col-span-2">
                <h3 class="text-2xl font-bold mb-6">Cohort Activities</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    @foreach ($cohortActivities as $activity)
                        <div class="reveal flex gap-4 bg-white rounded-xl p-5 shadow-sm card-lift" data-reveal-delay="{{ $loop->index * 70 }}">
                            <div class="shrink-0 w-12 h-12 rounded-full bg-primary/10 text-primary flex items-center justify-center"><x-lucide name="{{ $activity->icon }}" class="w-6 h-6"/></div>
                            <div>
                                <h4 class="font-semibold text-gray-900">{{ $activity->title }}</h4>
                                <p class="text-muted-foreground text-sm mt-1">{{ $activity->description }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Image card --}}
            <div class="relative rounded-xl overflow-hidden h-80 md:h-auto shadow-lg">
                <img src="{{ media_url(setting('cohort_image'), 'assets/imgs/cohort.jpg') }}" alt="Latechify cohort" loading="lazy" decoding="async" class="absolute inset-0 w-full h-full object-cover">
                <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/40 to-transparent"></div>
                <div class="absolute inset-x-0 bottom-0 p-6 text-white text-center">
                    <h3 class="text-xl font-bold">{{ setting('cohort_name', 'Current Cohort') }}</h3>
                    <p class="text-white/80 text-sm mt-1 mb-4">{{ setting('cohort_status') }}</p>
                    <a href="{{ route('apply') }}" class="block bg-primary hover:bg-primary/90 text-white py-2.5 rounded-lg font-medium transition-colors">Apply Now</a>
                </div>
            </div>
        </div>
    </div>
</section>
