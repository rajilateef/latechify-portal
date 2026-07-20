<div>
    @if ($sent)
        <div class="text-center py-10">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-green-100 text-green-600 mb-4">
                <x-lucide name="CheckCircle" class="w-8 h-8"/>
            </div>
            <h3 class="text-2xl font-bold text-gray-900 mb-2">Message Sent Successfully!</h3>
            <p class="text-muted-foreground">Thank you for reaching out to us. We've received your message and will get back to you as soon as possible.</p>
            <button wire:click="$set('sent', false)" class="mt-6 text-primary font-medium hover:underline">Send another message</button>
        </div>
    @else
        <form wire:submit="submit" class="space-y-5">
            <div class="grid sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Full Name</label>
                    <input type="text" wire:model="name" placeholder="John Doe" class="w-full rounded-lg border border-gray-300 px-4 py-2.5 focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none">
                    @error('name') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Email Address</label>
                    <input type="email" wire:model="email" placeholder="john@example.com" class="w-full rounded-lg border border-gray-300 px-4 py-2.5 focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none">
                    @error('email') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
            <div class="grid sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Phone Number</label>
                    <input type="text" wire:model="phone" placeholder="+234 701 234 5678" class="w-full rounded-lg border border-gray-300 px-4 py-2.5 focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Subject</label>
                    <select wire:model="subject" class="form-select">
                        <option value="">Select a subject</option>
                        @foreach ($subjects as $val => $label)
                            <option value="{{ $val }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Message</label>
                <textarea wire:model="message" rows="5" placeholder="How can we help you?" class="w-full rounded-lg border border-gray-300 px-4 py-2.5 focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none"></textarea>
                @error('message') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
            </div>
            <button type="submit" class="w-full bg-primary-0 hover:bg-primary-0/90 text-white py-3 rounded-lg font-medium transition-colors flex items-center justify-center gap-2" wire:loading.attr="disabled" wire:target="submit">
                <span wire:loading.remove wire:target="submit" class="flex items-center gap-2">Send Message <x-lucide name="Send" class="w-4 h-4"/></span>
                <span wire:loading wire:target="submit">Sending...</span>
            </button>
        </form>
    @endif
</div>
