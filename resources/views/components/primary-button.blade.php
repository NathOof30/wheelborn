<button {{ $attributes->merge(['type' => 'submit', 'class' => 'btn-brass focus:outline-none focus:ring-2 focus:ring-brass-dark/40']) }}>
    {{ $slot }}
</button>
