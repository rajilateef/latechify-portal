<div>
    @php $ngn = fn ($n) => '₦'.number_format($n); @endphp

    {{-- Selected package summary --}}
    @if ($this->selectedCourse)
        <div class="bg-gradient-to-r from-primary/10 to-blue-500/10 border border-primary/10 rounded-lg p-5 mb-6 flex items-start gap-3">
            <div class="text-primary mt-0.5"><x-lucide name="Package" class="w-6 h-6"/></div>
            <div>
                <h3 class="font-semibold text-gray-900">Selected Package</h3>
                <p class="text-sm text-muted-foreground mt-1">
                    <span class="font-medium text-gray-700">{{ $this->selectedCourse->title }}</span> —
                    <span class="font-semibold text-primary">{{ $ngn($this->price) }}</span>
                    ({{ $class_format === 'online' ? 'Online Learning' : 'In-Person Learning' }})
                </p>
            </div>
        </div>
    @endif

    <form wire:submit="submit" class="space-y-6">
        <div class="grid md:grid-cols-2 gap-5">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Full Name</label>
                <input type="text" wire:model="full_name" placeholder="John Doe" class="w-full rounded-lg border border-gray-300 px-4 py-2.5 focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none">
                @error('full_name') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Email Address</label>
                <input type="email" wire:model="email" placeholder="john@example.com" class="w-full rounded-lg border border-gray-300 px-4 py-2.5 focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none">
                @error('email') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Phone Number</label>
                <input type="text" wire:model="phone" placeholder="+234 801 234 5678" class="w-full rounded-lg border border-gray-300 px-4 py-2.5 focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none">
                @error('phone') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Course Interest</label>
                <select wire:model.live="course" class="form-select">
                    @foreach ($this->courses as $c)
                        <option value="{{ $c->slug }}">{{ $c->title }}</option>
                    @endforeach
                </select>
                @error('course') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Education Level</label>
                <select wire:model="education" class="form-select">
                    <option value="">Select your education</option>
                    @foreach ($educationOptions as $val => $label)<option value="{{ $val }}">{{ $label }}</option>@endforeach
                </select>
                @error('education') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Programming Experience</label>
                <select wire:model="experience" class="form-select">
                    <option value="">Select your experience level</option>
                    @foreach ($experienceOptions as $val => $label)<option value="{{ $val }}">{{ $label }}</option>@endforeach
                </select>
                @error('experience') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        {{-- Class format --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Preferred Class Format</label>
            <div class="grid sm:grid-cols-2 gap-4">
                <label class="cursor-pointer border rounded-lg p-4 transition-colors {{ $class_format === 'online' ? 'border-primary bg-primary/5' : 'border-gray-300 hover:bg-gray-50' }}">
                    <input type="radio" wire:model.live="class_format" value="online" class="sr-only">
                    <div class="font-medium text-gray-900">Online Classes</div>
                    <div class="text-sm text-muted-foreground">Learn remotely at your own pace</div>
                    <div class="font-semibold text-primary mt-2">{{ $ngn($this->selectedCourse?->price_online ?? 0) }}</div>
                </label>
                <label class="cursor-pointer border rounded-lg p-4 transition-colors {{ $class_format === 'physical' ? 'border-primary bg-primary/5' : 'border-gray-300 hover:bg-gray-50' }}">
                    <input type="radio" wire:model.live="class_format" value="physical" class="sr-only">
                    <div class="font-medium text-gray-900">In-Person Classes</div>
                    <div class="text-sm text-muted-foreground">Attend physical campus sessions</div>
                    <div class="font-semibold text-primary mt-2">{{ $ngn($this->selectedCourse?->price_physical ?? 0) }}</div>
                </label>
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Why do you want to join this program?</label>
            <textarea wire:model="motivation" rows="4" placeholder="Share your goals and what motivated you to apply..." class="w-full rounded-lg border border-gray-300 px-4 py-2.5 focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none"></textarea>
            @error('motivation') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">How did you hear about us?</label>
            <select wire:model="heard_about" class="form-select">
                <option value="">Select an option</option>
                @foreach ($heardOptions as $val => $label)<option value="{{ $val }}">{{ $label }}</option>@endforeach
            </select>
            @error('heard_about') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
        </div>

        {{-- Payment method --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Select Payment Method</label>
            <div class="grid sm:grid-cols-2 gap-4">
                <label class="cursor-pointer border rounded-lg p-4 flex items-center gap-3 transition-colors {{ $payment_method === 'paystack' ? 'border-primary bg-primary/5' : 'border-gray-300 hover:bg-gray-50' }}">
                    <input type="radio" wire:model.live="payment_method" value="paystack" class="sr-only">
                    <span class="text-green-600"><x-lucide name="CreditCard" class="w-6 h-6"/></span>
                    <span><span class="block font-medium text-gray-900">Pay with Paystack</span><span class="text-sm text-muted-foreground">Secure online card payment</span></span>
                </label>
                <label class="cursor-pointer border rounded-lg p-4 flex items-center gap-3 transition-colors {{ $payment_method === 'transfer' ? 'border-primary bg-primary/5' : 'border-gray-300 hover:bg-gray-50' }}">
                    <input type="radio" wire:model.live="payment_method" value="transfer" class="sr-only">
                    <span class="text-blue-600"><x-lucide name="Banknote" class="w-6 h-6"/></span>
                    <span><span class="block font-medium text-gray-900">Bank Transfer</span><span class="text-sm text-muted-foreground">Manual bank transfer</span></span>
                </label>
            </div>
        </div>

        <button type="submit" class="w-full bg-primary hover:bg-primary/90 text-white py-4 rounded-lg text-lg font-medium transition-colors" wire:loading.attr="disabled" wire:target="submit">
            <span wire:loading.remove wire:target="submit">
                {{ $payment_method === 'paystack' ? 'Submit Application & Proceed to Payment' : 'Submit Application & Get Transfer Details' }}
            </span>
            <span wire:loading wire:target="submit">Processing...</span>
        </button>
    </form>
</div>
