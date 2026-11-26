<button {{ $attributes->merge(['type' => 'submit', 'class' => 'btn-wax focus:outline-none focus:ring-2 focus:ring-wax/40 disabled:opacity-45']) }}>
    {{ $slot }}
</button>
