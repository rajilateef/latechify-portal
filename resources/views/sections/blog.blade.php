@if ($blogPosts->isNotEmpty())
    <section id="blog" class="py-20 md:py-24 bg-white">
        <div class="container-custom">
            {{-- Header row: heading left, "View all" link right on desktop --}}
            <div class="flex flex-col gap-6 mb-12 md:mb-14 sm:flex-row sm:items-end sm:justify-between reveal-fade">
                <div class="max-w-2xl">
                    <span class="section-eyebrow">{{ setting('blog_eyebrow', 'Blog') }}</span>
                    <h2 class="mt-4 text-gray-900">{{ setting('blog_heading', 'Latest from our Blog') }}</h2>
                    <p class="text-muted-foreground mt-3">{{ setting('blog_sub', 'Insights, tutorials, and stories from our team to help you learn, build, and grow in tech.') }}</p>
                </div>

                <a href="{{ route('blog.index') }}"
                   class="group inline-flex shrink-0 items-center gap-2 self-start rounded-full border border-border bg-white px-5 py-2.5 text-sm font-semibold text-gray-900 shadow-sm transition-colors hover:border-primary/30 hover:text-primary sm:self-auto">
                    View all articles
                    <x-lucide name="ArrowUpRight" class="w-4 h-4 transition-transform group-hover:translate-x-0.5 group-hover:-translate-y-0.5"/>
                </a>
            </div>

            {{-- Equal 3-column card grid (1 / 2 / 3 columns) --}}
            <div class="grid gap-6 md:gap-8 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($blogPosts as $post)
                    <article data-reveal-delay="{{ $loop->index * 100 }}"
                             class="group flex flex-col overflow-hidden rounded-2xl border border-border bg-card shadow-sm card-lift reveal-fade">
                        {{-- Featured image (aria-hidden: the title below is the accessible link) --}}
                        <a href="{{ route('blog.show', $post->slug) }}" tabindex="-1" aria-hidden="true"
                           class="relative block aspect-[16/10] overflow-hidden bg-muted/30">
                            <img src="{{ media_url($post->featured_image, 'assets/imgs/banner.jpg') }}"
                                 alt="{{ $post->title }}"
                                 class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105"
                                 loading="lazy" decoding="async">
                            @if ($post->category)
                                <span class="absolute left-4 top-4 inline-flex items-center rounded-full bg-white/95 px-3 py-1 text-xs font-semibold text-primary shadow-sm">
                                    {{ $post->category }}
                                </span>
                            @endif
                        </a>

                        {{-- Body --}}
                        <div class="flex flex-1 flex-col p-6">
                            <div class="mb-3 flex flex-wrap items-center gap-x-4 gap-y-1.5 text-xs text-muted-foreground">
                                @if ($post->published_at)
                                    <span class="inline-flex items-center gap-1.5">
                                        <x-lucide name="Calendar" class="w-4 h-4"/>{{ $post->published_at?->format('M j, Y') }}
                                    </span>
                                @endif
                                @if ($post->author_name)
                                    <span class="inline-flex items-center gap-1.5">
                                        <x-lucide name="User" class="w-4 h-4"/>{{ $post->author_name }}
                                    </span>
                                @endif
                            </div>

                            <h3 class="text-lg font-bold leading-snug text-gray-900">
                                <a href="{{ route('blog.show', $post->slug) }}" class="transition-colors hover:text-primary">
                                    {{ $post->title }}
                                </a>
                            </h3>

                            @if ($post->excerpt)
                                <p class="mt-2 line-clamp-3 text-sm leading-relaxed text-muted-foreground">{{ $post->excerpt }}</p>
                            @endif

                            {{-- Footer pinned to bottom so baselines align across cards --}}
                            <div class="mt-auto border-t border-border pt-4">
                                <a href="{{ route('blog.show', $post->slug) }}"
                                   class="inline-flex items-center gap-1.5 text-sm font-semibold text-primary">
                                    Read more
                                    <x-lucide name="ArrowRight" class="w-4 h-4 transition-transform group-hover:translate-x-1"/>
                                </a>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </section>
@endif
