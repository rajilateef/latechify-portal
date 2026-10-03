<x-layouts.portal title="Fees & payments">
    @if (session('success'))
        <div class="mb-5 flex items-start gap-2.5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
            <x-lucide name="CircleCheck" class="w-4 h-4 mt-0.5 shrink-0"/> {{ session('success') }}
        </div>
    @endif
    @if (session('notice'))
        <div class="mb-5 flex items-start gap-2.5 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
            <x-lucide name="Info" class="w-4 h-4 mt-0.5 shrink-0"/> {{ session('notice') }}
        </div>
    @endif
    @error('amount')
        <div class="mb-5 flex items-start gap-2.5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
            <x-lucide name="CircleAlert" class="w-4 h-4 mt-0.5 shrink-0"/> {{ $message }}
        </div>
    @enderror

    <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
        <div>
            <h2 class="text-xl font-bold text-gray-900">Fees &amp; payments</h2>
            <p class="text-sm text-muted-foreground">Your training fees, payments recorded, and outstanding balance.</p>
        </div>
        <div class="rounded-xl border {{ $outstanding > 0 ? 'border-red-200 bg-red-50' : 'border-emerald-200 bg-emerald-50' }} px-4 py-2.5">
            <span class="text-xs {{ $outstanding > 0 ? 'text-red-600' : 'text-emerald-600' }}">Total outstanding</span>
            <span class="ml-2 text-lg font-bold {{ $outstanding > 0 ? 'text-red-700' : 'text-emerald-700' }}">₦{{ number_format($outstanding) }}</span>
        </div>
    </div>

    @forelse ($enrollments as $enrollment)
        @php
            $fee = $enrollment->feeAmount();
            $paid = $enrollment->amountPaid();
            $bal = $enrollment->outstanding();
            $pct = $enrollment->paymentPercent();
        @endphp
        <div class="rounded-2xl border border-border bg-white shadow-sm p-6 mb-5">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <div class="flex items-center gap-2">
                        <h3 class="font-bold text-gray-900">{{ $enrollment->program->name }}</h3>
                        <span class="rounded-full px-2 py-0.5 text-xs font-medium {{ $enrollment->type === 'it_siwes' ? 'bg-amber-100 text-amber-700' : 'bg-gray-100 text-gray-600' }}">{{ $enrollment->typeLabel() }}</span>
                    </div>
                    <p class="text-xs text-muted-foreground mt-0.5">Enrolled {{ $enrollment->started_at?->format('M j, Y') }}@if ($enrollment->fee_note) · {{ $enrollment->fee_note }}@endif</p>
                </div>
                @if ($enrollment->isFullyPaid())
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700"><x-lucide name="CircleCheck" class="w-3.5 h-3.5"/> Fully paid</span>
                @elseif ($fee === 0)
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-500">No fee set</span>
                @else
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-red-100 px-3 py-1 text-xs font-semibold text-red-700">₦{{ number_format($bal) }} due</span>
                @endif
            </div>

            <div class="grid grid-cols-3 gap-3 mt-5">
                <div class="rounded-xl bg-gray-50 p-3.5">
                    <div class="text-xs text-muted-foreground">Total fee</div>
                    <div class="mt-1 font-bold text-gray-900">₦{{ number_format($fee) }}</div>
                </div>
                <div class="rounded-xl bg-gray-50 p-3.5">
                    <div class="text-xs text-muted-foreground">Paid</div>
                    <div class="mt-1 font-bold text-emerald-600">₦{{ number_format($paid) }}</div>
                </div>
                <div class="rounded-xl bg-gray-50 p-3.5">
                    <div class="text-xs text-muted-foreground">Balance</div>
                    <div class="mt-1 font-bold {{ $bal > 0 ? 'text-red-600' : 'text-emerald-600' }}">₦{{ number_format($bal) }}</div>
                </div>
            </div>

            @if ($fee > 0)
                <div class="mt-4 h-2.5 w-full rounded-full bg-gray-100 overflow-hidden">
                    <div class="h-full rounded-full {{ $bal > 0 ? 'bg-amber-400' : 'bg-emerald-500' }}" style="width: {{ $pct }}%"></div>
                </div>
                <div class="text-xs text-muted-foreground mt-1.5">{{ $pct }}% paid</div>
            @endif

            {{-- Payment history --}}
            @if ($enrollment->payments->isNotEmpty())
                <div class="mt-5">
                    <div class="text-xs font-semibold uppercase tracking-wide text-muted-foreground mb-2">Payment history</div>
                    <div class="divide-y divide-border rounded-xl border border-border overflow-hidden">
                        @foreach ($enrollment->payments as $p)
                            <div class="flex items-center gap-3 px-4 py-2.5 text-sm">
                                <x-lucide name="Receipt" class="w-4 h-4 text-primary shrink-0"/>
                                <div class="flex-1 min-w-0">
                                    <span class="font-medium text-gray-900">₦{{ number_format($p->amount) }}</span>
                                    <span class="text-muted-foreground capitalize"> · {{ str_replace('_', ' ', $p->method) }}</span>
                                    @if ($p->reference)<span class="text-xs text-gray-400"> · Ref {{ $p->reference }}</span>@endif
                                </div>
                                <span class="text-xs text-muted-foreground shrink-0 hidden sm:inline">{{ $p->paid_at?->format('M j, Y') }}</span>
                                @if ($p->receipt)
                                    <a href="{{ route('receipts.download', $p->receipt) }}"
                                       class="shrink-0 inline-flex items-center gap-1 rounded-lg border border-border px-2.5 py-1 text-xs font-medium text-primary hover:bg-primary/5">
                                        <x-lucide name="Download" class="w-3.5 h-3.5"/> Receipt
                                    </a>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            @if ($bal > 0)
                @if ($canPayOnline)
                    <div class="mt-5 rounded-xl border border-primary/20 bg-primary/5 p-4" x-data="{ open: false }">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div class="flex items-center gap-2.5">
                                <x-lucide name="CreditCard" class="w-5 h-5 text-primary shrink-0"/>
                                <div>
                                    <div class="text-sm font-semibold text-gray-900">Pay your balance online</div>
                                    <p class="text-xs text-muted-foreground">Card or bank transfer via Monnify. Your receipt is issued instantly.</p>
                                </div>
                            </div>
                            <button type="button" @click="open = !open"
                                    class="inline-flex items-center gap-1.5 rounded-lg bg-primary px-4 py-2.5 text-sm font-semibold text-white hover:bg-primary/90 transition-colors">
                                <x-lucide name="Wallet" class="w-4 h-4"/> <span x-text="open ? 'Cancel' : 'Pay now'"></span>
                            </button>
                        </div>

                        <form x-show="open" x-collapse method="POST" action="{{ route('portal.fees.pay', $enrollment) }}" class="mt-4 flex flex-wrap items-end gap-3">
                            @csrf
                            <div class="flex-1 min-w-[12rem]">
                                <label class="block text-xs font-medium text-gray-700 mb-1.5">Amount to pay (₦)</label>
                                <input type="number" name="amount" min="100" max="{{ $bal }}" step="1" value="{{ $bal }}" required
                                       class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none">
                                <p class="text-xs text-muted-foreground mt-1">Pay it all, or part of it — up to ₦{{ number_format($bal) }}.</p>
                            </div>
                            <button type="submit" class="inline-flex items-center gap-1.5 rounded-lg bg-primary px-5 py-2.5 text-sm font-semibold text-white hover:bg-primary/90 transition-colors">
                                Continue to checkout <x-lucide name="ArrowRight" class="w-4 h-4"/>
                            </button>
                        </form>
                    </div>
                @else
                    <p class="mt-4 text-xs text-muted-foreground flex items-start gap-1.5"><x-lucide name="Info" class="w-3.5 h-3.5 mt-0.5 shrink-0"/> To pay your balance, contact the administrator — recorded payments appear here automatically.</p>
                @endif
            @endif
        </div>
    @empty
        <div class="rounded-2xl border border-border bg-white p-10 text-center shadow-sm">
            <div class="inline-flex w-14 h-14 items-center justify-center rounded-full bg-primary/10 text-primary mb-4"><x-lucide name="Wallet" class="w-7 h-7"/></div>
            <h3 class="text-lg font-bold text-gray-900">No fees yet</h3>
            <p class="text-muted-foreground mt-1">Fee details appear once you're enrolled in a program.</p>
        </div>
    @endforelse
</x-layouts.portal>
