<x-layouts.app title="Pricing">
    <div x-data="{ tab: 'physical' }">

        {{-- ── HERO ── --}}
        <section class="py-16 bg-gradient-to-br from-primary-0/10 to-white">
            <div class="container-custom">
                <div class="text-center max-w-3xl mx-auto">
                    <span class="inline-flex items-center gap-2 bg-primary/10 text-primary border border-primary/15 text-xs font-semibold uppercase tracking-wide px-3 py-1.5 rounded-full mb-5">
                        <x-lucide name="Sparkles" class="w-3.5 h-3.5"/> Investment In Your Future
                    </span>
                    <h1 class="mb-6 leading-tight">Transparent Pricing for Your <span class="gradient-text">Tech Journey</span></h1>
                    <p class="text-lg text-muted-foreground mb-8">Choose the perfect plan that fits your learning goals and budget. All packages include access to our vibrant community and expert-led instruction.</p>
                    <div class="flex flex-wrap justify-center gap-4">
                        <a href="#packages" class="inline-flex items-center gap-2 bg-gradient-to-r from-primary to-[#1a3ad4] hover:from-[#1a3ad4] hover:to-primary text-white px-7 py-3.5 rounded-lg shadow-lg shadow-primary/20 font-medium transition-all">
                            View Packages <x-lucide name="ArrowRight" class="w-4 h-4"/>
                        </a>
                        <a href="{{ route('consultation') }}" class="inline-flex items-center gap-2 border border-primary text-primary hover:bg-primary/5 px-7 py-3.5 rounded-lg font-medium transition-colors">
                            Schedule Consultation
                        </a>
                    </div>
                </div>

                {{-- Feature strip --}}
                <div class="mt-14 max-w-4xl mx-auto grid sm:grid-cols-3 gap-6 rounded-2xl border border-border bg-white shadow-sm p-8">
                    <div class="text-center">
                        <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-primary/10 text-primary mb-3"><x-lucide name="CreditCard" class="w-6 h-6"/></div>
                        <h3 class="text-lg font-semibold text-gray-900">Flexible Payment</h3>
                        <p class="text-muted-foreground text-sm mt-1">Multiple payment options including installments</p>
                    </div>
                    <div class="text-center">
                        <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-primary/10 text-primary mb-3"><x-lucide name="Briefcase" class="w-6 h-6"/></div>
                        <h3 class="text-lg font-semibold text-gray-900">Job-Ready Skills</h3>
                        <p class="text-muted-foreground text-sm mt-1">Practical curriculum focused on employable skills</p>
                    </div>
                    <div class="text-center">
                        <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-primary/10 text-primary mb-3"><x-lucide name="Infinity" class="w-6 h-6"/></div>
                        <h3 class="text-lg font-semibold text-gray-900">Lifetime Access</h3>
                        <p class="text-muted-foreground text-sm mt-1">Ongoing access to course materials and updates</p>
                    </div>
                </div>
            </div>
        </section>

        {{-- ── PACKAGES ── --}}
        <section id="packages" class="py-20 bg-muted/30">
            <div class="container-custom">
                <div class="text-center max-w-2xl mx-auto mb-10">
                    <h2 class="mb-4">Choose Your <span class="gradient-text">Learning Path</span></h2>
                    <p class="text-muted-foreground">Select the plan that best fits your learning goals and budget. All plans include access to our vibrant community.</p>
                </div>

                {{-- Tab toggle --}}
                <div class="max-w-md mx-auto grid grid-cols-2 gap-2 p-1 bg-white rounded-lg shadow-sm border mb-12">
                    <button type="button" @click="tab='physical'" :class="tab==='physical' ? 'bg-primary text-white' : 'text-gray-600 hover:text-gray-900'" class="py-2.5 rounded-md text-sm font-medium transition-colors">Physical</button>
                    <button type="button" @click="tab='online'" :class="tab==='online' ? 'bg-primary text-white' : 'text-gray-600 hover:text-gray-900'" class="inline-flex items-center justify-center gap-2 py-2.5 rounded-md text-sm font-medium transition-colors">
                        Online
                        <span class="bg-green-100 text-green-700 text-[10px] font-semibold px-2 py-0.5 rounded-full">Save up to 20%</span>
                    </button>
                </div>

                {{-- Cards --}}
                <div class="grid md:grid-cols-3 gap-8">
                    @foreach ($courses as $course)
                        <div @class([
                            'relative flex flex-col rounded-xl border border-border bg-white shadow-sm hover:shadow-lg transition-shadow p-6',
                            'ring-2 ring-primary-0' => $course->popular,
                        ])>
                            @if ($course->popular)
                                <span class="absolute -top-3 left-1/2 -translate-x-1/2 whitespace-nowrap bg-primary-0 text-white text-xs font-semibold px-3 py-1 rounded-full shadow-sm">Most Popular</span>
                            @endif

                            <h3 class="text-xl font-bold text-gray-900 mb-3">{{ $course->title }}</h3>

                            <div class="mb-4">
                                <span class="text-4xl font-bold text-primary" x-show="tab==='physical'">₦{{ number_format($course->price_physical) }}</span>
                                <span class="text-4xl font-bold text-primary" x-show="tab==='online'" x-cloak>₦{{ number_format($course->price_online) }}</span>
                            </div>

                            <div class="flex items-center gap-1.5 text-sm font-medium text-primary mb-5">
                                <x-lucide name="ChevronRight" class="w-4 h-4 shrink-0"/> {{ $course->popular_feature }}
                            </div>

                            <div class="border-t border-border pt-5 mb-6">
                                <p class="text-sm font-semibold text-gray-900 mb-3">What's included:</p>
                                <ul class="space-y-2.5">
                                    @foreach ($course->features as $feature)
                                        <li class="flex items-start gap-2.5 text-sm">
                                            @if ($feature->included)
                                                <x-lucide name="Check" class="w-4 h-4 text-green-500 shrink-0 mt-0.5"/>
                                                <span class="text-gray-700">{{ $feature->name }}</span>
                                            @else
                                                <x-lucide name="X" class="w-4 h-4 text-gray-400 shrink-0 mt-0.5"/>
                                                <span class="text-muted-foreground line-through">{{ $feature->name }}</span>
                                            @endif
                                        </li>
                                    @endforeach
                                </ul>
                            </div>

                            <div class="mt-auto">
                                @php
                                    $btnBase = 'inline-flex w-full items-center justify-center gap-2 px-6 py-3 rounded-lg font-medium transition-colors';
                                    $btnClass = $course->popular
                                        ? $btnBase.' bg-gradient-to-r from-primary to-[#1a3ad4] hover:opacity-90 text-white'
                                        : $btnBase.' bg-primary hover:bg-primary/90 text-white';
                                @endphp
                                <a x-show="tab==='physical'" href="{{ route('apply', ['course' => $course->slug, 'format' => 'physical']) }}" class="{{ $btnClass }}">
                                    Register Now <x-lucide name="ArrowRight" class="w-4 h-4"/>
                                </a>
                                <a x-show="tab==='online'" x-cloak href="{{ route('apply', ['course' => $course->slug, 'format' => 'online']) }}" class="{{ $btnClass }}">
                                    Register Now <x-lucide name="ArrowRight" class="w-4 h-4"/>
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Enterprise --}}
                <div class="mt-10 rounded-2xl bg-primary relative overflow-hidden text-white p-8 lg:p-10">
                    <div class="absolute inset-0 dot-pattern-white opacity-[0.07]"></div>
                    <div class="relative grid lg:grid-cols-2 gap-10 items-center">
                        <div>
                            <span class="inline-block bg-white/10 text-primary-0 text-xs font-semibold uppercase tracking-wide px-3 py-1.5 rounded-full mb-4">Enterprise</span>
                            <h3 class="text-2xl md:text-3xl font-bold mb-2">Custom Corporate Training</h3>
                            <p class="text-white/80 mb-6">Tailored training solutions for your organization</p>
                            <div class="mb-6">
                                <span class="text-sm text-white/70">Starting from</span>
                                <div class="text-4xl font-bold">₦{{ number_format(setting('enterprise_base_price', 500000)) }}</div>
                            </div>
                            <a href="{{ route('contact') }}" class="inline-flex items-center gap-2 bg-white text-primary hover:bg-white/90 px-6 py-3 rounded-lg font-medium transition-colors">
                                Get in Touch <x-lucide name="ArrowRight" class="w-4 h-4"/>
                            </a>
                        </div>
                        <ul class="grid sm:grid-cols-2 gap-3">
                            @foreach ([
                                'Customized training program',
                                'Dedicated account manager',
                                'On-site or virtual delivery',
                                'Progress reporting',
                                'Certification for all participants',
                                'Post-training support',
                            ] as $item)
                                <li class="flex items-center gap-2.5 text-sm text-white/90">
                                    <x-lucide name="Check" class="w-4 h-4 text-primary-0 shrink-0"/> {{ $item }}
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </section>

        {{-- ── CONSULTATION CTA ── --}}
        <section class="py-20 bg-white">
            <div class="container-custom">
                <div class="max-w-4xl mx-auto rounded-2xl bg-gradient-to-r from-primary to-[#1a3ad4] text-white text-center p-10 lg:p-14 shadow-lg">
                    <h2 class="mb-4">Not sure which plan is right for you?</h2>
                    <p class="text-white/85 text-lg mb-8 max-w-2xl mx-auto">Our team of education advisors can help you choose the perfect plan based on your career goals and learning needs.</p>
                    <a href="{{ route('consultation') }}" class="inline-flex items-center gap-2 bg-white text-primary hover:bg-white/90 px-7 py-3.5 rounded-lg font-medium transition-colors">
                        Schedule a Free Consultation <x-lucide name="ArrowRight" class="w-4 h-4"/>
                    </a>
                </div>
            </div>
        </section>

        {{-- ── FAQ ── --}}
        <section class="py-20 bg-muted/30">
            <div class="container-custom">
                <div class="text-center max-w-2xl mx-auto mb-16">
                    <h2 class="mb-4">Frequently <span class="gradient-text">Asked Questions</span></h2>
                    <p class="text-muted-foreground">Everything you need to know about our pricing and packages</p>
                </div>
                <div class="max-w-3xl mx-auto">
                    <x-faq-accordion :faqs="$faqs" />
                </div>
            </div>
        </section>

    </div>
</x-layouts.app>
