@props(['active'])

@php
$classes = ($active ?? false)
            ? 'block w-full ps-3 pe-4 py-2.5 border-l-2 border-brass-light text-start font-display text-sm font-bold text-parchment-bright bg-brass/15 focus:outline-none transition duration-200 rounded-r-md'
            : 'block w-full ps-3 pe-4 py-2.5 border-l-2 border-transparent text-start font-display text-sm font-bold text-parchment-shade hover:text-parchment-bright hover:bg-leather-light/40 hover:border-brass/60 focus:outline-none transition duration-200 rounded-r-md';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
