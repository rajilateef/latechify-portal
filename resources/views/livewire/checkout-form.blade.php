<div>
    @php $ngn = fn ($n) => '₦'.number_format($n); @endphp

    @if (session('notice'))
        <div class="mb-6 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">{{ session('notice') }}</div>
    @endif

    @if ($this->courses->isEmpty())
        <div class="rounded-2xl border border-border bg-white p-10 text-center shadow-sm">
            <div class="inline-flex w-14 h-14 items-center justify-center rounded-full bg-primary/10 text-primary mb-4"><x-lucide name="GraduationCap" class="w-7 h-7"/></div>
            <h3 class="text-lg font-bold text-gray-900">No courses open right now</h3>
            <p class="text-muted-foreground mt-1">Enrolment opens shortly — <a href="{{ route('contact') }}" class="text-primary hover:underline">get in touch</a> and we'll let you know.</p>
        </div>
    @else
    <form wire:submit="submit" class="space-y-7">
        {{-- Class format — drives which price applies --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Class format</label>
            <div class="grid sm:grid-cols-2 gap-3">
                @foreach ($formatOptions as $value => $label)
                    @php
                        $course = $this->selectedCourse();
                        $price = $course?->payablePriceFor($value);
                        $was = $course?->hasDiscountFor($value) ? $course->priceFor($value) : null;
                    @endphp
                    <label class="cursor-pointer rounded-xl border p-4 transition-colors"
                           :class="$wire.format === '{{ $value }}' ? 'border-primary bg-primary/5 ring-1 ring-primary/30' : 'border-gray-300 hover:border-primary/40'">
                        <div class="flex items-start gap-3">
                            <input type="radio" wire:model.live="format" value="{{ $value }}" class="mt-1 accent-[color:var(--color-primary)]">
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-2 font-semibold text-gray-900">
                                    <x-lucide name="{{ $value === 'online' ? 'Monitor' : 'Building2' }}" class="w-4 h-4 text-primary"/> {{ $label }}
                                </div>
                                <p class="text-xs text-muted-foreground mt-1">
                                    {{ $value === 'online' ? 'Join live classes from anywhere.' : 'Attend in person at our hub.' }}
                                </p>
                                @if ($price !== null)
                                    <div class="mt-2">
                                        <x-price size="sm" :amount="$price" :was="$was" :percent="$course?->discountPercentFor($value)"/>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </label>
                @endforeach
            </div>
            @error('format') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
        </div>

        {{-- Course --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Choose your course</label>
            <div class="grid gap-3 sm:grid-cols-2">
                @foreach ($this->courses as $c)
                    <label class="cursor-pointer rounded-xl border p-4 transition-colors"
                           :class="$wire.course === '{{ $c->slug }}' ? 'border-primary bg-primary/5 ring-1 ring-primary/30' : 'border-gray-300 hover:border-primary/40'">
                        <div class="flex items-start gap-3">
                            <input type="radio" wire:model.live="course" value="{{ $c->slug }}" class="mt-1 accent-[color:var(--color-primary)]">
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-2">
                                    <x-lucide name="{{ $c->icon ?: 'BookOpen' }}" class="w-4 h-4 text-primary shrink-0"/>
                                    <span class="font-semibold text-gray-900">{{ $c->title }}</span>
                                </div>
                                <p class="text-xs text-muted-foreground mt-1 line-clamp-2">{{ $c->description }}</p>
                                <div class="mt-2 flex flex-wrap items-center gap-2 text-xs">
                                    @if ($c->duration)
                                        <span class="inline-flex items-center gap-1 rounded-full bg-gray-100 px-2 py-0.5 text-gray-600"><x-lucide name="Clock" class="w-3 h-3"/> {{ $c->duration }}</span>
                                    @endif
                                    <x-price size="sm"
                                             :amount="$c->payablePriceFor($format)"
                                             :was="$c->hasDiscountFor($format) ? $c->priceFor($format) : null"
                                             :percent="$c->discountPercentFor($format)"/>
                                </div>
                            </div>
                        </div>
                    </label>
                @endforeach
            </div>
            @error('course') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
        </div>

        {{-- Details --}}
        <div class="grid md:grid-cols-2 gap-5">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Full name</label>
                <input type="text" wire:model="full_name" placeholder="John Doe" class="w-full rounded-lg border border-gray-300 px-4 py-2.5 focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none">
                @error('full_name') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Email address</label>
                <input type="email" wire:model="email" placeholder="john@example.com" class="w-full rounded-lg border border-gray-300 px-4 py-2.5 focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none">
                @error('email') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                <p class="text-xs text-muted-foreground mt-1">Your portal sign-in and receipts go to this address.</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Phone number</label>
                <input type="text" wire:model="phone" placeholder="+234 801 234 5678" class="w-full rounded-lg border border-gray-300 px-4 py-2.5 focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none">
                @error('phone') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Anything we should know? <span class="text-muted-foreground font-normal">(optional)</span></label>
                <textarea wire:model="note" rows="1" placeholder="Preferred cohort, goals…" class="w-full rounded-lg border border-gray-300 px-4 py-2.5 focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none"></textarea>
                @error('note') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        {{-- Payment method --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Payment method</label>
            <div class="grid sm:grid-cols-2 gap-3">
                <label class="rounded-xl border p-4 transition-colors {{ $this->onlineAvailable ? 'cursor-pointer' : 'cursor-not-allowed bg-gray-50' }}"
                       :class="$wire.payment_method === 'monnify' ? 'border-primary bg-primary/5 ring-1 ring-primary/30' : 'border-gray-300 {{ $this->onlineAvailable ? 'hover:border-primary/40' : '' }}'">
                    <div class="flex items-start gap-3">
                        <input type="radio" wire:model.live="payment_method" value="monnify" class="mt-1 accent-[color:var(--color-primary)] disabled:opacity-40" @disabled(! $this->onlineAvailable)>
                        <div>
                            <div class="flex flex-wrap items-center gap-2 font-semibold {{ $this->onlineAvailable ? 'text-gray-900' : 'text-gray-400' }}">
                                <span class="flex items-center gap-2"><x-lucide name="CreditCard" class="w-4 h-4 {{ $this->onlineAvailable ? 'text-primary' : 'text-gray-400' }}"/> Pay online</span>
                                @unless ($this->onlineAvailable)
                                    <span class="rounded-full bg-gray-200 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-gray-600">Unavailable</span>
                                @endunless
                            </div>
                            <p class="text-xs mt-1 {{ $this->onlineAvailable ? 'text-muted-foreground' : 'text-gray-400' }}">
                                @if ($this->onlineAvailable)
                                    Card or bank transfer via Monnify secure checkout — confirmed instantly.
                                @else
                                    Card payment is temporarily switched off. Use bank transfer and we'll confirm your place within 24 hours.
                                @endif
                            </p>
                        </div>
                    </div>
                </label>
                <label class="cursor-pointer rounded-xl border p-4 transition-colors"
                       :class="$wire.payment_method === 'transfer' ? 'border-primary bg-primary/5 ring-1 ring-primary/30' : 'border-gray-300 hover:border-primary/40'">
                    <div class="flex items-start gap-3">
                        <input type="radio" wire:model.live="payment_method" value="transfer" class="mt-1 accent-[color:var(--color-primary)]">
                        <div>
                            <div class="flex items-center gap-2 font-semibold text-gray-900"><x-lucide name="Landmark" class="w-4 h-4 text-primary"/> Bank transfer</div>
                            <p class="text-xs text-muted-foreground mt-1">Get our account details and send proof of payment. Confirmed within 24 hours.</p>
                        </div>
                    </div>
                </label>
            </div>
            @error('payment_method') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
        </div>

        {{-- Order summary --}}
        <div class="rounded-xl border border-primary/15 bg-primary/5 p-5">
            <div class="flex items-center justify-between gap-4">
                <div class="min-w-0">
                    <div class="text-sm text-muted-foreground">You're paying for</div>
                    <div class="font-bold text-gray-900 truncate">{{ $this->selectedCourse()?->title ?: 'Select a course' }}</div>
                    <div class="text-xs text-muted-foreground mt-0.5">{{ $formatOptions[$format] ?? '' }} class</div>
                </div>
                <div class="text-right shrink-0">
                    <div class="text-xs text-muted-foreground">Total</div>
                    @if ($this->amount > 0)
                        <x-price size="md" class="justify-end" :amount="$this->amount" :was="$this->listAmount"/>
                        @if ($this->listAmount)
                            <div class="mt-1 text-xs font-semibold text-green-600">
                                You save {{ $ngn($this->listAmount - $this->amount) }}
                            </div>
                        @endif
                    @else
                        <div class="text-2xl font-bold text-primary">Free</div>
                    @endif
                </div>
            </div>
        </div>

        <button type="submit"
                class="btn-shine inline-flex w-full items-center justify-center gap-2 rounded-lg bg-primary px-6 py-3.5 font-semibold text-white transition-colors hover:bg-primary-100 disabled:opacity-70"
                wire:loading.attr="disabled" wire:target="submit">
            <span wire:loading.remove wire:target="submit" class="inline-flex items-center gap-2">
                <span x-data x-show="$wire.payment_method === 'monnify'">Proceed to secure checkout</span>
                <span x-data x-show="$wire.payment_method === 'transfer'">Continue to bank details</span>
                <x-lucide name="ArrowRight" class="w-4 h-4"/>
            </span>
            <span wire:loading wire:target="submit" class="inline-flex items-center gap-2">
                <x-lucide name="LoaderCircle" class="w-4 h-4 animate-spin"/> Processing…
            </span>
        </button>

        <p class="flex items-center justify-center gap-1.5 text-center text-xs text-muted-foreground">
            <x-lucide name="ShieldCheck" class="w-3.5 h-3.5 text-primary"/>
            Payments are processed securely by Monnify. By checking out you agree to our
            <a href="{{ route('terms') }}" class="text-primary hover:underline">terms</a>.
        </p>
    </form>
    @endif
</div>
