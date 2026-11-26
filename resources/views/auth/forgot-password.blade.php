<x-guest-layout>
    <div class="mb-6 text-center space-y-2">
        <h1 class="font-display font-bold text-xl text-ink tracking-wide">Mot de passe oublié</h1>
        <p class="font-body text-sm italic text-ink-muted leading-relaxed">
            Indiquez votre email et nous vous enverrons un lien pour réinitialiser votre mot de passe.
        </p>
    </div>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
        @csrf

        <div>
            <x-input-label for="email" value="Email" />
            <x-text-input
                id="email"
                type="email"
                name="email"
                :value="old('email')"
                required
                autofocus
                class="mt-1.5 block w-full"
            />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="pt-1">
            <button type="submit" class="btn-brass w-full">
                Envoyer le lien
            </button>
        </div>
    </form>

    <div class="mt-6 pt-5 border-t border-parchment-border text-center">
        <a href="{{ route('login') }}" class="font-body text-sm text-ink-sepia hover:text-wax transition">
            ← Retour à la connexion
        </a>
    </div>
</x-guest-layout>
