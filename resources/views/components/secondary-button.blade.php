<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center justify-center px-5 py-2.5 rounded-2xl glass text-slate-300 font-medium text-sm hover:text-white hover:bg-white/[0.08] hover:border-white/[0.15] focus:outline-none focus:ring-1 focus:ring-gold/30 disabled:opacity-25 transition-all duration-300 shadow-sm cursor-pointer']) }}>
    {{ $slot }}
</button>
