@php
    $resourceIcons = [
        'video'    => 'Video',
        'document' => 'FileText',
        'exercise' => 'Code',
        'quiz'     => 'ListChecks',
    ];
@endphp

<x-layouts.app :title="$course->title">
    {{-- HERO BANNER --}}
    <section class="relative h-[400px] md:h-[500px] flex items-end overflow-hidden">
        <img src="{{ media_url($course->image) }}" alt="{{ $course->title }}" class="absolute inset-0 w-full h-full object-cover">
        <div class="absolute inset-0 bg-gradient-to-t from-black/85 via-black/50 to-transparent"></div>
        <div class="container-custom relative pb-10 md:pb-14">
            <span class="inline-flex items-center gap-1.5 bg-primary-0 text-primary text-sm font-semibold px-3 py-1 rounded-full mb-4">
                <x-lucide name="GraduationCap" class="w-4 h-4"/> {{ $course->level }}
            </span>
            <h1 class="text-white mb-4 max-w-3xl">{{ $course->title }}</h1>
            <p class="text-lg text-white/85 max-w-2xl">{{ $course->subtitle }}</p>
        </div>
    </section>

    {{-- BODY --}}
    <section class="py-16 bg-white">
        <div class="container-custom">
            <div class="grid md:grid-cols-3 gap-12">
                {{-- LEFT --}}
                <div class="md:col-span-2">
                    {{-- About --}}
                    <h2 class="mb-5">About This <span class="gradient-text">Course</span></h2>
                    <div class="prose max-w-none text-muted-foreground text-lg leading-relaxed mb-12">
                        {!! $course->long_description !!}
                    </div>

                    {{-- What You'll Learn --}}
                    <h3 class="text-2xl font-bold mb-6">What You'll Learn</h3>
                    <div class="grid sm:grid-cols-2 gap-4 mb-12">
                        @foreach ($course->highlights as $highlight)
                            <div class="flex items-start gap-3">
                                <span class="shrink-0 text-primary mt-0.5"><x-lucide name="CheckCircle" class="w-5 h-5"/></span>
                                <span class="text-gray-700">{{ $highlight->text }}</span>
                            </div>
                        @endforeach
                    </div>

                    {{-- Curriculum --}}
                    <h3 class="text-2xl font-bold mb-6">Course Curriculum</h3>
                    <div class="space-y-4 mb-12">
                        @foreach ($course->modules as $module)
                            <div class="rounded-xl border border-border bg-white shadow-sm hover:shadow-lg transition-shadow p-6">
                                <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
                                    <div class="flex items-center gap-3">
                                        <span class="inline-flex items-center bg-primary/10 text-primary text-xs font-semibold px-3 py-1 rounded-full">{{ $module->week }}</span>
                                        <h4 class="text-lg font-bold text-gray-900">{{ $module->title }}</h4>
                                    </div>
                                    <span class="inline-flex items-center gap-1.5 text-sm text-muted-foreground">
                                        <x-lucide name="Clock" class="w-4 h-4"/> Est. {{ $module->estimated_hours }} hours
                                    </span>
                                </div>

                                @if ($module->is_detailed)
                                    <div class="space-y-4">
                                        @foreach ($module->topics as $topic)
                                            <div class="rounded-lg border border-border bg-muted/30 p-4">
                                                <div class="flex flex-wrap items-center justify-between gap-2 mb-1">
                                                    <h5 class="font-semibold text-gray-900">{{ $topic->title }}</h5>
                                                    @if ($topic->duration)
                                                        <span class="inline-flex items-center gap-1.5 text-xs text-muted-foreground">
                                                            <x-lucide name="Clock" class="w-3.5 h-3.5"/> {{ $topic->duration }}
                                                        </span>
                                                    @endif
                                                </div>
                                                @if ($topic->description)
                                                    <p class="text-sm text-muted-foreground mb-3">{{ $topic->description }}</p>
                                                @endif
                                                @if (!empty($topic->resources) && count($topic->resources))
                                                    <div class="flex flex-wrap gap-2">
                                                        @foreach ($topic->resources as $resource)
                                                            <span class="inline-flex items-center gap-1.5 text-xs font-medium px-2.5 py-1 rounded-full border border-primary/20 bg-primary/5 text-primary">
                                                                <x-lucide name="{{ $resourceIcons[$resource->type] ?? 'FileText' }}" class="w-3.5 h-3.5"/>
                                                                {{ ucfirst($resource->type) }}: {{ $resource->title }}
                                                            </span>
                                                        @endforeach
                                                    </div>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <ul class="space-y-2.5">
                                        @foreach ($module->topics as $topic)
                                            <li class="flex items-start gap-3 text-gray-700">
                                                <span class="shrink-0 text-primary mt-0.5"><x-lucide name="CheckCircle" class="w-5 h-5"/></span>
                                                <span>{{ $topic->title }}</span>
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    {{-- FAQ --}}
                    <h3 class="text-2xl font-bold mb-6">Frequently Asked Questions</h3>
                    <x-faq-accordion :faqs="$course->faqs" />
                </div>

                {{-- RIGHT --}}
                <div class="md:col-span-1">
                    <div class="sticky top-24 rounded-xl border border-border bg-muted/30 shadow-sm p-6">
                        <div class="mb-6">
                            <div class="text-sm text-muted-foreground mb-1">Tuition</div>
                            <div class="text-3xl font-bold text-primary">&#8358;{{ number_format($course->price_physical) }}</div>
                        </div>

                        <div class="space-y-4 mb-6">
                            <div class="flex items-center gap-3">
                                <span class="shrink-0 inline-flex items-center justify-center w-9 h-9 rounded-full bg-primary/10 text-primary"><x-lucide name="Clock" class="w-4 h-4"/></span>
                                <div>
                                    <div class="text-xs text-muted-foreground">Duration</div>
                                    <div class="font-medium text-gray-900">{{ $course->duration }}</div>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="shrink-0 inline-flex items-center justify-center w-9 h-9 rounded-full bg-primary/10 text-primary"><x-lucide name="Calendar" class="w-4 h-4"/></span>
                                <div>
                                    <div class="text-xs text-muted-foreground">Schedule</div>
                                    <div class="font-medium text-gray-900">{{ $course->schedule }}</div>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="shrink-0 inline-flex items-center justify-center w-9 h-9 rounded-full bg-primary/10 text-primary"><x-lucide name="GraduationCap" class="w-4 h-4"/></span>
                                <div>
                                    <div class="text-xs text-muted-foreground">Level</div>
                                    <div class="font-medium text-gray-900">{{ $course->level }}</div>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="shrink-0 inline-flex items-center justify-center w-9 h-9 rounded-full bg-primary/10 text-primary"><x-lucide name="Star" class="w-4 h-4"/></span>
                                <div>
                                    <div class="text-xs text-muted-foreground">Rating</div>
                                    <div class="font-medium text-gray-900">{{ $course->rating }} out of 5</div>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="shrink-0 inline-flex items-center justify-center w-9 h-9 rounded-full bg-primary/10 text-primary"><x-lucide name="Globe" class="w-4 h-4"/></span>
                                <div>
                                    <div class="text-xs text-muted-foreground">Starting</div>
                                    <div class="font-medium text-gray-900">{{ $course->start_date }}</div>
                                </div>
                            </div>
                        </div>

                        <a href="{{ route('apply', ['course' => $course->slug]) }}"
                           class="w-full inline-flex items-center justify-center gap-2 bg-gradient-to-r from-primary to-[#1a3ad4] text-white px-6 py-3 rounded-lg font-medium transition-all hover:shadow-lg hover:shadow-primary/20">
                            Enroll Now <x-lucide name="ArrowRight" class="w-5 h-5"/>
                        </a>
                    </div>
                </div>
            </div>

            {{-- BOTTOM CTA --}}
            <div class="mt-16 bg-primary/10 rounded-xl p-10 md:p-14 text-center">
                <h2 class="mb-4">Ready to Start Your <span class="gradient-text">Journey?</span></h2>
                <p class="text-lg text-muted-foreground max-w-2xl mx-auto mb-8">Join our upcoming cohort and transform your career with expert guidance and hands-on learning. Classes begin on {{ $course->start_date }}.</p>
                <a href="{{ route('pricing') }}" class="inline-flex items-center gap-2 bg-primary hover:bg-primary/90 text-white px-6 py-3 rounded-lg font-medium transition-colors">
                    View Pricing Options <x-lucide name="ArrowRight" class="w-5 h-5"/>
                </a>
            </div>
        </div>
    </section>
</x-layouts.app>
