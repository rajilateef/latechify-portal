@php
    // Only ~3 testimonials exist, so each column shows them all at a different
    // rotation + speed for variety. Each column is duplicated x2 for a seamless loop.
    $all = $testimonials->values();
    $rotate = fn ($by) => $all->isEmpty() ? $all : $all->slice($by % max($all->count(), 1))->concat($all->slice(0, $by % max($all->count(), 1)))->values();
    $columns = [
        ['items' => $rotate(0), 'dur' => '26s', 'class' => ''],
        ['items' => $rotate(1), 'dur' => '32s', 'class' => 'hidden md:block'],
        ['items' => $rotate(2), 'dur' => '29s', 'class' => 'hidden lg:block'],
    ];
@endphp

<section class="py-20 md:py-24 bg-white overflow-hidden">
    <div class="container-custom">
        <div class="text-center max-w-2xl mx-auto reveal">
            <span class="section-eyebrow">{{ setting('testimonials_eyebrow', 'Testimonials') }}</span>
            <h2 class="mt-3 text-gray-900">{{ setting('testimonials_heading', 'What Our Clients Say') }}</h2>
            <p class="text-muted-foreground mt-3">{{ setting('testimonials_sub', "Don't just take our word for it. Here's what our students and clients have to say about their experience with Latechify Digital Hub.") }}</p>
        </div>

        @if ($all->isNotEmpty())
            <div class="flex justify-center gap-6 mt-12 max-h-[42rem] overflow-hidden vscroll-mask">
                @foreach ($columns as $col)
                    <div class="vscroll {{ $col['class'] }}">
                        <div class="vscroll-track flex flex-col gap-6 pb-6" style="--vscroll-duration: {{ $col['dur'] }}">
                            @foreach ($col['items']->concat($col['items']) as $i => $t)
                                <div class="w-[20rem] max-w-full p-7 rounded-3xl border border-border bg-card shadow-lg shadow-primary/10"
                                     @if ($i >= $col['items']->count()) aria-hidden="true" @endif>
                                    <div class="flex gap-0.5 text-amber-400 mb-4">
                                        @for ($s = 0; $s < ($t->rating ?: 5); $s++)<x-lucide name="Star" class="w-4 h-4 fill-current"/>@endfor
                                    </div>
                                    <p class="text-gray-700 leading-relaxed">{{ $t->quote }}</p>
                                    <div class="flex items-center gap-3 mt-5">
                                        <img src="{{ media_url($t->avatar, 'assets/imgs/latech.jpg') }}" alt="{{ $t->name }}"
                                             width="44" height="44" loading="lazy" decoding="async"
                                             class="h-11 w-11 rounded-full object-cover ring-2 ring-primary/10">
                                        <div class="flex flex-col">
                                            <div class="font-semibold tracking-tight leading-5 text-gray-900">{{ $t->name }}</div>
                                            <div class="text-sm tracking-tight text-muted-foreground leading-5 mt-0.5">{{ $t->designation }}</div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</section>
