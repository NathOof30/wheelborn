<section class="space-y-6">
    <header>
        <h2 class="text-lg font-display font-bold text-rose-300 tracking-wide flex items-center gap-2">
            <span>⚠️</span>
            <span>{{ __('Zone de Dissolution du Compte') }}</span>
        </h2>

        <p class="mt-1 text-sm text-slate-400">
            {{ __('Une fois votre compte supprimé, l\'ensemble de vos combattants, victoires et ressources accumulées seront définitivement anéantis.') }}
        </p>
    </header>

    <x-danger-button
        x-data=""
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
    >{{ __('Supprimer le compte') }}</x-danger-button>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="p-8">
            @csrf
            @method('delete')

            <h2 class="text-xl font-display font-bold text-white tracking-wide">
                {{ __('Êtes-vous certain de vouloir dissoudre ce compte ?') }}
            </h2>

            <p class="mt-2 text-sm text-slate-400 leading-relaxed">
                {{ __('Cette action est irréversible. Veuillez renseigner votre mot de passe pour confirmer la destruction définitive de vos données.') }}
            </p>

            <div class="mt-6">
                <x-input-label for="password" value="{{ __('Mot de passe') }}" class="sr-only" />

                <x-text-input
                    id="password"
                    name="password"
                    type="password"
                    class="mt-1 block w-full"
                    placeholder="{{ __('Votre mot de passe de confirmation') }}"
                />

                <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2" />
            </div>

            <div class="mt-8 flex justify-end gap-3">
                <x-secondary-button x-on:click="$dispatch('close')">
                    {{ __('Annuler') }}
                </x-secondary-button>

                <x-danger-button>
                    {{ __('Confirmer la suppression') }}
                </x-danger-button>
            </div>
        </form>
    </x-modal>
</section>
