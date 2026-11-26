@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'border border-parchment-border bg-parchment-bright text-ink rounded-md px-3.5 py-2.5 font-body text-sm placeholder-ink-muted/70 shadow-inner focus:border-brass-dark focus:ring-1 focus:ring-brass-dark focus:outline-none transition']) }}>
