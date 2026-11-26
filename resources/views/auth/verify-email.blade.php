<x-guest-layout>
    <div class="mb-6 text-center space-y-2">
        <h1 class="font-display font-bold text-xl text-ink tracking-wide">Vérification de l'email</h1>
        <p class="font-body text-sm italic text-ink-muted leading-relaxed">
            Merci pour votre inscription ! Vérifiez votre adresse email en cliquant sur le lien que nous venons de vous envoyer.
        </p>
    </div>

    @if (session('status') == 'verification-link-sent')
        <div class="mb-5 p-3.5 rounded-md bg-forest/10 border border-forest/40 font-body text-sm font-semibold text-forest text-center">
            Un nouveau lien de vérification a été envoyé à votre adresse email.
        </div>
    @endif

    <div class="flex flex-wrap items-center justify-between gap-4">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button type="submit" class="btn-brass btn-brass--sm">
                Renvoyer le lien
            </button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="font-display text-xs font-bold text-ink-muted hover:text-wax transition cursor-pointer">
                Déconnexion
            </button>
        </form>
    </div>
</x-guest-layout>
