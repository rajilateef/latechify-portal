{{-- Dismissible advert / flier popup shown on first visit (once per day) --}}
<div x-data="{
        show: false,
        key: 'advert_popup_{{ $advert->id }}',
        init() {
            const last = localStorage.getItem(this.key);
            const today = new Date().toDateString();
            if (last !== today) {
                setTimeout(() => { this.show = true; window.renderIcons && window.renderIcons(); }, 1500);
            }
        },
        close() { this.show = false; localStorage.setItem(this.key, new Date().toDateString()); }
     }"
     x-show="show" x-cloak x-transition.opacity
     class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/70"
     @keydown.escape.window="close()">
    <div x-show="show" x-transition
         class="relative w-full max-w-md bg-white rounded-2xl overflow-hidden shadow-2xl"
         @click.outside="close()">
        <button @click="close()" aria-label="Close"
                class="absolute top-3 right-3 z-10 bg-black/40 hover:bg-black/60 text-white rounded-full p-1.5 transition-colors">
            <x-lucide name="X" class="w-5 h-5"/>
        </button>

        <a href="{{ $advert->link_url ? route('advert.click', $advert) : '#' }}"
           @if($advert->link_url && str_starts_with($advert->link_url, 'http')) target="_blank" rel="noopener" @endif>
            <img src="{{ media_url($advert->image) }}" alt="{{ $advert->title }}" class="w-full object-cover max-h-[60vh]">
        </a>

        <div class="p-6 text-center">
            <h3 class="text-xl font-bold text-gray-900">{{ $advert->title }}</h3>
            @if ($advert->description)
                <p class="text-muted-foreground mt-2">{{ $advert->description }}</p>
            @endif
            @if ($advert->link_url)
                <a href="{{ route('advert.click', $advert) }}"
                   @if(str_starts_with($advert->link_url, 'http')) target="_blank" rel="noopener" @endif
                   class="inline-flex items-center gap-2 mt-4 bg-gradient-to-r from-primary to-[#1a3ad4] text-white px-6 py-3 rounded-lg font-medium hover:opacity-90 transition-opacity">
                    Check it out <x-lucide name="ArrowRight" class="w-4 h-4"/>
                </a>
            @endif
        </div>
    </div>
</div>
