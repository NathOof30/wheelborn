<button {{ $attributes->merge(['type' => 'button', 'class' => 'btn-cartouche focus:outline-none focus:ring-2 focus:ring-brass-dark/40 disabled:opacity-45']) }}>
    {{ $slot }}
</button>
