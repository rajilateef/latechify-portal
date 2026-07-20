@props(['name' => '', 'logo' => null])

@php $ic = tech_icon($name); @endphp

@if ($logo)
    <img src="{{ media_url($logo) }}" alt="{{ $name }}" {{ $attributes->merge(['class' => 'object-contain']) }}>
@elseif ($ic)
    <svg viewBox="0 0 24 24" role="img" aria-label="{{ $name }}" fill="currentColor"
         style="color: {{ $ic['hex'] }}" {{ $attributes->merge(['class' => 'shrink-0']) }}>
        <path d="{{ $ic['path'] }}"/>
    </svg>
@else
    <span {{ $attributes->merge(['class' => 'shrink-0 inline-flex items-center justify-center rounded-md bg-gradient-to-br from-primary to-primary-0 text-white text-[10px] font-bold leading-none']) }}>
        {{ strtoupper(mb_substr($name, 0, 1)) }}
    </span>
@endif
