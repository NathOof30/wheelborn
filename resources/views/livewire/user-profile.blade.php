@php
    use App\View\Presenters\FolioCardPresenter;
    $leagueInk = FolioCardPresenter::leagueInkColor($league->value);
@endphp

<div class="space-y-8">
    @if ($notification)
        <div
            wire:key="profile-toast-{{ md5($notification) }}"
            x-data="{
                timer: null,
                init() { this.timer = setTimeout(() => $wire.dismissNotification(), 5000); },
                destroy() { if (this.timer) clearTimeout(this.timer); }
            }"
            class="p-4 rounded-lg flex items-center justify-between border-2 shadow-lg {{ $notificationType === 'error' ? 'bg-parchment-bright border-wax text-wax' : ($notificationType === 'warning' ? 'bg-parchment-bright border-brass text-brass-dark' : 'bg-parchment-bright border-forest text-forest') }}"
            role="status"
        >
            <span class="font-body text-sm font-bold">{{ $notification }}</span>
            <button type="button" wire:click="dismissNotification" class="font-bold text-ink-muted hover:text-ink px-1 cursor-pointer" aria-label="Fermer">✕</button>
        </div>
    @endif

    {{-- Onglets Codex (Destinée & Palmarès vs Paramètres du Compte) --}}
    <div class="flex items-center justify-center">
        <div class="inline-flex flex-wrap justify-center gap-2 p-1.5 rounded-lg bg-parchment-shade/65 border border-parchment-border">
            <button
                type="button"
                wire:click="setTab('overview')"
                class="{{ $activeTab === 'overview' ? 'btn-brass btn-brass--sm' : 'btn-cartouche btn-cartouche--sm' }}"
            >
                <span>Destinée &amp; Palmarès</span>
            </button>

            <button
                type="button"
                wire:click="setTab('settings')"
                class="{{ $activeTab === 'settings' ? 'btn-brass btn-brass--sm' : 'btn-cartouche btn-cartouche--sm' }}"
            >
                <span>Paramètres du Compte</span>
            </button>
        </div>
    </div>

    @if ($activeTab === 'overview')
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

                {{-- Jauge d'XP laiton --}}
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

            {{-- 4 Cartouches KPI --}}
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 pt-5 border-t border-parchment-border/70">
                <div class="p-3 rounded-lg bg-parchment-bright border border-parchment-border">
                    <span class="font-display text-[10px] uppercase font-bold tracking-wider text-ink-muted block">Solde d'Or</span>
                    <span class="font-display text-base font-bold text-brass-dark">{{ number_format($user->coins, 0, ',', ' ') }} 🪙</span>
                </div>

                <div class="p-3 rounded-lg bg-parchment-bright border border-parchment-border">
                    <span class="font-display text-[10px] uppercase font-bold tracking-wider text-ink-muted block">Collection</span>
                    <span class="font-display text-base font-bold text-ink">{{ $heroesCount }} Héros</span>
                </div>

                <div class="p-3 rounded-lg bg-parchment-bright border border-parchment-border">
                    <span class="font-display text-[10px] uppercase font-bold tracking-wider text-ink-muted block">Puissance Max</span>
                    <span class="font-display text-base font-bold text-wax">{{ $maxPowerScore }} pts</span>
                </div>

                <div class="p-3 rounded-lg bg-parchment-bright border border-parchment-border">
                    <span class="font-display text-[10px] uppercase font-bold tracking-wider text-ink-muted block">Taux de Victoire</span>
                    <span class="font-display text-base font-bold text-forest">{{ $winrate }}%</span>
                </div>
            </div>
        </div>

        {{-- 2. VITRINE TOP 3 FAVORIS (CARTES UNIVERSELLES 316×440) --}}
        <div class="space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-parchment-border">
                <div>
                    <h2 class="font-display text-lg sm:text-xl font-bold text-ink flex items-center gap-2">
                        <span>Vitrine des Combattants Phares</span>
                        <span class="px-2 py-0.5 rounded-full bg-brass/20 border border-brass-dark/40 font-display text-xs font-bold text-brass-dark">
                            {{ $favoriteCharacters->count() }}/3
                        </span>
                    </h2>
                    <p class="font-body text-sm italic text-ink-muted mt-0.5">
                        Les 3 héros exposés sur votre profil. Cliquez une carte pour consulter son verso.
                    </p>
                </div>

                <a href="{{ route('collection') }}" class="btn-cartouche btn-cartouche--sm shrink-0">
                    <span>Gérer ma collection</span>
                </a>
            </div>

            <div class="folios-grid">
                @for ($slot = 0; $slot < 3; $slot++)
                    @php $character = $favoriteCharacters[$slot] ?? null; @endphp

                    @if ($character)
                        <div wire:key="profile-slot-{{ $character->id }}" class="flex flex-col items-center gap-2.5 w-full max-w-[316px]">
                            <div class="w-full flex items-center justify-between gap-2 px-1">
                                <span class="px-2.5 py-0.5 rounded-full bg-brass/20 border border-brass-dark/50 font-display text-[11px] font-bold text-brass-dark">
                                    ★ Vitrine #{{ $slot + 1 }}
                                </span>
                                <button
                                    type="button"
                                    wire:click="removeFavorite('{{ $character->id }}')"
                                    class="px-2.5 py-0.5 rounded-md border border-wax/45 bg-wax/10 text-wax hover:bg-wax hover:text-parchment-bright font-display text-[11px] font-bold transition cursor-pointer"
                                    title="Retirer de la vitrine"
                                >
                                    ✕ Retirer
                                </button>
                            </div>

                            <x-folio-card
                                :card="$character"
                                :showOwnerActions="true"
                                :folioKey="'profile-fav-'.$character->id"
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
                                <div class="w-14 h-14 rounded-full bg-parchment-shade/80 border border-brass-dark/40 flex items-center justify-center text-brass-dark text-xl">
                                    ★
                                </div>
                                <h4 class="font-display text-sm font-bold text-ink">Emplacement Libre #{{ $slot + 1 }}</h4>
                                <p class="font-body text-sm italic text-ink-muted max-w-[220px]">
                                    Placez un héros en favori depuis votre collection pour l'exposer ici.
                                </p>
                                <a href="{{ route('collection') }}" class="btn-brass btn-brass--sm mt-1">
                                    Choisir un héros →
                                </a>
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
                    </div>
                    <div class="pt-1 space-y-1">
                        <div class="flex justify-between font-display text-xs font-bold">
                            <span class="text-ink-muted">Taux de Victoire</span>
                            <span class="text-forest">{{ $winrate }}%</span>
                        </div>
                        <div class="w-full bg-parchment-shade h-2 rounded-full overflow-hidden border border-parchment-border">
                            <div class="h-full rounded-full bg-forest" style="width: {{ $winrate }}%"></div>
                        </div>
                    </div>
                </div>

                <div class="codex-panel p-5 space-y-3">
                    <h3 class="font-display text-sm font-bold uppercase tracking-wider text-ink">Collection &amp; Roue</h3>
                    <div class="divide-y divide-parchment-border/60 font-body text-sm">
                        <div class="flex justify-between py-1.5">
                            <span class="text-ink-muted">Héros en réserve</span>
                            <span class="font-display font-bold text-ink">{{ $heroesCount }}</span>
                        </div>
                        <div class="flex justify-between py-1.5">
                            <span class="text-ink-muted">Cartes Légendaires</span>
                            <span class="font-display font-bold text-brass-dark">{{ $legendariesCount }}</span>
                        </div>
                        <div class="flex justify-between py-1.5">
                            <span class="text-ink-muted">Cartes Épiques</span>
                            <span class="font-display font-bold" style="color: #54274A;">{{ $epicsCount }}</span>
                        </div>
                        <div class="flex justify-between py-1.5">
                            <span class="text-ink-muted">Record de Puissance</span>
                            <span class="font-display font-bold text-ink">{{ $maxPowerScore }} pts</span>
                        </div>
                    </div>
                </div>

                <div class="codex-panel p-5 space-y-3">
                    <h3 class="font-display text-sm font-bold uppercase tracking-wider text-brass-dark">Économie &amp; Atelier</h3>
                    <div class="divide-y divide-parchment-border/60 font-body text-sm">
                        <div class="flex justify-between py-1.5">
                            <span class="text-ink-muted">Héros recyclés</span>
                            <span class="font-display font-bold text-ink">{{ $recycledCount }}</span>
                        </div>
                        <div class="flex justify-between py-1.5">
                            <span class="text-ink-muted">Aimant à Pouvoirs</span>
                            <span class="font-display font-bold text-ink">Niv. {{ $workshopLevels['rare_magnet'] }}/5</span>
                        </div>
                        <div class="flex justify-between py-1.5">
                            <span class="text-ink-muted">Forge des Héros</span>
                            <span class="font-display font-bold text-ink">Niv. {{ $workshopLevels['hero_forge'] }}/5</span>
                        </div>
                        <div class="flex justify-between py-1.5">
                            <span class="text-ink-muted">Coffre de Guerre</span>
                            <span class="font-display font-bold text-ink">Niv. {{ $workshopLevels['arena_bonus'] }}/3</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- 4. SALLE DES TROPHÉES & HAUTS FAITS --}}
        <div class="space-y-4">
            <div class="flex items-center justify-between gap-2 pb-2 border-b border-parchment-border">
                <h2 class="font-display text-lg sm:text-xl font-bold text-ink">
                    Salle des Trophées &amp; Hauts Faits
                </h2>
                <span class="font-display text-xs font-bold text-ink-muted">
                    {{ collect($achievementsList)->where('is_unlocked', true)->count() }} / {{ count($achievementsList) }} accomplis
                </span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                @foreach ($achievementsList as $ach)
                    <div class="p-4 rounded-lg border-2 flex flex-col justify-between {{ $ach['is_unlocked'] ? 'bg-parchment-bright border-brass shadow-md' : 'bg-parchment-light/65 border-parchment-border opacity-75' }}">
                        <div class="flex items-start gap-3">
                            <div class="w-11 h-11 rounded-lg flex items-center justify-center text-xl shrink-0 border {{ $ach['is_unlocked'] ? 'bg-brass/20 border-brass' : 'bg-parchment-shade border-parchment-border grayscale' }}">
                                <span>{{ $ach['icon'] }}</span>
                            </div>
                            <div class="space-y-1 min-w-0">
                                <h4 class="font-display text-xs font-bold truncate {{ $ach['is_unlocked'] ? 'text-ink' : 'text-ink-muted' }}">
                                    {{ $ach['name'] }}
                                </h4>
                                <p class="font-body text-xs text-ink-sepia leading-snug">
                                    {{ $ach['description'] }}
                                </p>
                            </div>
                        </div>

                        <div class="mt-3 pt-2.5 border-t border-parchment-border/60 flex items-center justify-between text-[11px]">
                            @if ($ach['is_unlocked'])
                                <span class="font-display font-bold text-forest">✓ {{ $ach['unlocked_at'] }}</span>
                            @else
                                <span class="font-body italic text-ink-muted">Verrouillé</span>
                            @endif
                            <span class="font-display font-bold text-brass-dark">+{{ $ach['reward'] }} 🪙</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @else
        {{-- ONGLET 2 : PARAMÈTRES DU COMPTE --}}
        <div class="max-w-3xl mx-auto space-y-6">
            <div class="codex-panel codex-panel--framed p-5 sm:p-7">
                <div class="max-w-xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <div class="codex-panel codex-panel--framed p-5 sm:p-7">
                <div class="max-w-xl">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            <div class="codex-panel p-5 sm:p-7 border-wax/50">
                <div class="max-w-xl">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </div>
    @endif
</div>
