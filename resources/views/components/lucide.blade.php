@props(['name' => 'circle'])
<i data-lucide="{{ lucide_name($name) }}" {{ $attributes->merge(['class' => 'inline-block w-5 h-5']) }}></i>
