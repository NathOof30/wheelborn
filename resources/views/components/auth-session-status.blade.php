@props(['status'])

@if ($status)
    <div {{ $attributes->merge(['class' => 'font-body font-semibold text-sm text-forest bg-forest/10 border border-forest/40 px-4 py-2.5 rounded-md']) }}>
        {{ $status }}
    </div>
@endif
