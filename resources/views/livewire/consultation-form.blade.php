<div>
    @if ($sent)
        <div class="text-center py-10">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-green-100 text-green-600 mb-4">
                <x-lucide name="CheckCircle" class="w-8 h-8"/>
            </div>
            <h3 class="text-2xl font-bold text-gray-900 mb-2">Consultation Scheduled!</h3>
            <p class="text-muted-foreground">We'll contact you soon to confirm your consultation. Thank you!</p>
            <button wire:click="$set('sent', false)" class="mt-6 text-primary font-medium hover:underline">Book another session</button>
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
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Email</label>
                    <input type="email" wire:model="email" placeholder="john@example.com" class="w-full rounded-lg border border-gray-300 px-4 py-2.5 focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none">
                    @error('email') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Phone Number</label>
                    <input type="text" wire:model="phone" placeholder="+234 801 234 5678" class="w-full rounded-lg border border-gray-300 px-4 py-2.5 focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none">
                    @error('phone') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Service Interest</label>
                    <select wire:model="service" class="form-select">
                        <option value="">Select a service</option>
                        @foreach ($services as $val => $label)<option value="{{ $val }}">{{ $label }}</option>@endforeach
                    </select>
                    @error('service') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Preferred Date</label>
                    <input type="date" wire:model="preferred_date" min="{{ now()->toDateString() }}" class="w-full rounded-lg border border-gray-300 px-4 py-2.5 focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none">
                    @error('preferred_date') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Preferred Time</label>
                    <select wire:model="preferred_time" class="form-select">
                        <option value="">Select a time</option>
                        @foreach ($times as $t)<option value="{{ $t }}">{{ $t }}</option>@endforeach
                    </select>
                    @error('preferred_time') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Tell us about your project</label>
                <textarea wire:model="message" rows="4" placeholder="Please share details about your project, goals, or specific questions you might have." class="w-full rounded-lg border border-gray-300 px-4 py-2.5 focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none"></textarea>
                @error('message') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
            </div>
            <button type="submit" class="w-full bg-primary hover:bg-primary/90 text-white py-3.5 rounded-lg font-medium transition-colors" wire:loading.attr="disabled" wire:target="submit">
                <span wire:loading.remove wire:target="submit">Schedule Consultation</span>
                <span wire:loading wire:target="submit">Scheduling...</span>
            </button>
        </form>
    @endif
</div>
