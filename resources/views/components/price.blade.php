@props([
    'amount',            // what the customer pays
    'was' => null,       // list price, rendered struck through when it's higher
    'percent' => null,   // optional "save X%" badge
    'size' => 'md',      // sm | md | lg | xl
    'tone' => 'primary', // primary | dark | green
])
@php
    $discounted = $was !== null && $was > $amount;

    $amountClass = [
        'sm' => 'text-lg',
        'md' => 'text-2xl',
        'lg' => 'text-3xl',
        'xl' => 'text-4xl',
    ][$size] ?? 'text-2xl';

    $wasClass = [
        'sm' => 'text-xs',
        'md' => 'text-sm',
        'lg' => 'text-base',
        'xl' => 'text-lg',
    ][$size] ?? 'text-sm';

    $toneClass = [
        'primary' => 'text-primary',
        'dark'    => 'text-gray-900',
        'green'   => 'text-green-600',
    ][$tone] ?? 'text-primary';
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex flex-wrap items-baseline gap-x-2 gap-y-1']) }}>
    <span class="{{ $amountClass }} font-bold {{ $toneClass }} leading-none">&#8358;{{ number_format($amount) }}</span>

    @if ($discounted)
        <span class="{{ $wasClass }} font-medium text-gray-400 line-through decoration-gray-400/70 leading-none">&#8358;{{ number_format($was) }}</span>
        @if ($percent)
            <span class="inline-flex items-center rounded-full bg-green-100 px-2 py-0.5 text-[11px] font-bold uppercase tracking-wide text-green-700 leading-none">
                Save {{ $percent }}%
            </span>
        @endif
    @endif
</span>
