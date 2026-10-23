@props(['active'])

@php
$classes = ($active ?? false)
            ? 'block w-full ps-3 pe-4 py-2.5 border-l-2 border-gold text-start text-base font-medium text-gold-light bg-gold/5 focus:outline-none transition-all duration-300 rounded-r-xl'
            : 'block w-full ps-3 pe-4 py-2.5 border-l-2 border-transparent text-start text-base font-medium text-slate-400 hover:text-white hover:bg-white/[0.04] hover:border-slate-600 focus:outline-none transition-all duration-300 rounded-r-xl';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
