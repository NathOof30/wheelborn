@props(['status'])

@if ($status)
    <div {{ $attributes->merge(['class' => 'font-medium text-sm text-emerald-300 bg-emerald-500/10 border border-emerald-500/20 px-4 py-2.5 rounded-xl backdrop-blur-sm']) }}>
        {{ $status }}
    </div>
@endif
