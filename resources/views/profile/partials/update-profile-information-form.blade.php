<section>
    <header>
        <h2 class="font-display text-lg font-bold text-ink tracking-wide">
            {{ __('Informations du Profil') }}
        </h2>

        <p class="mt-1 font-body text-sm italic text-ink-muted">
            {{ __('Mettez à jour les informations de votre compte et votre adresse e-mail.') }}
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-5">
        @csrf
        @method('patch')

        <div>
            <x-input-label for="name" :value="__('Nom du Héros / Compte')" />
            <x-text-input id="name" name="name" type="text" class="mt-1.5 block w-full" :value="old('name', $user->name)" required autofocus autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="email" :value="__('Adresse E-mail')" />
            <x-text-input id="email" name="email" type="email" class="mt-1.5 block w-full" :value="old('email', $user->email)" required autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div class="mt-3">
                    <p class="font-body text-sm text-brass-dark">
                        {{ __("Votre adresse e-mail n'est pas vérifiée.") }}

                        <button form="send-verification" class="underline font-bold text-wax hover:text-wax-dark rounded-md focus:outline-none">
                            {{ __('Cliquez ici pour renvoyer le lien de confirmation.') }}
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 font-body font-semibold text-sm text-forest">
                            {{ __('Un nouveau lien de vérification a été envoyé à votre adresse e-mail.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div class="flex items-center gap-4 pt-2">
            <x-primary-button>{{ __('Sauvegarder') }}</x-primary-button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2500)"
                    class="font-body text-sm font-bold text-forest"
                >{{ __('Modifications enregistrées.') }}</p>
            @endif
        </div>
    </form>
</section>
