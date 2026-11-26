@props(['value'])

<label {{ $attributes->merge(['class' => 'block font-display text-xs font-bold uppercase tracking-wider text-ink-sepia']) }}>
    {{ $value ?? $slot }}
</label>
