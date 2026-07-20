<div>
    @if ($sent)
        <p class="text-green-600 font-medium flex items-center justify-center gap-2"><x-lucide name="CheckCircle" class="w-5 h-5"/> You're subscribed. Thank you!</p>
    @else
        <form wire:submit="submit" class="flex flex-col sm:flex-row gap-3 max-w-lg mx-auto">
            <input type="email" wire:model="email" placeholder="Your email address" class="flex-1 rounded-lg border border-gray-300 px-4 py-3 focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none">
            <button type="submit" class="bg-primary hover:bg-primary/90 text-white px-6 py-3 rounded-lg font-medium transition-colors whitespace-nowrap" wire:loading.attr="disabled" wire:target="submit">
                <span wire:loading.remove wire:target="submit">Subscribe</span>
                <span wire:loading wire:target="submit">…</span>
            </button>
        </form>
        @error('email') <p class="text-red-500 text-sm mt-2 text-center">{{ $message }}</p> @enderror
    @endif
</div>
