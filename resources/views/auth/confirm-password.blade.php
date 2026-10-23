<x-guest-layout>
    <div class="mb-6 text-center">
        <h2 class="font-display font-extrabold text-xl text-white tracking-wide">Zone sécurisée</h2>
        <p class="text-sm text-slate-500 mt-3 leading-relaxed">
            Confirmez votre mot de passe pour continuer.
        </p>
    </div>

    <form method="POST" action="{{ route('password.confirm') }}">
        @csrf

        <!-- Password -->
        <div>
            <label for="password" class="block text-sm font-medium text-slate-400 mb-1.5">Mot de passe</label>
            <input id="password" type="password" name="password" required autocomplete="current-password"
                class="block w-full rounded-xl border border-white/[0.08] bg-white/[0.04] text-slate-200 px-4 py-3 text-sm placeholder-slate-600 focus:border-gold/50 focus:ring-1 focus:ring-gold/30 focus:outline-none transition-all duration-300" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="mt-8">
            <button type="submit" class="w-full px-7 py-3.5 rounded-2xl bg-gradient-to-r from-gold to-gold-light text-cosmos-deep font-display font-bold text-sm tracking-wider uppercase transition-all duration-300 hover:shadow-lg hover:shadow-gold/20 hover:scale-[1.01] active:scale-95">
                Confirmer
            </button>
        </div>
    </form>
</x-guest-layout>
