@props(['title' => 'Trainee Portal'])
@php
    $user = auth()->user();
    $recentNotifications = $user->notifications()->latest()->limit(6)->get();
    $unreadCount = $user->unreadNotifications()->count();
    $nav = [
        ['route' => 'portal.dashboard',     'label' => 'Dashboard',     'icon' => 'LayoutDashboard'],
        ['route' => 'portal.materials',     'label' => 'Materials',     'icon' => 'FolderOpen'],
        ['route' => 'portal.assignments',   'label' => 'Assignments',   'icon' => 'ClipboardList'],
        ['route' => 'portal.schedule',      'label' => 'Schedule',      'icon' => 'CalendarDays'],
        ['route' => 'portal.announcements', 'label' => 'Announcements', 'icon' => 'Megaphone'],
        ['route' => 'portal.fees',          'label' => 'Fees',          'icon' => 'Wallet'],
        ['route' => 'portal.certificates',  'label' => 'Certificates',  'icon' => 'Award'],
        ['route' => 'portal.enroll',        'label' => 'Enrol',         'icon' => 'PlusCircle'],
    ];
@endphp
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <script>document.documentElement.classList.add('js')</script>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title }} · {{ setting('site_name', 'Latechify') }}</title>
    <link rel="icon" href="{{ media_url(setting('logo'), 'favicon.ico') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="min-h-screen bg-gray-50 text-gray-900 antialiased" x-data="{ sidebar: false }" x-cloak>

    {{-- Mobile backdrop --}}
    <div x-show="sidebar" @click="sidebar = false" class="fixed inset-0 z-40 bg-black/40 lg:hidden" x-transition.opacity></div>

    {{-- Sidebar --}}
    <aside :class="sidebar ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
           class="fixed inset-y-0 left-0 z-50 w-64 bg-primary text-white flex flex-col transition-transform duration-200 lg:translate-x-0">
        <div class="flex items-center gap-2.5 px-5 h-16 border-b border-white/10">
            <img src="{{ media_url(setting('logo_white'), 'assets/imgs/latechify_logo_white.png') }}" class="h-8 w-auto" alt="{{ setting('site_name') }}">
            <span class="font-semibold">Trainee Portal</span>
            <button @click="sidebar = false" class="ml-auto lg:hidden"><x-lucide name="X" class="w-5 h-5"/></button>
        </div>
        <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-1">
            @foreach ($nav as $item)
                @php $active = request()->routeIs($item['route']); @endphp
                <a href="{{ route($item['route']) }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-colors {{ $active ? 'bg-white text-primary font-semibold shadow-sm' : 'text-white/80 hover:bg-white/10 hover:text-white' }}">
                    <x-lucide :name="$item['icon']" class="w-5 h-5 shrink-0"/>
                    <span>{{ $item['label'] }}</span>
                    @if ($item['route'] === 'portal.fees' && $user->totalOutstanding() > 0)
                        <span class="ml-auto text-[10px] font-bold bg-red-500 text-white rounded-full px-1.5 py-0.5">Due</span>
                    @endif
                </a>
            @endforeach
        </nav>
        <div class="p-4 border-t border-white/10">
            <a href="{{ route('portal.profile') }}" class="flex items-center gap-3 rounded-lg p-2 hover:bg-white/10 transition-colors">
                @if ($user->avatar_url)
                    <img src="{{ media_url($user->avatar_url) }}" class="w-9 h-9 rounded-full object-cover" alt="">
                @else
                    <span class="w-9 h-9 rounded-full bg-white/20 grid place-items-center text-sm font-semibold">{{ $user->initials() }}</span>
                @endif
                <div class="min-w-0">
                    <div class="text-sm font-medium truncate">{{ $user->displayName() }}</div>
                    <div class="text-xs text-white/60">View profile</div>
                </div>
            </a>
        </div>
    </aside>

    {{-- Main --}}
    <div class="lg:pl-64 flex flex-col min-h-screen">
        <header class="sticky top-0 z-30 bg-white/90 backdrop-blur border-b border-border">
            <div class="flex items-center gap-3 h-16 px-4 md:px-6">
                <button @click="sidebar = true" class="lg:hidden text-gray-600"><x-lucide name="Menu" class="w-6 h-6"/></button>
                <h1 class="text-lg md:text-xl font-bold text-gray-900 truncate">{{ $title }}</h1>

                <div class="ml-auto flex items-center gap-2">
                    {{-- Notification bell --}}
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" class="relative grid place-items-center w-10 h-10 rounded-full hover:bg-gray-100 text-gray-600">
                            <x-lucide name="Bell" class="w-5 h-5"/>
                            @if ($unreadCount > 0)
                                <span class="absolute top-1.5 right-1.5 min-w-4 h-4 px-1 grid place-items-center text-[10px] font-bold text-white bg-red-500 rounded-full">{{ $unreadCount > 9 ? '9+' : $unreadCount }}</span>
                            @endif
                        </button>
                        <div x-show="open" @click.outside="open = false" x-transition
                             class="absolute right-0 mt-2 w-80 max-w-[90vw] rounded-xl border border-border bg-white shadow-xl overflow-hidden">
                            <div class="flex items-center justify-between px-4 py-3 border-b border-border">
                                <span class="font-semibold text-sm">Notifications</span>
                                @if ($unreadCount > 0)
                                    <form method="POST" action="{{ route('portal.notifications.read-all') }}">@csrf
                                        <button class="text-xs text-primary hover:underline">Mark all read</button>
                                    </form>
                                @endif
                            </div>
                            <div class="max-h-96 overflow-y-auto divide-y divide-border">
                                @forelse ($recentNotifications as $n)
                                    <a href="{{ route('portal.notifications.read', $n->id) }}"
                                       class="flex gap-3 px-4 py-3 hover:bg-gray-50 {{ $n->read_at ? '' : 'bg-primary/5' }}">
                                        <x-lucide :name="$n->data['icon'] ?? 'Bell'" class="w-4 h-4 mt-0.5 text-primary shrink-0"/>
                                        <div class="min-w-0">
                                            <div class="text-sm font-medium text-gray-900">{{ $n->data['title'] ?? 'Notification' }}</div>
                                            @if (!empty($n->data['body']))<div class="text-xs text-muted-foreground line-clamp-2">{{ $n->data['body'] }}</div>@endif
                                            <div class="text-[11px] text-gray-400 mt-0.5">{{ $n->created_at->diffForHumans() }}</div>
                                        </div>
                                    </a>
                                @empty
                                    <div class="px-4 py-8 text-center text-sm text-muted-foreground">You're all caught up.</div>
                                @endforelse
                            </div>
                            <a href="{{ route('portal.notifications') }}" class="block px-4 py-2.5 text-center text-xs font-medium text-primary hover:bg-gray-50 border-t border-border">View all</a>
                        </div>
                    </div>

                    {{-- Avatar menu --}}
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" class="flex items-center gap-2 rounded-full hover:bg-gray-100 p-1 pr-2">
                            @if ($user->avatar_url)
                                <img src="{{ media_url($user->avatar_url) }}" class="w-8 h-8 rounded-full object-cover" alt="">
                            @else
                                <span class="w-8 h-8 rounded-full bg-primary text-white grid place-items-center text-xs font-semibold">{{ $user->initials() }}</span>
                            @endif
                            <span class="hidden sm:block text-sm font-medium max-w-32 truncate">{{ \Illuminate\Support\Str::before($user->displayName(), ' ') }}</span>
                            <x-lucide name="ChevronDown" class="w-4 h-4 text-gray-400"/>
                        </button>
                        <div x-show="open" @click.outside="open = false" x-transition
                             class="absolute right-0 mt-2 w-48 rounded-xl border border-border bg-white shadow-xl overflow-hidden py-1">
                            <a href="{{ route('portal.profile') }}" class="flex items-center gap-2 px-4 py-2.5 text-sm hover:bg-gray-50"><x-lucide name="User" class="w-4 h-4"/> Profile</a>
                            <a href="{{ route('portal.fees') }}" class="flex items-center gap-2 px-4 py-2.5 text-sm hover:bg-gray-50"><x-lucide name="Wallet" class="w-4 h-4"/> Fees</a>
                            <form method="POST" action="{{ route('portal.logout') }}" class="border-t border-border mt-1">@csrf
                                <button class="w-full flex items-center gap-2 px-4 py-2.5 text-sm text-red-600 hover:bg-red-50"><x-lucide name="LogOut" class="w-4 h-4"/> Log out</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <main class="flex-1 px-4 md:px-6 py-6 md:py-8">
            @if (session('success'))
                <div class="mb-5 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700 flex items-center gap-2"><x-lucide name="CheckCircle" class="w-4 h-4"/> {{ session('success') }}</div>
            @endif
            @if (session('notice'))
                <div class="mb-5 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800 flex items-center gap-2"><x-lucide name="Info" class="w-4 h-4"/> {{ session('notice') }}</div>
            @endif
            {{ $slot }}
        </main>
    </div>

    @livewireScripts
</body>
</html>
