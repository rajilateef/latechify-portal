<x-layouts.portal title="Notifications">
    <div class="max-w-3xl">
        <div class="flex items-center justify-between mb-5">
            <h2 class="text-xl font-bold text-gray-900">Notifications</h2>
            @if (auth()->user()->unreadNotifications()->count() > 0)
                <form method="POST" action="{{ route('portal.notifications.read-all') }}">@csrf
                    <button class="text-sm text-primary hover:underline">Mark all as read</button>
                </form>
            @endif
        </div>

        <div class="rounded-2xl border border-border bg-white shadow-sm divide-y divide-border overflow-hidden">
            @forelse ($notifications as $n)
                <a href="{{ route('portal.notifications.read', $n->id) }}" class="flex gap-3 p-4 hover:bg-gray-50 {{ $n->read_at ? '' : 'bg-primary/5' }}">
                    <div class="shrink-0 grid place-items-center w-10 h-10 rounded-lg bg-primary/10 text-primary"><x-lucide :name="$n->data['icon'] ?? 'Bell'" class="w-5 h-5"/></div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2">
                            <span class="font-medium text-gray-900">{{ $n->data['title'] ?? 'Notification' }}</span>
                            @if (! $n->read_at)<span class="w-2 h-2 rounded-full bg-primary shrink-0"></span>@endif
                        </div>
                        @if (!empty($n->data['body']))<p class="text-sm text-muted-foreground mt-0.5">{{ $n->data['body'] }}</p>@endif
                        <div class="text-xs text-gray-400 mt-1">{{ $n->created_at->diffForHumans() }}</div>
                    </div>
                </a>
            @empty
                <div class="p-12 text-center">
                    <div class="inline-flex w-12 h-12 items-center justify-center rounded-full bg-gray-100 text-gray-400 mb-3"><x-lucide name="BellOff" class="w-6 h-6"/></div>
                    <p class="text-sm text-muted-foreground">No notifications yet.</p>
                </div>
            @endforelse
        </div>

        <div class="mt-5">{{ $notifications->links() }}</div>
    </div>
</x-layouts.portal>
