@php
    use Illuminate\Support\Str;
    use Illuminate\Support\Js;

    $courseSearch = $courses->map(fn ($c) => [
        't'    => Str::lower($c->title.' '.$c->description),
        'tags' => $c->tags ?? [],
    ])->values();

    $searchFor = fn ($c) => ['t' => Str::lower($c->title.' '.$c->description), 'tags' => $c->tags ?? []];
@endphp

<x-layouts.app title="Our Courses">
    <div x-data="{
        q: '',
        tag: 'all',
        matches(c) {
            const term = this.q.toLowerCase();
            return (this.q === '' || c.t.includes(term)) && (this.tag === 'all' || c.tags.includes(this.tag));
        },
        anyMatch(list) { return list.some(c => this.matches(c)); }
    }">
        {{-- HERO --}}
        <section class="relative py-16 bg-gradient-to-br from-primary/5 to-blue-50 overflow-hidden">
            <div class="absolute -top-16 -right-10 w-72 h-72 rounded-full bg-primary/10 blur-3xl aurora-blob"></div>
            <div class="container-custom relative">
                <div class="text-center max-w-2xl mx-auto reveal reveal-visible">
                    <h1 class="mb-5">Our <span class="gradient-text">Courses</span></h1>
                    <p class="text-lg text-muted-foreground mb-8">Comprehensive tech training programs designed to transform your career and equip you with in-demand skills for today's job market.</p>
                    <div class="relative max-w-md mx-auto">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-muted-foreground">
                            <x-lucide name="Search" class="w-5 h-5"/>
                        </span>
                        <input type="text" x-model="q" placeholder="Search courses..."
                               class="w-full rounded-lg border border-border bg-white pl-11 pr-4 py-3 text-gray-900 placeholder:text-muted-foreground shadow-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition">
                    </div>
                </div>
            </div>
        </section>

        {{-- COURSE LISTINGS --}}
        <section class="py-16 bg-white">
            <div class="container-custom">
                {{-- Filter bar --}}
                <div class="flex flex-wrap items-center gap-3 mb-12">
                    <span class="inline-flex items-center gap-2 text-sm font-medium text-muted-foreground mr-1">
                        <x-lucide name="Filter" class="w-4 h-4"/> Filter:
                    </span>
                    <button type="button" @click="tag = 'all'"
                            :class="tag === 'all' ? 'bg-primary text-white border-primary' : 'bg-white text-gray-700 border-border hover:border-primary'"
                            class="px-4 py-2 rounded-lg border text-sm font-medium transition-colors">All</button>
                    @foreach ($tags as $t)
                        <button type="button" @click="tag = @js($t)"
                                :class="tag === @js($t) ? 'bg-primary text-white border-primary' : 'bg-white text-gray-700 border-border hover:border-primary'"
                                class="px-4 py-2 rounded-lg border text-sm font-medium transition-colors">{{ $t }}</button>
                    @endforeach
                </div>

                {{-- Categories --}}
                @foreach ($grouped as $category => $list)
                    @php $catArr = $list->map($searchFor)->values(); @endphp
                    <div x-show="anyMatch({{ Js::from($catArr) }})" x-cloak class="mb-14 reveal">
                        <h2 class="text-2xl md:text-3xl font-bold mb-6 flex items-center gap-3">
                            <span class="w-8 h-1.5 rounded-full bg-gradient-to-r from-primary to-primary-0"></span>
                            {{ $category }} Courses
                        </h2>
                        <div class="grid md:grid-cols-2 gap-8">
                            @foreach ($list as $course)
                                <div x-show="matches({{ Js::from($searchFor($course)) }})" x-cloak
                                     class="flex flex-col rounded-xl border border-border bg-white shadow-sm card-lift p-6">
                                    <div class="flex items-start justify-between gap-3 mb-4">
                                        <div class="flex items-center gap-3">
                                            <div class="p-2.5 bg-primary/10 rounded-lg text-primary shrink-0"><x-lucide name="{{ $course->icon }}" class="w-6 h-6"/></div>
                                            <h3 class="text-xl font-bold text-gray-900">{{ $course->title }}</h3>
                                        </div>
                                        @if ($course->popular)
                                            <span class="shrink-0 inline-flex items-center gap-1 bg-primary text-white text-xs font-medium px-2.5 py-1 rounded-full">
                                                <x-lucide name="Star" class="w-3 h-3"/> Popular
                                            </span>
                                        @endif
                                    </div>

                                    <p class="text-muted-foreground mb-4">{{ $course->description }}</p>

                                    <div class="flex flex-wrap gap-4 text-sm text-muted-foreground mb-4">
                                        <span class="flex items-center gap-1.5"><x-lucide name="Clock" class="w-4 h-4"/> {{ $course->duration }}</span>
                                        <span class="flex items-center gap-1.5"><x-lucide name="GraduationCap" class="w-4 h-4"/> {{ $course->level }}</span>
                                    </div>

                                    @if (!empty($course->tags))
                                        <div class="flex flex-wrap gap-2 mb-5">
                                            @foreach ($course->tags as $tag)
                                                <span class="text-xs font-medium px-2.5 py-1 rounded-full border border-primary/20 bg-primary/5 text-primary">{{ $tag }}</span>
                                            @endforeach
                                        </div>
                                    @endif

                                    <div class="grid grid-cols-2 gap-3 mb-5 mt-auto">
                                        <div class="rounded-lg border border-border bg-white px-4 py-3">
                                            <div class="text-xs text-muted-foreground mb-1">Physical</div>
                                            <div class="text-lg font-bold text-gray-900">&#8358;{{ number_format($course->price_physical) }}</div>
                                        </div>
                                        <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3">
                                            <div class="text-xs text-green-700 mb-1">Online</div>
                                            <div class="text-lg font-bold text-green-600">&#8358;{{ number_format($course->price_online) }}</div>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-2 gap-3">
                                        <a href="{{ route('apply', ['course' => $course->slug, 'format' => 'physical']) }}"
                                           class="btn-shine inline-flex items-center justify-center gap-2 bg-primary hover:bg-primary/90 text-white px-4 py-2.5 rounded-lg font-medium transition-colors">
                                            Physical <x-lucide name="ArrowRight" class="w-4 h-4"/>
                                        </a>
                                        <a href="{{ route('apply', ['course' => $course->slug, 'format' => 'online']) }}"
                                           class="inline-flex items-center justify-center gap-2 border border-primary text-primary hover:bg-primary/5 px-4 py-2.5 rounded-lg font-medium transition-colors">
                                            Online <x-lucide name="ArrowRight" class="w-4 h-4"/>
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach

                {{-- Empty state --}}
                <div x-show="!anyMatch({{ Js::from($courseSearch) }})" x-cloak class="text-center py-16">
                    <div class="inline-flex items-center justify-center w-14 h-14 rounded-full bg-primary/10 text-primary mb-4"><x-lucide name="Search" class="w-7 h-7"/></div>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">No courses found</h3>
                    <p class="text-muted-foreground mb-6">Try adjusting your search or filter criteria</p>
                    <button type="button" @click="q = ''; tag = 'all'"
                            class="inline-flex items-center gap-2 bg-primary hover:bg-primary/90 text-white px-6 py-3 rounded-lg font-medium transition-colors">Clear Filters</button>
                </div>
            </div>
        </section>

        {{-- CTA --}}
        <section class="py-16 bg-muted/30">
            <div class="container-custom">
                <div class="max-w-3xl mx-auto text-center reveal">
                    <h2 class="mb-4">Not sure which course is <span class="gradient-text">right for you?</span></h2>
                    <p class="text-lg text-muted-foreground mb-8">Schedule a free consultation with our academic advisors who can help you choose the perfect program based on your goals, experience, and learning style.</p>
                    <div class="flex flex-wrap justify-center gap-4">
                        <a href="{{ route('consultation') }}" class="btn-shine inline-flex items-center gap-2 bg-primary hover:bg-primary/90 text-white px-6 py-3 rounded-lg font-medium transition-colors">
                            <x-lucide name="Calendar" class="w-5 h-5"/> Schedule Consultation
                        </a>
                        <a href="{{ route('apply') }}" class="inline-flex items-center gap-2 border border-primary text-primary hover:bg-primary/5 px-6 py-3 rounded-lg font-medium transition-colors">
                            Apply Now <x-lucide name="ArrowRight" class="w-5 h-5"/>
                        </a>
                    </div>
                </div>
            </div>
        </section>
    </div>
</x-layouts.app>
