@props(['active'])

@php
$classes = ($active ?? false)
            ? 'inline-flex items-center gap-2 px-1 pt-1 border-b-2 border-gold text-sm font-medium leading-5 text-gold-light focus:outline-none transition-all duration-300'
            : 'inline-flex items-center gap-2 px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-slate-500 hover:text-slate-200 hover:border-slate-700 focus:outline-none focus:text-slate-300 transition-all duration-300';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
