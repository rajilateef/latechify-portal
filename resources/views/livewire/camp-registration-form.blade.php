<div>
    @php $ngn = fn ($n) => '₦'.number_format($n); @endphp

    @if (session('notice'))
        <div class="mb-6 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">{{ session('notice') }}</div>
    @endif

    {{-- Fee summary --}}
    <div class="mb-6 flex items-center justify-between gap-4 rounded-xl border border-primary/15 bg-primary/5 p-5">
        <div class="flex items-center gap-3">
            <div class="text-primary"><x-lucide name="Ticket" class="w-6 h-6"/></div>
            <div>
                <div class="text-sm text-muted-foreground">Camp registration fee</div>
                <div class="font-bold text-gray-900">{{ $this->fee > 0 ? $ngn($this->fee) : 'Free' }}</div>
            </div>
        </div>
        <span class="hidden sm:inline-flex items-center gap-1.5 rounded-full bg-white px-3 py-1 text-xs font-semibold text-primary border border-primary/15">
            <x-lucide name="ShieldCheck" class="w-3.5 h-3.5"/> Secure checkout
        </span>
    </div>

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
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Age Group</label>
                <select wire:model="age_group" class="form-select">
                    <option value="">Select age group</option>
                    @foreach ($ageOptions as $val => $label)<option value="{{ $val }}">{{ $label }}</option>@endforeach
                </select>
                @error('age_group') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Preferred Track</label>
                <select wire:model="track" class="form-select">
                    <option value="">Select a track</option>
                    @foreach ($this->tracks as $t)<option value="{{ $t }}">{{ $t }}</option>@endforeach
                </select>
                @error('track') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Coding Experience</label>
                <select wire:model="experience" class="form-select">
                    <option value="">Select your experience</option>
                    @foreach ($experienceOptions as $val => $label)<option value="{{ $val }}">{{ $label }}</option>@endforeach
                </select>
                @error('experience') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        {{-- Attendance mode --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Attendance Mode</label>
            <div class="grid sm:grid-cols-2 gap-3">
                <label class="cursor-pointer rounded-xl border p-4 transition-colors"
                       :class="$wire.mode === 'physical' ? 'border-primary bg-primary/5 ring-1 ring-primary/30' : 'border-gray-300 hover:border-primary/40'">
                    <div class="flex items-start gap-3">
                        <input type="radio" wire:model.live="mode" value="physical" class="mt-1 accent-[color:var(--color-primary)]">
                        <div>
                            <div class="flex items-center gap-2 font-semibold text-gray-900"><x-lucide name="Building2" class="w-4 h-4 text-primary"/> Physical <span class="text-xs font-normal text-muted-foreground">(on-site)</span></div>
                            <p class="text-xs text-muted-foreground mt-1">Attend in person at our hub with your cohort.</p>
                            <div class="mt-2 font-bold text-primary">{{ $this->feePhysical > 0 ? $ngn($this->feePhysical) : 'Free' }}</div>
                        </div>
                    </div>
                </label>
                <label class="cursor-pointer rounded-xl border p-4 transition-colors"
                       :class="$wire.mode === 'virtual' ? 'border-primary bg-primary/5 ring-1 ring-primary/30' : 'border-gray-300 hover:border-primary/40'">
                    <div class="flex items-start gap-3">
                        <input type="radio" wire:model.live="mode" value="virtual" class="mt-1 accent-[color:var(--color-primary)]">
                        <div>
                            <div class="flex items-center gap-2 font-semibold text-gray-900"><x-lucide name="Monitor" class="w-4 h-4 text-primary"/> Virtual <span class="text-xs font-normal text-muted-foreground">(online)</span></div>
                            <p class="text-xs text-muted-foreground mt-1">Join live online from anywhere.</p>
                            <div class="mt-2 font-bold text-primary">{{ $this->feeVirtual > 0 ? $ngn($this->feeVirtual) : 'Free' }}</div>
                        </div>
                    </div>
                </label>
            </div>
            @error('mode') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Anything we should know? <span class="text-muted-foreground font-normal">(optional)</span></label>
            <textarea wire:model="note" rows="3" placeholder="Allergies, accessibility needs, goals for the camp…" class="w-full rounded-lg border border-gray-300 px-4 py-2.5 focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none"></textarea>
            @error('note') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
        </div>

        {{-- Payment method --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Payment Method</label>
            <div class="grid sm:grid-cols-2 gap-3">
                <label class="cursor-pointer rounded-xl border p-4 transition-colors"
                       :class="$wire.payment_method === 'monnify' ? 'border-primary bg-primary/5 ring-1 ring-primary/30' : 'border-gray-300 hover:border-primary/40'"
                       x-data>
                    <div class="flex items-start gap-3">
                        <input type="radio" wire:model.live="payment_method" value="monnify" class="mt-1 accent-[color:var(--color-primary)]">
                        <div>
                            <div class="flex items-center gap-2 font-semibold text-gray-900"><x-lucide name="CreditCard" class="w-4 h-4 text-primary"/> Pay Online</div>
                            <p class="text-xs text-muted-foreground mt-1">Card or bank transfer via Monnify secure checkout — instant confirmation.</p>
                        </div>
                    </div>
                </label>
                <label class="cursor-pointer rounded-xl border p-4 transition-colors"
                       :class="$wire.payment_method === 'manual' ? 'border-primary bg-primary/5 ring-1 ring-primary/30' : 'border-gray-300 hover:border-primary/40'"
                       x-data>
                    <div class="flex items-start gap-3">
                        <input type="radio" wire:model.live="payment_method" value="manual" class="mt-1 accent-[color:var(--color-primary)]">
                        <div>
                            <div class="flex items-center gap-2 font-semibold text-gray-900"><x-lucide name="Landmark" class="w-4 h-4 text-primary"/> Manual / Bank Transfer</div>
                            <p class="text-xs text-muted-foreground mt-1">Get our bank details and send proof of payment. We confirm within 24 hours.</p>
                        </div>
                    </div>
                </label>
            </div>
            @error('payment_method') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
        </div>

        <button type="submit"
                class="btn-shine inline-flex w-full items-center justify-center gap-2 rounded-lg bg-primary px-6 py-3.5 font-semibold text-white transition-colors hover:bg-primary-100 disabled:opacity-70"
                wire:loading.attr="disabled" wire:target="submit">
            <span wire:loading.remove wire:target="submit" class="inline-flex items-center gap-2">
                <span x-data x-show="$wire.payment_method === 'monnify'">Proceed to Payment</span>
                <span x-data x-show="$wire.payment_method === 'manual'">Register &amp; Get Bank Details</span>
                <x-lucide name="ArrowRight" class="w-4 h-4"/>
            </span>
            <span wire:loading wire:target="submit" class="inline-flex items-center gap-2">
                <x-lucide name="LoaderCircle" class="w-4 h-4 animate-spin"/> Processing…
            </span>
        </button>

        <p class="text-center text-xs text-muted-foreground">By registering you agree to our
            <a href="{{ route('terms') }}" class="text-primary hover:underline">terms</a>. Your details are kept private.</p>
    </form>
</div>
