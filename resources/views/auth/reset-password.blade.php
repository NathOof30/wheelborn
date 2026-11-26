<x-guest-layout>
    <div class="mb-6 text-center space-y-2">
        <h1 class="font-display font-bold text-xl text-ink tracking-wide">Nouveau mot de passe</h1>
        <p class="font-body text-sm italic text-ink-muted leading-relaxed">
            Choisissez un nouveau mot de passe pour votre compte.
        </p>
    </div>

    <form method="POST" action="{{ route('password.store') }}" class="space-y-4">
        @csrf

        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <div>
            <x-input-label for="email" value="Email" />
            <x-text-input
                id="email"
                type="email"
                name="email"
                :value="old('email', $request->email)"
                required
                autofocus
                autocomplete="username"
                class="mt-1.5 block w-full"
            />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="password" value="Nouveau mot de passe" />
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
            <x-input-label for="password_confirmation" value="Confirmer" />
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
            <button type="submit" class="btn-brass w-full">
                Réinitialiser le mot de passe
            </button>
        </div>
    </form>
</x-guest-layout>
