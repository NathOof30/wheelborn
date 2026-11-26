<x-guest-layout>
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <div class="mb-6 text-center space-y-1">
        <h1 class="font-display font-bold text-xl text-ink tracking-wide">Connexion</h1>
        <p class="font-body text-sm italic text-ink-muted">Retrouvez vos combattants et votre rang.</p>
    </div>

    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf

        <div>
            <x-input-label for="email" value="Email ou Pseudo" />
            <x-text-input
                id="email"
                type="text"
                name="email"
                :value="old('email')"
                required
                autofocus
                autocomplete="username"
                class="mt-1.5 block w-full"
                placeholder="ex: hero@wheelborn.test ou NathOof"
            />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="password" value="Mot de passe" />
            <x-text-input
                id="password"
                type="password"
                name="password"
                required
                autocomplete="current-password"
                class="mt-1.5 block w-full"
            />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div>
            <label for="remember_me" class="inline-flex items-center cursor-pointer">
                <input
                    id="remember_me"
                    type="checkbox"
                    name="remember"
                    class="rounded border-parchment-border bg-parchment-bright text-wax shadow-sm focus:ring-brass-dark"
                >
                <span class="ms-2 font-body text-sm text-ink-sepia">Se souvenir de moi</span>
            </label>
        </div>

        <div class="flex flex-wrap items-center justify-between gap-3 pt-2">
            @if (Route::has('password.request'))
                <a class="font-body text-sm text-ink-muted hover:text-wax underline-offset-2 hover:underline transition" href="{{ route('password.request') }}">
                    Mot de passe oublié ?
                </a>
            @endif

            <button type="submit" class="btn-wax">
                Connexion
            </button>
        </div>
    </form>

    <div class="mt-6 pt-5 border-t border-parchment-border text-center">
        <a href="{{ route('register') }}" class="font-body text-sm text-ink-sepia hover:text-wax transition">
            Pas encore de compte ? <span class="font-display font-bold text-ink underline-offset-2 hover:underline">S'inscrire</span>
        </a>
    </div>
</x-guest-layout>
