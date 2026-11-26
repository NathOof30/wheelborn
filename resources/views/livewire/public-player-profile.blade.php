@php
    use App\View\Presenters\FolioCardPresenter;
    $leagueInk = FolioCardPresenter::leagueInkColor($league->value);
@endphp

<div class="space-y-8">
    {{-- 1. BANNIÈRE IDENTITÉ ENLUMINÉE --}}
    <div class="codex-panel codex-panel--framed p-5 sm:p-7 space-y-6">
        <div class="flex flex-col lg:flex-row items-stretch lg:items-center justify-between gap-6">
            <div class="flex items-center gap-4 sm:gap-5 w-full lg:w-auto min-w-0">
                <div class="relative shrink-0">
                    <div
                        class="w-20 h-20 sm:w-24 sm:h-24 rounded-xl bg-parchment-bright border-2 p-2 flex flex-col items-center justify-center shadow-md"
                        style="border-color: {{ $leagueInk }};"
                    >
                        <span class="text-3xl sm:text-4xl leading-none">{{ $league->icon() }}</span>
                        <span class="font-display text-[10px] uppercase font-bold tracking-widest mt-1" style="color: {{ $leagueInk }};">
                            {{ $league->label() }}
                        </span>
                    </div>
                    <div class="absolute -bottom-2 -right-2 px-2 py-0.5 rounded-full bg-leather border border-brass text-[11px] font-display text-parchment-bright font-bold shadow">
                        {{ $user->elo ?? 1000 }} Élo
                    </div>
                </div>

                <div class="space-y-1.5 flex-1 min-w-0">
                    <div class="flex flex-wrap items-center gap-2 min-w-0">
                        <h1 class="font-display text-xl sm:text-3xl font-bold text-ink tracking-wide break-words leading-tight">
                            {{ $user->name }}
                        </h1>
                        @if ($user->isAdmin())
                            <span class="px-2 py-0.5 rounded-full font-display text-[10px] font-bold uppercase bg-wax/15 text-wax border border-wax/45">
                                Admin
                            </span>
                        @endif
                    </div>

                    <div class="inline-flex flex-wrap items-center gap-1.5 max-w-full px-3 py-0.5 rounded-full bg-brass/15 border border-brass-dark/40 font-display text-xs font-bold text-ink-sepia">
                        <span class="break-words">Titre : « {{ $levelProgress['title'] }} »</span>
                    </div>

                    <p class="font-body text-xs italic text-ink-muted">
                        Combattant WheelBorn depuis le {{ $user->created_at?->format('d/m/Y') ?? "l'Origine" }}
                    </p>
                </div>
            </div>

            <div class="w-full lg:w-80 p-4 rounded-lg bg-parchment-bright border border-parchment-border space-y-2 shrink-0">
                <div class="flex items-center justify-between font-display text-xs font-bold">
                    <span class="text-ink">Niveau {{ $levelProgress['level'] }}</span>
                    <span class="text-brass-dark">{{ $levelProgress['current_level_xp'] }} / {{ $levelProgress['step_xp_required'] }} XP</span>
                </div>

                <div class="w-full bg-parchment-shade h-2.5 rounded-full overflow-hidden border border-parchment-border">
                    <div
                        class="h-full rounded-full transition-all duration-500"
                        style="width: {{ $levelProgress['percentage'] }}%; background: linear-gradient(90deg, var(--gold-dark), var(--gold-light));"
                    ></div>
                </div>

                <div class="flex items-center justify-between font-body text-xs italic text-ink-muted">
                    <span>Progression palier</span>
                    <span class="font-display not-italic font-bold text-ink">{{ $levelProgress['percentage'] }}%</span>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 pt-5 border-t border-parchment-border/70">
            <div class="p-3 rounded-lg bg-parchment-bright border border-parchment-border">
                <span class="font-display text-[10px] uppercase font-bold tracking-wider text-ink-muted block">Classement Élo</span>
                <span class="font-display text-base font-bold text-brass-dark">{{ $user->elo ?? 1000 }}</span>
            </div>

            <div class="p-3 rounded-lg bg-parchment-bright border border-parchment-border">
                <span class="font-display text-[10px] uppercase font-bold tracking-wider text-ink-muted block">Collection</span>
                <span class="font-display text-base font-bold text-ink">{{ $collectionCount }} héros</span>
            </div>

            <div class="p-3 rounded-lg bg-parchment-bright border border-parchment-border">
                <span class="font-display text-[10px] uppercase font-bold tracking-wider text-ink-muted block">Duels disputés</span>
                <span class="font-display text-base font-bold text-wax">{{ $battlesCount }}</span>
            </div>

            <div class="p-3 rounded-lg bg-parchment-bright border border-parchment-border">
                <span class="font-display text-[10px] uppercase font-bold tracking-wider text-ink-muted block">Taux de victoire</span>
                <span class="font-display text-base font-bold text-forest">{{ $winrate }}%</span>
            </div>
        </div>

        <div class="pt-4 border-t border-parchment-border/60 flex flex-wrap items-center justify-between gap-3">
            <a href="{{ route('leaderboard') }}" class="btn-cartouche btn-cartouche--sm">
                <span>← Retour au Classement</span>
            </a>

            @if ($isOwnProfile)
                <a href="{{ route('profile.edit') }}" class="btn-brass btn-brass--sm">
                    <span>Gérer mon Profil &amp; Paramètres</span>
                </a>
            @else
                <a href="{{ route('arena') }}" class="btn-wax btn-wax--sm">
                    <span>Rejoindre l'Arène</span>
                </a>
            @endif
        </div>
    </div>

    {{-- 2. VITRINE DES 3 HÉROS FAVORIS (LECTURE SEULE) --}}
    <div class="space-y-4">
        <div class="pb-3 border-b border-parchment-border">
            <h2 class="font-display text-lg sm:text-xl font-bold text-ink">Vitrine des Héros Phares</h2>
            <p class="font-body text-sm italic text-ink-muted mt-0.5">
                Les 3 champions d'exception sélectionnés par {{ $user->name }}.
            </p>
        </div>

        <div class="folios-grid">
            @for ($slot = 0; $slot < 3; $slot++)
                @php $character = $favoriteCharacters[$slot] ?? null; @endphp

                @if ($character)
                    <div wire:key="pub-slot-{{ $character->id }}" class="flex flex-col items-center gap-2.5 w-full max-w-[316px]">
                        <div class="w-full flex items-center justify-start px-1">
                            <span class="px-2.5 py-0.5 rounded-full bg-brass/20 border border-brass-dark/50 font-display text-[11px] font-bold text-brass-dark">
                                ★ Vitrine #{{ $slot + 1 }}
                            </span>
                        </div>

                        <x-folio-card
                            :card="$character"
                            :showOwnerActions="false"
                            :folioKey="'pub-fav-'.$character->id"
                        />
                    </div>
                @else
                    <div class="flex flex-col items-center gap-2.5 w-full max-w-[316px]">
                        <div class="w-full flex items-center justify-start px-1 min-h-[26px]">
                            <span class="px-2.5 py-0.5 rounded-full bg-parchment-shade/60 border border-parchment-border font-display text-[11px] font-bold text-ink-muted">
                                ☆ Vitrine #{{ $slot + 1 }}
                            </span>
                        </div>

                        <div
                            class="w-full rounded-xl border-2 border-dashed border-parchment-border bg-parchment-bright/60 p-6 flex flex-col items-center justify-center text-center gap-3"
                            style="min-height: 440px;"
                        >
                            <div class="w-14 h-14 rounded-full bg-parchment-shade/80 border border-parchment-border flex items-center justify-center text-ink-muted text-xl">
                                ★
                            </div>
                            <h4 class="font-display text-sm font-bold text-ink-sepia">Emplacement Libre #{{ $slot + 1 }}</h4>
                            <p class="font-body text-sm italic text-ink-muted max-w-[220px]">
                                Aucun champion exposé sur cet emplacement par le joueur.
                            </p>
                        </div>
                    </div>
                @endif
            @endfor
        </div>
    </div>

    {{-- 3. STATISTIQUES DE CARRIÈRE (3 BLOCS) --}}
    <div class="space-y-4">
        <h2 class="font-display text-lg sm:text-xl font-bold text-ink pb-2 border-b border-parchment-border">
            Statistiques de Carrière
        </h2>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
            <div class="codex-panel p-5 space-y-3">
                <h3 class="font-display text-sm font-bold uppercase tracking-wider text-wax">Performances Arène 1v1</h3>
                <div class="divide-y divide-parchment-border/60 font-body text-sm">
                    <div class="flex justify-between py-1.5">
                        <span class="text-ink-muted">Duels disputés</span>
                        <span class="font-display font-bold text-ink">{{ $battlesCount }}</span>
                    </div>
                    <div class="flex justify-between py-1.5">
                        <span class="text-ink-muted">Victoires</span>
                        <span class="font-display font-bold text-forest">{{ $winsCount }}</span>
                    </div>
                    <div class="flex justify-between py-1.5">
                        <span class="text-ink-muted">Défaites</span>
                        <span class="font-display font-bold text-wax">{{ $lossesCount }}</span>
                    </div>
                    <div class="flex justify-between py-1.5">
                        <span class="text-ink-muted">Matchs nuls</span>
                        <span class="font-display font-bold text-brass-dark">{{ $drawsCount }}</span>
                    </div>
                    <div class="flex justify-between py-1.5">
                        <span class="font-semibold text-ink">Taux de victoire</span>
                        <span class="font-display font-bold text-forest">{{ $winrate }}%</span>
                    </div>
                </div>
            </div>

            <div class="codex-panel p-5 space-y-3">
                <h3 class="font-display text-sm font-bold uppercase tracking-wider text-ink">Collection &amp; Destin</h3>
                <div class="divide-y divide-parchment-border/60 font-body text-sm">
                    <div class="flex justify-between py-1.5">
                        <span class="text-ink-muted">Héros en caserne</span>
                        <span class="font-display font-bold text-ink">{{ $collectionCount }}</span>
                    </div>
                    <div class="flex justify-between py-1.5">
                        <span class="text-ink-muted">Légendaires possédés</span>
                        <span class="font-display font-bold text-brass-dark">{{ $legendariesCount }}</span>
                    </div>
                    <div class="flex justify-between py-1.5">
                        <span class="text-ink-muted">Synergies découvertes</span>
                        <span class="font-display font-bold text-ink">{{ $synergiesCount }} / 16</span>
                    </div>
                    <div class="flex justify-between py-1.5">
                        <span class="font-semibold text-ink">Ligue atteinte</span>
                        <span class="font-display font-bold" style="color: {{ $leagueInk }};">{{ $league->icon() }} {{ $league->label() }}</span>
                    </div>
                </div>
            </div>

            <div class="codex-panel p-5 space-y-3">
                <h3 class="font-display text-sm font-bold uppercase tracking-wider text-brass-dark">Économie &amp; Forge</h3>
                <div class="divide-y divide-parchment-border/60 font-body text-sm">
                    <div class="flex justify-between py-1.5">
                        <span class="text-ink-muted">Cartes recyclées</span>
                        <span class="font-display font-bold text-ink">{{ $recycledCount }}</span>
                    </div>
                    <div class="flex justify-between py-1.5">
                        <span class="text-ink-muted">Niveau d'Atelier</span>
                        <span class="font-display font-bold text-brass-dark">Niveau {{ $workshopLevel }}</span>
                    </div>
                    <div class="flex justify-between py-1.5">
                        <span class="font-semibold text-ink">Rang Élo</span>
                        <span class="font-display font-bold text-ink">{{ $user->elo ?? 1000 }} Élo</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- 4. SALLE DES TROPHÉES --}}
    <div class="space-y-4">
        <div class="flex items-center justify-between gap-2 pb-2 border-b border-parchment-border">
            <h2 class="font-display text-lg sm:text-xl font-bold text-ink">Salle des Trophées</h2>
            <span class="font-display text-xs font-bold text-ink-muted">
                {{ collect($achievementsList)->where('unlocked', true)->count() }} / {{ count($achievementsList) }} accomplis
            </span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach ($achievementsList as $ach)
                <div class="p-4 rounded-lg border-2 flex flex-col justify-between {{ $ach['unlocked'] ? 'bg-parchment-bright border-brass shadow-md' : 'bg-parchment-light/65 border-parchment-border opacity-75' }}">
                    <div class="flex items-start gap-3">
                        <div class="w-11 h-11 rounded-lg flex items-center justify-center text-xl shrink-0 border {{ $ach['unlocked'] ? 'bg-brass/20 border-brass' : 'bg-parchment-shade border-parchment-border grayscale' }}">
                            <span>{{ $ach['icon'] }}</span>
                        </div>
                        <div class="space-y-1 min-w-0">
                            <h4 class="font-display text-xs font-bold truncate {{ $ach['unlocked'] ? 'text-ink' : 'text-ink-muted' }}">
                                {{ $ach['title'] }}
                            </h4>
                            <p class="font-body text-xs text-ink-sepia leading-snug">
                                {{ $ach['description'] }}
                            </p>
                        </div>
                    </div>

                    <div class="mt-3 pt-2.5 border-t border-parchment-border/60 flex items-center justify-between text-[11px]">
                        @if ($ach['unlocked'])
                            <span class="font-display font-bold text-forest">✓ Débloqué le {{ $ach['unlocked_at'] }}</span>
                        @else
                            <span class="font-body italic text-ink-muted">Verrouillé</span>
                        @endif
                        <span class="font-display font-bold text-brass-dark">+{{ $ach['reward_coins'] }} 🪙</span>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
