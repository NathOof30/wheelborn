<x-guest-layout>
    <div class="mb-6 text-center">
        <h2 class="font-display font-extrabold text-xl text-white tracking-wide">Vérification de l'email</h2>
        <p class="text-sm text-slate-500 mt-3 leading-relaxed">
            Merci pour votre inscription ! Vérifiez votre adresse email en cliquant sur le lien que nous venons de vous envoyer.
        </p>
    </div>

    @if (session('status') == 'verification-link-sent')
        <div class="mb-5 p-3.5 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-sm text-emerald-300 text-center">
            Un nouveau lien de vérification a été envoyé à votre adresse email.
        </div>
    @endif

    <div class="flex items-center justify-between gap-4">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button type="submit" class="px-6 py-3 rounded-2xl bg-gradient-to-r from-gold to-gold-light text-cosmos-deep font-display font-bold text-sm tracking-wider uppercase transition-all duration-300 hover:shadow-lg hover:shadow-gold/20 hover:scale-[1.02] active:scale-95">
                Renvoyer le lien
            </button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="text-sm text-slate-500 hover:text-slate-300 transition-colors duration-300">
                Déconnexion
            </button>
        </form>
    </div>
</x-guest-layout>
