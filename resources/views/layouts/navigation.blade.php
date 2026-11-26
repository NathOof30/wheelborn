<header x-data="{ open: false }" class="codex-navbar" role="banner">
    <div class="codex-navbar-inner flex-wrap lg:flex-nowrap py-2 lg:py-0">
        {{-- Marque --}}
        <a href="{{ route('dashboard') }}" class="codex-brand" aria-label="Wheelborn — Tirage">
            <svg class="codex-brand-crest" viewBox="0 0 36 36" aria-hidden="true">
                <circle cx="18" cy="18" r="15" fill="none" stroke="currentColor" stroke-width="2"/>
                <circle cx="18" cy="18" r="5" fill="none" stroke="currentColor" stroke-width="1.8"/>
                <path d="M18 3 V13 M18 23 V33 M3 18 H13 M23 18 H33 M7.4 7.4 L14.5 14.5 M21.5 21.5 L28.6 28.6 M28.6 7.4 L21.5 14.5 M14.5 21.5 L7.4 28.6" stroke="currentColor" stroke-width="1.6"/>
            </svg>
            <span class="codex-brand-name">Wheelborn</span>
        </a>

        {{-- Navigation Desktop (5 chapitres officiels) --}}
        <nav class="codex-nav hidden md:flex" aria-label="Navigation principale">
            <a href="{{ route('dashboard') }}"
               class="codex-nav-link {{ request()->routeIs('dashboard') ? 'is-active' : '' }}"
               @if (request()->routeIs('dashboard')) aria-current="page" @endif>
                <svg class="nav-icon" viewBox="0 0 24 24" aria-hidden="true">
                    <circle cx="12" cy="12" r="9" fill="none" stroke="currentColor" stroke-width="1.9"/>
                    <circle cx="12" cy="12" r="3" fill="none" stroke="currentColor" stroke-width="1.7"/>
                    <path d="M12 3 V9 M12 15 V21 M3 12 H9 M15 12 H21" stroke="currentColor" stroke-width="1.7"/>
                </svg>
                <span>Tirage</span>
            </a>

            <a href="{{ route('collection') }}"
               class="codex-nav-link {{ request()->routeIs('collection') ? 'is-active' : '' }}"
               @if (request()->routeIs('collection')) aria-current="page" @endif>
                <svg class="nav-icon" viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M4 5.5 C7 4.5 10 5 12 6.5 C14 5 17 4.5 20 5.5 V18.5 C17 17.5 14 18 12 19.5 C10 18 7 17.5 4 18.5 Z" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linejoin="round"/>
                    <line x1="12" y1="6.5" x2="12" y2="19.5" stroke="currentColor" stroke-width="1.7"/>
                </svg>
                <span>Héros</span>
            </a>

            <a href="{{ route('arena') }}"
               class="codex-nav-link {{ request()->routeIs('arena') ? 'is-active' : '' }}"
               @if (request()->routeIs('arena')) aria-current="page" @endif>
                <svg class="nav-icon" viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M5 19 L18 6 M14 5 H19 V10 M5 5 L19 19 M19 19 L16 19 M5 16 L8 19" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <span>Arène</span>
            </a>

            <a href="{{ route('leaderboard') }}"
               class="codex-nav-link {{ request()->routeIs('leaderboard') ? 'is-active' : '' }}"
               @if (request()->routeIs('leaderboard')) aria-current="page" @endif>
                <svg class="nav-icon" viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M4 17 L6 8 L10 12 L12 6 L14 12 L18 8 L20 17 Z" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linejoin="round"/>
                    <line x1="5" y1="20" x2="19" y2="20" stroke="currentColor" stroke-width="1.9" stroke-linecap="round"/>
                </svg>
                <span>Rang</span>
            </a>

            <a href="{{ route('workshop') }}"
               class="codex-nav-link {{ request()->routeIs('workshop') ? 'is-active' : '' }}"
               @if (request()->routeIs('workshop')) aria-current="page" @endif>
                <svg class="nav-icon" viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M4 10 H17 L20 13 H15 L17 19 H7 L9 13 H4 Z" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linejoin="round"/>
                    <path d="M9 6 H15 V10 H9 Z" fill="none" stroke="currentColor" stroke-width="1.7"/>
                </svg>
                <span>Atelier</span>
            </a>
        </nav>

        {{-- HUD Unique (inline sur desktop >= 1024px, centré en seconde ligne sur tablette & mobile) --}}
        <div class="codex-hud-slot lg:ml-auto">
            <livewire:player-hud />
        </div>

        {{-- Compte Desktop & Hamburger Mobile --}}
        <div class="flex items-center gap-2.5 ml-auto lg:ml-0">
            <div class="hidden md:block">
                <x-dropdown align="right" width="48" contentClasses="py-1 bg-parchment-bright border border-brass-dark/50 shadow-xl">
                    <x-slot name="trigger">
                        <button type="button" class="codex-account-trigger">
                            @if (Auth::user()->isAdmin())
                                <span title="Administrateur" aria-hidden="true">👑</span>
                            @endif
                            <span class="max-w-[110px] truncate">{{ Auth::user()->name }}</span>
                            <svg class="w-3.5 h-3.5 opacity-80" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')" class="font-display font-bold text-ink hover:bg-parchment-shade/60">
                            {{ __('Profil') }}
                        </x-dropdown-link>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault(); this.closest('form').submit();"
                                    class="font-display font-bold text-wax hover:bg-wax/10">
                                {{ __('Déconnexion') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            {{-- Bouton tiroir mobile --}}
            <button
                type="button"
                @click="open = ! open"
                class="md:hidden inline-flex items-center justify-center p-2 rounded-md text-[#E7D6B5] hover:text-white hover:bg-white/10 border border-brass/30 transition"
                aria-label="Ouvrir le menu"
                :aria-expanded="open.toString()"
            >
                <svg class="h-5 w-5" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                    <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    </div>

    {{-- Tiroir Mobile --}}
    <div x-show="open" x-cloak class="md:hidden border-t border-brass-dark/60 bg-leather-dark/95 px-4 py-3 space-y-2">
        <div class="grid grid-cols-5 gap-1.5">
            <a href="{{ route('dashboard') }}" class="codex-nav-link flex-col gap-1 px-1 py-2 text-center text-xs {{ request()->routeIs('dashboard') ? 'is-active' : '' }}">
                <svg class="nav-icon" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9" fill="none" stroke="currentColor" stroke-width="1.9"/><circle cx="12" cy="12" r="3" fill="none" stroke="currentColor" stroke-width="1.7"/></svg>
                <span>Tirage</span>
            </a>
            <a href="{{ route('collection') }}" class="codex-nav-link flex-col gap-1 px-1 py-2 text-center text-xs {{ request()->routeIs('collection') ? 'is-active' : '' }}">
                <svg class="nav-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M4 5.5 C7 4.5 10 5 12 6.5 C14 5 17 4.5 20 5.5 V18.5 C17 17.5 14 18 12 19.5 C10 18 7 17.5 4 18.5 Z" fill="none" stroke="currentColor" stroke-width="1.9"/></svg>
                <span>Héros</span>
            </a>
            <a href="{{ route('arena') }}" class="codex-nav-link flex-col gap-1 px-1 py-2 text-center text-xs {{ request()->routeIs('arena') ? 'is-active' : '' }}">
                <svg class="nav-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M5 19 L18 6 M14 5 H19 V10 M5 5 L19 19" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round"/></svg>
                <span>Arène</span>
            </a>
            <a href="{{ route('leaderboard') }}" class="codex-nav-link flex-col gap-1 px-1 py-2 text-center text-xs {{ request()->routeIs('leaderboard') ? 'is-active' : '' }}">
                <svg class="nav-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M4 17 L6 8 L10 12 L12 6 L14 12 L18 8 L20 17 Z" fill="none" stroke="currentColor" stroke-width="1.9"/></svg>
                <span>Rang</span>
            </a>
            <a href="{{ route('workshop') }}" class="codex-nav-link flex-col gap-1 px-1 py-2 text-center text-xs {{ request()->routeIs('workshop') ? 'is-active' : '' }}">
                <svg class="nav-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M4 10 H17 L20 13 H15 L17 19 H7 L9 13 H4 Z" fill="none" stroke="currentColor" stroke-width="1.9"/></svg>
                <span>Atelier</span>
            </a>
        </div>

        <div class="pt-2.5 border-t border-brass/20 flex items-center justify-between gap-2 text-sm">
            <div class="min-w-0">
                <div class="font-display font-bold text-[#F5E6C4] truncate">{{ Auth::user()->name }}</div>
                <div class="text-xs text-[#C7B28E] truncate">{{ Auth::user()->email }}</div>
            </div>
            <div class="flex items-center gap-2 shrink-0">
                <a href="{{ route('profile.edit') }}" class="px-3 py-1.5 rounded border border-brass/50 text-xs font-display font-bold text-[#F5E6C4] hover:bg-brass/20">
                    {{ __('Profil') }}
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="px-3 py-1.5 rounded border border-wax-light/60 bg-wax/30 text-xs font-display font-bold text-[#FBF1DC] hover:bg-wax">
                        {{ __('Déconnexion') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</header>
