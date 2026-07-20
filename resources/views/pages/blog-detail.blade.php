<x-layouts.app :title="$post->title">
    <article>
        {{-- Hero --}}
        <div class="relative h-[360px] w-full overflow-hidden">
            <img src="{{ media_url($post->featured_image, 'assets/imgs/banner.jpg') }}" alt="{{ $post->title }}" class="absolute inset-0 w-full h-full object-cover">
            <div class="absolute inset-0 bg-gradient-to-t from-black/85 via-black/50 to-black/25"></div>
            <div class="absolute inset-0 flex items-end">
                <div class="container-custom pb-10">
                    <div class="max-w-3xl">
                        <div class="flex flex-wrap items-center gap-4 text-white/90 text-sm mb-4">
                            <span class="bg-primary text-white text-xs font-medium px-3 py-1 rounded-full">{{ $post->category }}</span>
                            <span class="inline-flex items-center gap-1.5"><x-lucide name="Calendar" class="w-4 h-4"/>{{ $post->published_at?->format('M j, Y') }}</span>
                            <span class="inline-flex items-center gap-1.5"><x-lucide name="User" class="w-4 h-4"/>By {{ $post->author_name }}</span>
                        </div>
                        <h1 class="text-white text-3xl md:text-4xl lg:text-5xl">{{ $post->title }}</h1>
                    </div>
                </div>
            </div>
        </div>

        {{-- Body --}}
        <div class="py-16 bg-white">
            <div class="container-custom">
                <div class="max-w-3xl mx-auto">
                    <div class="prose-legal">{!! $post->body !!}</div>

                    <div class="mt-10 pt-8 border-t border-border">
                        <a href="{{ route('blog.index') }}" class="inline-flex items-center gap-2 text-primary hover:text-primary/80 font-medium transition-colors">
                            <x-lucide name="ArrowLeft" class="w-4 h-4"/> Back to Blog
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Related --}}
        @if ($related->isNotEmpty())
            <section class="py-16 bg-gradient-to-r from-blue-50 to-indigo-50 border-t border-border">
                <div class="container-custom">
                    <h2 class="mb-10 text-center">Related <span class="gradient-text">Articles</span></h2>
                    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                        @foreach ($related as $item)
                            <article class="rounded-xl border border-border bg-white shadow-sm hover:shadow-lg transition-shadow overflow-hidden flex flex-col">
                                <a href="{{ route('blog.show', $item->slug) }}" class="relative block">
                                    <img src="{{ media_url($item->featured_image, 'assets/imgs/banner.jpg') }}" alt="{{ $item->title }}" class="w-full h-44 object-cover">
                                    @if (!empty($item->category))
                                        <span class="absolute top-4 left-4 bg-primary text-white text-xs font-medium px-3 py-1 rounded-full">{{ $item->category }}</span>
                                    @endif
                                </a>
                                <div class="p-6 flex flex-col flex-1">
                                    <h3 class="text-lg font-bold text-gray-900 mb-4">
                                        <a href="{{ route('blog.show', $item->slug) }}" class="hover:text-primary transition-colors">{{ $item->title }}</a>
                                    </h3>
                                    <a href="{{ route('blog.show', $item->slug) }}" class="inline-flex items-center gap-2 text-primary hover:text-primary/80 font-medium transition-colors mt-auto">
                                        Read More <x-lucide name="ArrowRight" class="w-4 h-4"/>
                                    </a>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </div>
            </section>
        @endif
    </article>
</x-layouts.app>
