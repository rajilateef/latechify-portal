<x-layouts.app title="Blog">
    <section class="py-16 md:py-20 bg-white">
        <div class="container-custom">
            {{-- Header --}}
            <div class="text-center max-w-2xl mx-auto mb-16">
                <h1 class="mb-4"><span class="gradient-text">Blog</span></h1>
                <p class="text-muted-foreground text-lg">Insights, tutorials, and industry news from our team of experts at Latechify Digital Hub. Stay updated with the latest in tech education and digital innovation.</p>
            </div>

            {{-- Post grid --}}
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                @forelse ($posts as $post)
                    <article class="rounded-xl border border-border bg-white shadow-sm hover:shadow-lg transition-shadow overflow-hidden flex flex-col">
                        <a href="{{ route('blog.show', $post->slug) }}" class="relative block">
                            <img src="{{ media_url($post->featured_image, 'assets/imgs/banner.jpg') }}" alt="{{ $post->title }}" class="w-full h-48 object-cover">
                            <span class="absolute top-4 left-4 bg-primary text-white text-xs font-medium px-3 py-1 rounded-full">{{ $post->category }}</span>
                        </a>
                        <div class="p-6 flex flex-col flex-1">
                            <div class="flex items-center justify-between gap-3 text-sm text-muted-foreground mb-3">
                                <span class="inline-flex items-center gap-1.5"><x-lucide name="Calendar" class="w-4 h-4"/>{{ $post->published_at?->format('M j, Y') }}</span>
                                <span>By {{ $post->author_name }}</span>
                            </div>
                            <h3 class="text-xl font-bold text-gray-900 mb-3">
                                <a href="{{ route('blog.show', $post->slug) }}" class="hover:text-primary transition-colors">{{ $post->title }}</a>
                            </h3>
                            <p class="text-muted-foreground text-sm mb-6 flex-1">{{ $post->excerpt }}</p>
                            <a href="{{ route('blog.show', $post->slug) }}" class="inline-flex items-center justify-center gap-2 border border-primary text-primary hover:bg-primary/5 px-6 py-3 rounded-lg font-medium transition-colors mt-auto">
                                Read More <x-lucide name="ArrowRight" class="w-4 h-4"/>
                            </a>
                        </div>
                    </article>
                @empty
                    <div class="col-span-full text-center py-16">
                        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-primary/10 text-primary mb-4"><x-lucide name="Newspaper" class="w-8 h-8"/></div>
                        <h3 class="text-2xl font-bold text-gray-900 mb-2">No articles yet</h3>
                        <p class="text-muted-foreground">Check back soon for insights, tutorials, and industry news from our team.</p>
                    </div>
                @endforelse
            </div>

            {{-- Pagination --}}
            <div class="mt-12">
                {{ $posts->links() }}
            </div>

            {{-- Newsletter --}}
            <div class="mt-16 bg-primary/5 border border-primary/20 rounded-xl p-10 text-center max-w-3xl mx-auto">
                <h2 class="mb-4">Subscribe to Our <span class="gradient-text">Newsletter</span></h2>
                <p class="text-muted-foreground mb-8 max-w-xl mx-auto">Get the latest articles, tutorials, and updates delivered straight to your inbox. No spam, just valuable content to help you stay ahead in tech.</p>
                <livewire:newsletter-form />
            </div>
        </div>
    </section>
</x-layouts.app>
