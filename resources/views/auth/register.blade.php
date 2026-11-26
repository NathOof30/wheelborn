<x-guest-layout>
    <div class="mb-6 text-center space-y-1">
        <h1 class="font-display font-bold text-xl text-ink tracking-wide">Créer un compte</h1>
        <p class="font-body text-sm italic text-ink-muted">Rejoignez l'arène et forgez votre légende.</p>
    </div>

    <form method="POST" action="{{ route('register') }}" class="space-y-4">
        @csrf

        <div>
            <x-input-label for="name" value="Pseudo de combattant" />
            <x-text-input
                id="name"
                type="text"
                name="name"
                :value="old('name')"
                required
                autofocus
                autocomplete="name"
                class="mt-1.5 block w-full"
                placeholder="Votre nom dans l'arène"
            />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="email" value="Email" />
            <x-text-input
                id="email"
                type="email"
                name="email"
                :value="old('email')"
                required
                autocomplete="username"
                class="mt-1.5 block w-full"
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
                autocomplete="new-password"
                class="mt-1.5 block w-full"
            />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="password_confirmation" value="Confirmer le mot de passe" />
            <x-text-input
                id="password_confirmation"
                type="password"
                name="password_confirmation"
                required
                autocomplete="new-password"
                class="mt-1.5 block w-full"
            />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="pt-3">
            <button type="submit" class="btn-wax w-full">
                Forger mon destin
            </button>
        </div>
    </form>

    <div class="mt-6 pt-5 border-t border-parchment-border text-center">
        <a href="{{ route('login') }}" class="font-body text-sm text-ink-sepia hover:text-wax transition">
            Déjà inscrit ? <span class="font-display font-bold text-ink underline-offset-2 hover:underline">Connexion</span>
        </a>
    </div>
</x-guest-layout>
