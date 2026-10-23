<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center px-5 py-2.5 rounded-2xl bg-rose-500/15 border border-rose-500/30 text-rose-300 font-display font-semibold text-sm tracking-wide hover:bg-rose-500/25 hover:border-rose-400 hover:text-rose-200 focus:outline-none focus:ring-1 focus:ring-rose-500/40 transition-all duration-300 shadow-sm cursor-pointer disabled:opacity-25']) }}>
    {{ $slot }}
</button>
