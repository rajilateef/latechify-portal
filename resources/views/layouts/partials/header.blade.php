@php
    $menuItems  = \App\Models\MenuItem::visible()->get();
    $navServices = \App\Models\Service::active()->take(6)->get();
    $navCourses = \App\Models\Course::active()->get(['title', 'slug']);
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
                        <div class="relative" x-data="{ o: false }" @mouseenter="o = true" @mouseleave="o = false">
                            <a href="{{ $item->url }}" class="px-3 py-2 text-sm {{ $linkClass }} transition-colors flex items-center gap-1 whitespace-nowrap">{{ $item->label }} <x-lucide name="ChevronDown" class="w-4 h-4"/></a>
                            <div x-show="o" x-transition x-cloak class="absolute left-0 top-full w-56 bg-white shadow-lg rounded-lg border py-2">
                                @foreach ($navServices as $svc)
                                    <a href="{{ route('services') }}#{{ $svc->slug }}" class="block px-4 py-2 text-sm text-gray-600 hover:bg-primary/5 hover:text-primary">{{ $svc->title }}</a>
                                @endforeach
                            </div>
                        </div>
                    @elseif ($item->type === 'courses')
                        <div class="relative" x-data="{ o: false }" @mouseenter="o = true" @mouseleave="o = false">
                            <a href="{{ $item->url }}" class="px-3 py-2 text-sm {{ $linkClass }} transition-colors flex items-center gap-1 whitespace-nowrap">{{ $item->label }} <x-lucide name="ChevronDown" class="w-4 h-4"/></a>
                            <div x-show="o" x-transition x-cloak class="absolute left-0 top-full w-64 bg-white shadow-lg rounded-lg border py-2">
                                @foreach ($navCourses as $c)
                                    <a href="{{ route('courses.show', $c->slug) }}" class="block px-4 py-2 text-sm text-gray-600 hover:bg-primary/5 hover:text-primary">{{ $c->title }}</a>
                                @endforeach
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

                <a href="{{ route('apply') }}" class="btn-shine hidden lg:inline-flex items-center bg-gradient-to-r from-primary to-[#1a3ad4] hover:from-[#1a3ad4] hover:to-primary text-white px-5 py-2.5 rounded-lg shadow-md shadow-primary/20 text-sm font-medium transition-all whitespace-nowrap">
                    {{ setting('cta_label', 'Enrol Today') }}
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
                <a href="{{ $item->url }}" class="py-2 flex items-center gap-1.5 {{ $item->highlight ? 'font-semibold text-primary' : 'text-gray-700 font-medium' }}">
                    @if ($item->icon)<x-lucide :name="$item->icon" class="w-4 h-4"/>@endif {{ $item->label }}
                </a>
            @endforeach
            <div class="grid grid-cols-2 gap-2 mt-2">
                <a href="https://wa.me/{{ $whatsapp }}" target="_blank" rel="noopener" class="inline-flex items-center justify-center gap-2 bg-[#25D366] text-white py-3 rounded-lg text-sm font-medium">
                    <x-social-icon network="whatsapp" class="w-4 h-4"/> WhatsApp
                </a>
                <a href="{{ route('apply') }}" class="inline-flex items-center justify-center text-center bg-gradient-to-r from-primary to-[#1a3ad4] text-white py-3 rounded-lg text-sm font-medium">{{ setting('cta_label', 'Enrol Today') }}</a>
            </div>
        </div>
    </header>

    {{-- Spacer so fixed header doesn't overlap content --}}
    <div class="h-16 md:h-24"></div>
</div>
