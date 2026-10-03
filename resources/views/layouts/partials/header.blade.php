@php
    $menuItems  = \App\Models\MenuItem::visible()->get();
    $navServices = \App\Models\Service::active()->take(6)->get(['title', 'slug', 'icon', 'description']);
    $navCourses = \App\Models\Course::active()->get(['title', 'slug', 'icon', 'subtitle', 'level', 'duration']);
    $socials = array_filter([
        'facebook'  => setting('facebook_url'),
        'twitter'   => setting('twitter_url'),
        'instagram' => setting('instagram_url'),
        'linkedin'  => setting('linkedin_url'),
    ]);
    $whatsapp = setting('whatsapp_number');
@endphp

<div x-data="{ scrolled: false }"
     x-init="
        scrolled = window.scrollY > 20;
        let ticking = false;
        const sync = () => { ticking = false; const s = window.scrollY > 20; if (s !== scrolled) scrolled = s; };
        window.addEventListener('scroll', () => { if (!ticking) { ticking = true; requestAnimationFrame(sync); } }, { passive: true });
     ">
    {{-- Top contact bar --}}
    <div class="fixed top-0 inset-x-0 z-[60] bg-primary text-white text-xs transition-all duration-300 hidden md:block"
         :class="scrolled ? '-translate-y-full opacity-0' : 'translate-y-0 opacity-100'">
        <div class="container-custom flex items-center justify-between py-2.5">
            <div class="flex items-center gap-3 whitespace-nowrap overflow-hidden">
                <span class="flex items-center gap-1.5"><x-lucide name="MapPin" class="w-3.5 h-3.5 shrink-0"/> {{ setting('topbar_address') }}</span>
                <span class="w-px h-4 bg-white/30"></span>
                <a href="tel:{{ setting('contact_phone') }}" class="flex items-center gap-1.5 hover:text-primary-0 transition-colors"><x-lucide name="Phone" class="w-3.5 h-3.5"/> {{ setting('contact_phone') }}</a>
                <span class="w-px h-4 bg-white/30"></span>
                <a href="https://wa.me/{{ $whatsapp }}" target="_blank" rel="noopener" class="flex items-center gap-1.5 hover:text-primary-0 transition-colors"><x-lucide name="MessageCircle" class="w-3.5 h-3.5"/> WhatsApp</a>
                <span class="w-px h-4 bg-white/30"></span>
                <a href="mailto:{{ setting('contact_email') }}" class="flex items-center gap-1.5 hover:text-primary-0 transition-colors"><x-lucide name="Mail" class="w-3.5 h-3.5"/> {{ setting('contact_email') }}</a>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('portal.login') }}" class="flex items-center gap-1.5 hover:text-primary-0 transition-colors">
                    <x-lucide name="GraduationCap" class="w-3.5 h-3.5"/> Student Login
                </a>
                <span class="w-px h-4 bg-white/30"></span>
                <a href="{{ route('verify-certificate') }}" class="flex items-center gap-1.5 hover:text-primary-0 transition-colors">
                    <x-lucide name="BadgeCheck" class="w-3.5 h-3.5"/> Verify Certificate
                </a>
                <span class="w-px h-4 bg-white/30"></span>
                <span class="text-white/60">Follow us</span>
                @foreach ($socials as $network => $url)
                    <a href="{{ $url }}" target="_blank" rel="noopener" aria-label="{{ $network }}" class="hover:text-primary-0 transition-colors">
                        <x-social-icon :network="$network" class="w-3.5 h-3.5"/>
                    </a>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Main navbar --}}
    <header x-data="{ open: false }"
            class="fixed inset-x-0 z-50 bg-white transition-all duration-300"
            :class="scrolled ? 'top-0 shadow-md py-3' : 'md:top-9 top-0 py-4'">
        <div class="container-custom flex items-center justify-between gap-4">
            {{-- Logo (left) --}}
            <a href="{{ route('home') }}" class="flex items-center shrink-0">
                <img src="{{ media_url(setting('logo'), 'assets/imgs/latechify_logo.png') }}" alt="{{ setting('site_name') }}" class="h-12 md:h-14 w-auto">
            </a>

            {{-- Menu (desktop) — managed from Admin → Settings → Navbar Menu --}}
            <nav class="hidden lg:flex items-center gap-1">
                @foreach ($menuItems as $item)
                    @php $linkClass = $item->highlight ? 'font-semibold text-primary hover:text-primary-100' : 'font-medium text-gray-700 hover:text-primary'; @endphp
                    @if ($item->type === 'services')
                        <div class="relative" x-data="{ o: false }" @mouseenter="o = true" @mouseleave="o = false" @keydown.escape.window="o = false">
                            <a href="{{ $item->url }}" :class="o ? 'text-primary' : ''" class="px-3 py-2 text-sm {{ $linkClass }} transition-colors flex items-center gap-1 whitespace-nowrap">
                                {{ $item->label }}
                                <x-lucide name="ChevronDown" class="w-4 h-4 transition-transform duration-200" ::class="o && 'rotate-180'"/>
                            </a>
                            {{-- pt-3 bridges the gap so the panel doesn't flicker on the way down --}}
                            <div x-show="o" x-cloak
                                 x-transition:enter="transition ease-out duration-200"
                                 x-transition:enter-start="opacity-0 -translate-y-1 scale-[.98]"
                                 x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                                 x-transition:leave="transition ease-in duration-150"
                                 x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                                 x-transition:leave-end="opacity-0 -translate-y-1 scale-[.98]"
                                 class="absolute left-1/2 -translate-x-1/2 top-full pt-3 z-50 origin-top">
                                <div class="nav-panel w-[22rem] p-2">
                                    <span class="nav-panel-caret"></span>
                                    @foreach ($navServices as $svc)
                                        <a href="{{ route('services') }}#{{ $svc->slug }}" class="nav-panel-item group">
                                            <span class="nav-panel-icon"><x-lucide :name="$svc->icon ?: 'Sparkles'" class="w-[18px] h-[18px]"/></span>
                                            <span class="min-w-0 flex-1">
                                                <span class="nav-panel-title">{{ $svc->title }}</span>
                                                @if ($svc->description)<span class="nav-panel-sub">{{ \Illuminate\Support\Str::limit(strip_tags($svc->description), 58) }}</span>@endif
                                            </span>
                                            <x-lucide name="ChevronRight" class="nav-panel-chevron"/>
                                        </a>
                                    @endforeach
                                    <a href="{{ route('services') }}" class="nav-panel-footer group">
                                        View all services <x-lucide name="ArrowRight" class="w-3.5 h-3.5 transition-transform group-hover:translate-x-0.5"/>
                                    </a>
                                </div>
                            </div>
                        </div>
                    @elseif ($item->type === 'courses')
                        <div class="relative" x-data="{ o: false }" @mouseenter="o = true" @mouseleave="o = false" @keydown.escape.window="o = false">
                            <a href="{{ $item->url }}" :class="o ? 'text-primary' : ''" class="px-3 py-2 text-sm {{ $linkClass }} transition-colors flex items-center gap-1 whitespace-nowrap">
                                {{ $item->label }}
                                <x-lucide name="ChevronDown" class="w-4 h-4 transition-transform duration-200" ::class="o && 'rotate-180'"/>
                            </a>
                            <div x-show="o" x-cloak
                                 x-transition:enter="transition ease-out duration-200"
                                 x-transition:enter-start="opacity-0 -translate-y-1 scale-[.98]"
                                 x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                                 x-transition:leave="transition ease-in duration-150"
                                 x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                                 x-transition:leave-end="opacity-0 -translate-y-1 scale-[.98]"
                                 class="absolute left-1/2 -translate-x-1/2 top-full pt-3 z-50 origin-top">
                                <div class="nav-panel w-[40rem] p-2">
                                    <span class="nav-panel-caret"></span>
                                    <div class="grid grid-cols-2 gap-0.5">
                                        @foreach ($navCourses as $c)
                                            <a href="{{ route('courses.show', $c->slug) }}" class="nav-panel-item group">
                                                <span class="nav-panel-icon"><x-lucide :name="$c->icon ?: 'BookOpen'" class="w-[18px] h-[18px]"/></span>
                                                <span class="min-w-0 flex-1">
                                                    <span class="nav-panel-title">{{ $c->title }}</span>
                                                    <span class="nav-panel-sub">{{ collect([$c->level, $c->duration])->filter()->implode(' · ') ?: \Illuminate\Support\Str::limit($c->subtitle, 46) }}</span>
                                                </span>
                                                <x-lucide name="ChevronRight" class="nav-panel-chevron"/>
                                            </a>
                                        @endforeach
                                    </div>
                                    <a href="{{ route('courses.index') }}" class="nav-panel-footer group">
                                        Browse all courses <x-lucide name="ArrowRight" class="w-3.5 h-3.5 transition-transform group-hover:translate-x-0.5"/>
                                    </a>
                                </div>
                            </div>
                        </div>
                    @else
                        <a href="{{ $item->url }}" class="px-3 py-2 text-sm {{ $linkClass }} transition-colors flex items-center gap-1.5 whitespace-nowrap">
                            @if ($item->icon)<x-lucide :name="$item->icon" class="w-4 h-4"/>@endif {{ $item->label }}
                        </a>
                    @endif
                @endforeach
            </nav>

            {{-- Right cluster: WhatsApp + Enrol (desktop) + mobile toggle --}}
            <div class="flex items-center gap-2 shrink-0">
                <a href="https://wa.me/{{ $whatsapp }}" target="_blank" rel="noopener"
                   class="hidden lg:inline-flex items-center gap-2 text-sm font-medium text-gray-700 hover:text-[#1ebe5b] transition-colors whitespace-nowrap">
                    <x-social-icon network="whatsapp" class="w-5 h-5 text-[#25D366] shrink-0"/>
                    <span class="hidden xl:inline">Let's chat</span>
                </a>

                <a href="{{ route('portal.login') }}"
                   class="hidden lg:inline-flex items-center gap-1.5 border border-gray-200 hover:border-primary/40 hover:text-primary text-gray-700 px-4 py-2.5 rounded-lg text-sm font-medium transition-colors whitespace-nowrap">
                    <x-lucide name="GraduationCap" class="w-4 h-4"/> Student Login
                </a>

                <a href="{{ route('checkout') }}" class="btn-shine hidden lg:inline-flex items-center gap-1.5 bg-gradient-to-r from-primary to-[#1a3ad4] hover:from-[#1a3ad4] hover:to-primary text-white px-5 py-2.5 rounded-lg shadow-md shadow-primary/20 text-sm font-medium transition-all whitespace-nowrap">
                    <x-lucide name="ShoppingCart" class="w-4 h-4"/> {{ setting('cta_label', 'Checkout') }}
                </a>

                {{-- Mobile toggle --}}
                <button @click="open = !open" class="lg:hidden text-gray-700" aria-label="Toggle menu">
                    <x-lucide name="Menu" x-show="!open" class="w-6 h-6"/>
                    <x-lucide name="X" x-show="open" x-cloak class="w-6 h-6"/>
                </button>
            </div>
        </div>

        {{-- Mobile menu --}}
        <div x-show="open" x-cloak x-transition class="lg:hidden absolute top-full inset-x-0 bg-white shadow-lg border-t py-4 px-6 flex flex-col gap-1 max-h-[80vh] overflow-y-auto">
            @foreach ($menuItems as $item)
                @if (in_array($item->type, ['services', 'courses']))
                    @php $children = $item->type === 'services' ? $navServices : $navCourses; @endphp
                    <div x-data="{ sub: false }" class="border-b border-gray-100 last:border-0">
                        <button type="button" @click="sub = !sub"
                                class="w-full py-3 flex items-center gap-1.5 text-left {{ $item->highlight ? 'font-semibold text-primary' : 'text-gray-700 font-medium' }}">
                            @if ($item->icon)<x-lucide :name="$item->icon" class="w-4 h-4"/>@endif
                            <span>{{ $item->label }}</span>
                            <x-lucide name="ChevronDown" class="w-4 h-4 ml-auto text-gray-400 transition-transform duration-200" ::class="sub && 'rotate-180'"/>
                        </button>
                        <div x-show="sub" x-collapse x-cloak class="pb-2">
                            @foreach ($children as $child)
                                <a href="{{ $item->type === 'services' ? route('services').'#'.$child->slug : route('courses.show', $child->slug) }}"
                                   class="flex items-center gap-3 py-2 pl-1 pr-2 rounded-lg hover:bg-primary/5">
                                    <span class="grid place-items-center w-8 h-8 shrink-0 rounded-lg bg-primary/8 text-primary">
                                        <x-lucide :name="$child->icon ?: ($item->type === 'services' ? 'Sparkles' : 'BookOpen')" class="w-4 h-4"/>
                                    </span>
                                    <span class="min-w-0">
                                        <span class="block text-sm font-medium text-gray-800 truncate">{{ $child->title }}</span>
                                        @if ($item->type === 'courses' && ($child->level || $child->duration))
                                            <span class="block text-xs text-gray-500">{{ collect([$child->level, $child->duration])->filter()->implode(' · ') }}</span>
                                        @endif
                                    </span>
                                </a>
                            @endforeach
                            <a href="{{ $item->url }}" class="block pl-12 py-2 text-xs font-semibold text-primary">
                                {{ $item->type === 'services' ? 'View all services' : 'Browse all courses' }} →
                            </a>
                        </div>
                    </div>
                @else
                    <a href="{{ $item->url }}" class="py-3 flex items-center gap-1.5 border-b border-gray-100 last:border-0 {{ $item->highlight ? 'font-semibold text-primary' : 'text-gray-700 font-medium' }}">
                        @if ($item->icon)<x-lucide :name="$item->icon" class="w-4 h-4"/>@endif {{ $item->label }}
                    </a>
                @endif
            @endforeach
            <a href="{{ route('portal.login') }}" class="mt-2 inline-flex items-center justify-center gap-2 border border-gray-200 text-gray-700 py-3 rounded-lg text-sm font-medium">
                <x-lucide name="GraduationCap" class="w-4 h-4"/> Student Login
            </a>
            <div class="grid grid-cols-2 gap-2 mt-2">
                <a href="https://wa.me/{{ $whatsapp }}" target="_blank" rel="noopener" class="inline-flex items-center justify-center gap-2 bg-[#25D366] text-white py-3 rounded-lg text-sm font-medium">
                    <x-social-icon network="whatsapp" class="w-4 h-4"/> WhatsApp
                </a>
                <a href="{{ route('checkout') }}" class="inline-flex items-center justify-center gap-1.5 text-center bg-gradient-to-r from-primary to-[#1a3ad4] text-white py-3 rounded-lg text-sm font-medium"><x-lucide name="ShoppingCart" class="w-4 h-4"/> {{ setting('cta_label', 'Checkout') }}</a>
            </div>
        </div>
    </header>

    {{-- Spacer so fixed header doesn't overlap content --}}
    <div class="h-16 md:h-24"></div>
</div>
