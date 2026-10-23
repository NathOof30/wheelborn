<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    {{-- Titre --}}
    <div class="mb-8 text-center">
        <h2 class="font-display font-extrabold text-xl text-white tracking-wide">Connexion</h2>
        <p class="text-sm text-slate-500 mt-2">Retrouvez vos combattants et votre rang.</p>
    </div>

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Email Address -->
        <div>
            <label for="email" class="block text-sm font-medium text-slate-400 mb-1.5">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                class="block w-full rounded-xl border border-white/[0.08] bg-white/[0.04] text-slate-200 px-4 py-3 text-sm placeholder-slate-600 focus:border-gold/50 focus:ring-1 focus:ring-gold/30 focus:outline-none transition-all duration-300" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-5">
            <label for="password" class="block text-sm font-medium text-slate-400 mb-1.5">Mot de passe</label>
            <input id="password" type="password" name="password" required autocomplete="current-password"
                class="block w-full rounded-xl border border-white/[0.08] bg-white/[0.04] text-slate-200 px-4 py-3 text-sm placeholder-slate-600 focus:border-gold/50 focus:ring-1 focus:ring-gold/30 focus:outline-none transition-all duration-300" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="block mt-5">
            <label for="remember_me" class="inline-flex items-center cursor-pointer">
                <input id="remember_me" type="checkbox" class="rounded border-white/10 bg-white/[0.04] text-gold shadow-sm focus:ring-gold/30 focus:ring-offset-0" name="remember">
                <span class="ms-2 text-sm text-slate-500">Se souvenir de moi</span>
            </label>
        </div>

        <div class="flex items-center justify-between mt-8">
            @if (Route::has('password.request'))
                <a class="text-sm text-slate-500 hover:text-slate-300 transition-colors duration-300" href="{{ route('password.request') }}">
                    Mot de passe oublié ?
                </a>
            @endif

            <button type="submit" class="px-7 py-3 rounded-2xl bg-gradient-to-r from-gold to-gold-light text-cosmos-deep font-display font-bold text-sm tracking-wider uppercase transition-all duration-300 hover:shadow-lg hover:shadow-gold/20 hover:scale-[1.02] active:scale-95">
                Connexion
            </button>
        </div>
    </form>

    {{-- Lien vers inscription --}}
    <div class="mt-8 pt-6 border-t border-white/[0.04] text-center">
        <a href="{{ route('register') }}" class="text-sm text-slate-500 hover:text-gold-light transition-colors duration-300">
            Pas encore de compte ? <span class="font-medium text-slate-300">S'inscrire</span>
        </a>
    </div>
</x-guest-layout>
