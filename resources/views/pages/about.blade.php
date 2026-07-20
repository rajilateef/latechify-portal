<x-layouts.app title="About Us">
    {{-- ─────────────────────────  HERO  ───────────────────────── --}}
    <section class="relative overflow-hidden bg-primary text-white">
        <div class="absolute inset-0 dot-pattern-white opacity-[0.07]"></div>
        <div class="container-custom relative py-16 lg:py-24">
            <div class="grid lg:grid-cols-2 gap-12 items-center">
                {{-- Left --}}
                <div>
                    <span class="inline-flex items-center gap-2 bg-white/10 border border-white/15 text-white text-xs font-semibold uppercase tracking-wide px-4 py-1.5 rounded-full mb-6">
                        <x-lucide name="Sparkles" class="w-4 h-4"/> Transforming Futures Since 2020
                    </span>
                    <h1 class="mb-6 leading-tight">Empowering the Next Generation of <span class="underline decoration-wavy decoration-white/40 underline-offset-8">Tech Leaders</span></h1>
                    <p class="text-white/80 text-lg mb-8">Latechify Digital Hub is on a mission to transform tech education and nurture talent across Africa. We bridge the skills gap through innovative, hands-on learning experiences.</p>
                    <div class="flex flex-wrap gap-4">
                        <a href="#our-story" class="inline-flex items-center gap-2 bg-white text-primary hover:bg-white/90 px-6 py-3 rounded-lg font-medium transition-colors">
                            Learn Our Story <x-lucide name="ArrowRight" class="w-4 h-4"/>
                        </a>
                        <a href="{{ route('contact') }}" class="inline-flex items-center gap-2 border border-white/40 text-white hover:bg-white/10 px-6 py-3 rounded-lg font-medium transition-colors">
                            Contact Us
                        </a>
                    </div>
                </div>

                {{-- Right --}}
                <div class="relative">
                    <div class="rounded-2xl overflow-hidden shadow-2xl">
                        <img src="{{ media_url(setting('about_image'), 'assets/imgs/team.jpg') }}" alt="{{ setting('site_name', 'Latechify Digital Hub') }} team" class="w-full h-full object-cover aspect-[4/3]">
                    </div>
                    {{-- Floating badge: top-left --}}
                    <div class="absolute -top-5 -left-5 bg-white text-primary rounded-xl shadow-lg px-5 py-3 text-center">
                        <div class="text-2xl font-bold">85%</div>
                        <div class="text-xs text-muted-foreground">Job Placement</div>
                    </div>
                    {{-- Floating badge: bottom-right --}}
                    <div class="absolute -bottom-5 -right-5 bg-white text-primary rounded-xl shadow-lg px-5 py-3 text-center">
                        <div class="text-2xl font-bold">20+</div>
                        <div class="text-xs text-muted-foreground">Graduates</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ─────────────────────────  OUR STORY  ───────────────────────── --}}
    <section id="our-story" class="py-20 bg-white">
        <div class="container-custom">
            <div class="text-center max-w-2xl mx-auto mb-16">
                <span class="inline-block bg-primary/10 text-primary text-xs font-semibold uppercase tracking-wide px-4 py-1.5 rounded-full mb-4">Our Story</span>
                <h2 class="mb-4">A Journey of <span class="gradient-text">Growth &amp; Impact</span></h2>
                <p class="text-muted-foreground">Latechify Digital Hub was founded with a vision to democratize tech education and empower individuals with the skills needed to thrive in the digital economy.</p>
            </div>

            {{-- Timeline --}}
            <div class="relative max-w-4xl mx-auto">
                <div class="absolute left-6 md:left-1/2 top-0 bottom-0 w-0.5 bg-primary/15 md:-translate-x-1/2"></div>

                <div class="space-y-10">
                    @foreach ($milestones as $m)
                        <div class="relative flex items-center {{ $loop->even ? 'md:flex-row-reverse' : '' }}">
                            {{-- Icon node --}}
                            <div class="absolute left-6 md:left-1/2 -translate-x-1/2 z-10 flex items-center justify-center w-12 h-12 rounded-full bg-primary text-white shadow-lg ring-4 ring-white">
                                <x-lucide :name="$m->icon" class="w-6 h-6"/>
                            </div>
                            {{-- Card --}}
                            <div class="ml-16 md:ml-0 md:w-1/2 {{ $loop->even ? 'md:pl-16' : 'md:pr-16' }}">
                                <div class="rounded-xl border border-border bg-white shadow-sm hover:shadow-lg transition-shadow p-6 {{ $loop->even ? 'md:text-left' : 'md:text-right' }}">
                                    <div class="text-2xl font-bold text-primary">{{ $m->year }}</div>
                                    <p class="text-muted-foreground mt-2">{{ $m->event }}</p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    {{-- ─────────────────────────  MISSION & VISION  ───────────────────────── --}}
    <section class="py-20 bg-white">
        <div class="container-custom">
            <div class="grid md:grid-cols-2 gap-8">
                {{-- Mission --}}
                <div class="rounded-xl bg-primary text-white p-8 lg:p-10 shadow-sm">
                    <div class="inline-flex items-center justify-center w-14 h-14 rounded-xl bg-white/10 text-white mb-6">
                        <x-lucide name="Target" class="w-7 h-7"/>
                    </div>
                    <h3 class="mb-4">Our Mission</h3>
                    <p class="text-white/80 text-lg">To empower individuals with cutting-edge technical skills and provide businesses with innovative digital solutions that drive growth and success in the modern digital landscape.</p>
                </div>

                {{-- Vision --}}
                <div class="rounded-xl bg-primary/5 p-8 lg:p-10 border border-border shadow-sm">
                    <div class="inline-flex items-center justify-center w-14 h-14 rounded-xl bg-primary/10 text-primary mb-6">
                        <x-lucide name="Globe" class="w-7 h-7"/>
                    </div>
                    <h3 class="mb-4">Our Vision</h3>
                    <p class="text-muted-foreground text-lg">To become the leading technology education and digital solutions provider in Africa, known for excellence, innovation, and transformative impact on individuals and businesses.</p>
                </div>
            </div>
        </div>
    </section>

    {{-- ─────────────────────────  CORE VALUES  ───────────────────────── --}}
    <section class="py-20 bg-gray-50">
        <div class="container-custom">
            <div class="text-center max-w-2xl mx-auto mb-16">
                <span class="inline-block bg-primary/10 text-primary text-xs font-semibold uppercase tracking-wide px-4 py-1.5 rounded-full mb-4">What We Stand For</span>
                <h2 class="mb-4">Our Core <span class="gradient-text">Values</span></h2>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                @foreach ($values as $value)
                    <div class="rounded-xl border border-border bg-white shadow-sm hover:shadow-lg transition-shadow p-6">
                        <div class="inline-flex items-center justify-center w-12 h-12 rounded-lg bg-primary/10 text-primary mb-4">
                            <x-lucide :name="$value->icon" class="w-6 h-6"/>
                        </div>
                        <h3 class="text-xl font-semibold mb-2">{{ $value->title }}</h3>
                        <p class="text-muted-foreground text-sm">{{ $value->description }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ─────────────────────────  STATS BAND  ───────────────────────── --}}
    <section class="py-16 bg-primary text-white relative overflow-hidden">
        <div class="absolute inset-0 dot-pattern-white opacity-[0.07]"></div>
        <div class="container-custom relative">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-8">
                @foreach ($stats as $stat)
                    <div class="text-center">
                        <div class="inline-flex items-center justify-center w-14 h-14 rounded-xl bg-white/10 text-white mb-4">
                            <x-lucide :name="$stat->icon" class="w-7 h-7"/>
                        </div>
                        <div class="text-4xl font-bold">{{ $stat->value }}</div>
                        <div class="text-white/70 text-sm mt-1">{{ $stat->label }}</div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ─────────────────────────  CTA  ───────────────────────── --}}
    <section class="py-20 bg-gray-50">
        <div class="container-custom">
            <div class="max-w-3xl mx-auto text-center rounded-2xl border border-border bg-white shadow-sm p-10 lg:p-14">
                <span class="inline-block bg-primary/10 text-primary text-xs font-semibold uppercase tracking-wide px-4 py-1.5 rounded-full mb-4">Get Started Today</span>
                <h2 class="mb-4">Ready to Start Your <span class="gradient-text">Tech Journey?</span></h2>
                <p class="text-muted-foreground text-lg mb-8">Join thousands of students who have transformed their careers with Latechify Digital Hub. Our expert-led courses and supportive community are waiting for you.</p>
                <div class="flex flex-wrap justify-center gap-4">
                    <a href="{{ route('courses.index') }}" class="inline-flex items-center gap-2 bg-primary hover:bg-primary/90 text-white px-6 py-3 rounded-lg font-medium transition-colors">
                        Explore Our Courses <x-lucide name="ArrowRight" class="w-4 h-4"/>
                    </a>
                    <a href="{{ route('consultation') }}" class="inline-flex items-center gap-2 border border-primary text-primary hover:bg-primary/5 px-6 py-3 rounded-lg font-medium transition-colors">
                        Schedule a Consultation
                    </a>
                </div>
            </div>
        </div>
    </section>
</x-layouts.app>
