<x-layouts.app :title="$page->title">
    <section class="py-12 md:py-16 bg-white">
        <div class="container-custom">
            <div class="max-w-4xl mx-auto">
                {{-- Header --}}
                <div class="text-center mb-12">
                    <h1 class="mb-4">{{ $page->title }}</h1>
                    @if ($page->last_updated)
                        <p class="text-muted-foreground">Last updated: {{ $page->last_updated?->format('F j, Y') }}</p>
                    @endif
                </div>

                {{-- Body --}}
                <div class="prose-legal">{!! $page->body !!}</div>

                {{-- Cookies table --}}
                @if ($cookies->isNotEmpty())
                    <div class="mt-8 overflow-x-auto rounded-xl border border-border">
                        <table class="w-full text-left border-collapse min-w-full">
                            <thead>
                                <tr>
                                    <th class="bg-gray-100 p-3 font-semibold text-gray-900 border-b border-border">Name</th>
                                    <th class="bg-gray-100 p-3 font-semibold text-gray-900 border-b border-border">Purpose</th>
                                    <th class="bg-gray-100 p-3 font-semibold text-gray-900 border-b border-border">Duration</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($cookies as $cookie)
                                    <tr>
                                        <td class="p-3 border-b border-border text-gray-900 font-mono text-sm">{{ $cookie->name }}</td>
                                        <td class="p-3 border-b border-border text-gray-600">{{ $cookie->purpose }}</td>
                                        <td class="p-3 border-b border-border text-gray-600 whitespace-nowrap">{{ $cookie->duration }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </section>
</x-layouts.app>
