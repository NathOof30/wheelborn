<x-guest-layout>
    {{-- Titre --}}
    <div class="mb-8 text-center">
        <h2 class="font-display font-extrabold text-xl text-white tracking-wide">Créer un compte</h2>
        <p class="text-sm text-slate-500 mt-2">Rejoignez l'arène et forgez votre légende.</p>
    </div>

    <form method="POST" action="{{ route('register') }}">
        @csrf

        <!-- Name -->
        <div>
            <label for="name" class="block text-sm font-medium text-slate-400 mb-1.5">Pseudo de combattant</label>
            <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name"
                class="block w-full rounded-xl border border-white/[0.08] bg-white/[0.04] text-slate-200 px-4 py-3 text-sm placeholder-slate-600 focus:border-gold/50 focus:ring-1 focus:ring-gold/30 focus:outline-none transition-all duration-300"
                placeholder="Votre nom dans l'arène" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Email Address -->
        <div class="mt-5">
            <label for="email" class="block text-sm font-medium text-slate-400 mb-1.5">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="username"
                class="block w-full rounded-xl border border-white/[0.08] bg-white/[0.04] text-slate-200 px-4 py-3 text-sm placeholder-slate-600 focus:border-gold/50 focus:ring-1 focus:ring-gold/30 focus:outline-none transition-all duration-300" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-5">
            <label for="password" class="block text-sm font-medium text-slate-400 mb-1.5">Mot de passe</label>
            <input id="password" type="password" name="password" required autocomplete="new-password"
                class="block w-full rounded-xl border border-white/[0.08] bg-white/[0.04] text-slate-200 px-4 py-3 text-sm placeholder-slate-600 focus:border-gold/50 focus:ring-1 focus:ring-gold/30 focus:outline-none transition-all duration-300" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div class="mt-5">
            <label for="password_confirmation" class="block text-sm font-medium text-slate-400 mb-1.5">Confirmer le mot de passe</label>
            <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password"
                class="block w-full rounded-xl border border-white/[0.08] bg-white/[0.04] text-slate-200 px-4 py-3 text-sm placeholder-slate-600 focus:border-gold/50 focus:ring-1 focus:ring-gold/30 focus:outline-none transition-all duration-300" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="mt-8">
            <button type="submit" class="w-full px-7 py-3.5 rounded-2xl bg-gradient-to-r from-gold to-gold-light text-cosmos-deep font-display font-bold text-sm tracking-wider uppercase transition-all duration-300 hover:shadow-lg hover:shadow-gold/20 hover:scale-[1.01] active:scale-95">
                Forger mon destin
            </button>
        </div>
    </form>

    {{-- Lien vers connexion --}}
    <div class="mt-8 pt-6 border-t border-white/[0.04] text-center">
        <a href="{{ route('login') }}" class="text-sm text-slate-500 hover:text-gold-light transition-colors duration-300">
            Déjà inscrit ? <span class="font-medium text-slate-300">Connexion</span>
        </a>
    </div>
</x-guest-layout>
