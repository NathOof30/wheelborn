@props(['messages'])

@if ($messages)
    <ul {{ $attributes->merge(['class' => 'font-body text-sm font-semibold text-wax space-y-1']) }}>
        @foreach ((array) $messages as $message)
            <li>{{ $message }}</li>
        @endforeach
    </ul>
@endif
