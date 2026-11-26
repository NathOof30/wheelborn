<x-guest-layout>
    <div class="mb-6 text-center space-y-2">
        <h1 class="font-display font-bold text-xl text-ink tracking-wide">Zone sécurisée</h1>
        <p class="font-body text-sm italic text-ink-muted leading-relaxed">
            Confirmez votre mot de passe pour continuer.
        </p>
    </div>

    <form method="POST" action="{{ route('password.confirm') }}" class="space-y-5">
        @csrf

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

        <div class="pt-1">
            <button type="submit" class="btn-wax w-full">
                Confirmer
            </button>
        </div>
    </form>
</x-guest-layout>
