@props(['active'])

@php
$classes = ($active ?? false)
            ? 'inline-flex items-center gap-2 px-1 pt-1 border-b-2 border-brass-light font-display text-sm font-bold leading-5 text-parchment-bright focus:outline-none transition duration-200'
            : 'inline-flex items-center gap-2 px-1 pt-1 border-b-2 border-transparent font-display text-sm font-bold leading-5 text-parchment-shade/80 hover:text-parchment-bright hover:border-brass/60 focus:outline-none transition duration-200';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
