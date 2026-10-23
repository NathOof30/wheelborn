<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center px-6 py-3 rounded-2xl bg-gradient-to-r from-gold to-gold-light text-cosmos-deep font-display font-bold text-sm tracking-wider uppercase transition-all duration-300 hover:shadow-lg hover:shadow-gold/20 hover:scale-[1.02] active:scale-95 focus:outline-none focus:ring-2 focus:ring-gold/30 focus:ring-offset-0']) }}>
    {{ $slot }}
</button>
