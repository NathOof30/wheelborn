@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'border-white/[0.08] bg-white/[0.04] text-slate-200 rounded-xl focus:border-gold/50 focus:ring-1 focus:ring-gold/30 placeholder-slate-600 transition-all duration-300']) }}>
