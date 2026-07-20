@php $wa = setting('whatsapp_number'); @endphp
@if ($wa)
    <a href="https://wa.me/{{ $wa }}" target="_blank" rel="noopener"
       aria-label="Chat on WhatsApp"
       class="fixed bottom-6 right-6 z-40 flex items-center gap-2 bg-[#25D366] hover:bg-[#1ebe5b] text-white pl-3 pr-4 py-3 rounded-full shadow-lg shadow-black/20 transition-all hover:scale-105 group">
        <x-lucide name="MessageCircle" class="w-6 h-6"/>
        <span class="max-w-0 overflow-hidden group-hover:max-w-[120px] transition-all duration-300 whitespace-nowrap text-sm font-medium">Chat with us</span>
    </a>
@endif
