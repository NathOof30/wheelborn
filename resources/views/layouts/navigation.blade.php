<nav x-data="{ open: false }" class="relative z-50 glass border-b border-white/[0.04]">
    <!-- Primary Navigation Menu -->
    <div class="max-w-6xl mx-auto px-6 sm:px-8 lg:px-10">
        <div class="flex justify-between h-16">
            <div class="flex items-center gap-10">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-3 group">
                        <span class="text-2xl transition-all duration-500 group-hover:scale-110 group-hover:rotate-12 group-hover:drop-shadow-[0_0_12px_rgba(212,168,67,0.5)]">🎡</span>
                        <span class="font-display font-extrabold text-lg tracking-[0.12em] uppercase heading-majestic">
                            WHEELBORN
                        </span>
                    </a>
                </div>

                <!-- Navigation Links — aérée -->
                <div class="hidden space-x-8 sm:-my-px sm:flex">
                    <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-2 px-1 pt-1 border-b-2 text-sm font-medium leading-5 transition-all duration-300 {{ request()->routeIs('dashboard') ? 'border-gold text-gold-light' : 'border-transparent text-slate-500 hover:text-slate-200 hover:border-slate-700' }}">
                        <span class="text-base">🎡</span>
                        <span>Tirage</span>
                    </a>
                    <a href="{{ route('collection') }}" class="inline-flex items-center gap-2 px-1 pt-1 border-b-2 text-sm font-medium leading-5 transition-all duration-300 {{ request()->routeIs('collection') ? 'border-indigo-400 text-indigo-300' : 'border-transparent text-slate-500 hover:text-slate-200 hover:border-slate-700' }}">
                        <span class="text-base">🛡️</span>
                        <span>Héros</span>
                    </a>
                    <a href="{{ route('arena') }}" class="inline-flex items-center gap-2 px-1 pt-1 border-b-2 text-sm font-medium leading-5 transition-all duration-300 {{ request()->routeIs('arena') ? 'border-rose-400 text-rose-300' : 'border-transparent text-slate-500 hover:text-slate-200 hover:border-slate-700' }}">
                        <span class="text-base">⚔️</span>
                        <span>Arène</span>
                    </a>
                    <a href="{{ route('leaderboard') }}" class="inline-flex items-center gap-2 px-1 pt-1 border-b-2 text-sm font-medium leading-5 transition-all duration-300 {{ request()->routeIs('leaderboard') ? 'border-gold text-gold-light' : 'border-transparent text-slate-500 hover:text-slate-200 hover:border-slate-700' }}">
                        <span class="text-base">🏆</span>
                        <span>Rang</span>
                    </a>
                    <a href="{{ route('workshop') }}" class="inline-flex items-center gap-2 px-1 pt-1 border-b-2 text-sm font-medium leading-5 transition-all duration-300 {{ request()->routeIs('workshop') ? 'border-emerald-400 text-emerald-300' : 'border-transparent text-slate-500 hover:text-slate-200 hover:border-slate-700' }}">
                        <span class="text-base">🏛️</span>
                        <span>Atelier</span>
                    </a>
                </div>
            </div>

            <!-- Stats Bar (Coins, Elo, Charges) & User Profile -->
            <div class="hidden sm:flex sm:items-center sm:gap-5">
                <!-- HUD Badges Réactif Livewire -->
                <livewire:player-hud />

                <!-- Settings Dropdown -->
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center gap-2 px-3.5 py-2 border {{ Auth::user()->isAdmin() ? 'border-gold/30 bg-gold/5 text-gold-light' : 'border-white/[0.06] bg-white/[0.03] text-slate-400' }} text-sm leading-4 font-medium rounded-2xl hover:text-white hover:bg-white/[0.06] focus:outline-none transition-all duration-300">
                            @if (Auth::user()->isAdmin())
                                <span>👑</span>
                            @endif
                            <div>{{ Auth::user()->name }}</div>

                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')" class="text-slate-300 hover:bg-white/[0.04]">
                            {{ __('Profil') }}
                        </x-dropdown-link>

                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault(); this.closest('form').submit();"
                                    class="text-red-400 hover:bg-white/[0.04]">
                                {{ __('Déconnexion') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger (mobile) -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-xl text-slate-500 hover:text-white hover:bg-white/[0.06] focus:outline-none transition-all duration-300">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden glass border-t border-white/[0.04]">
        <div class="pt-3 pb-4 space-y-1 px-4">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" class="text-slate-300">
                🎡 Tirage
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('collection')" :active="request()->routeIs('collection')" class="text-slate-300">
                🛡️ Héros
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('arena')" :active="request()->routeIs('arena')" class="text-slate-300">
                ⚔️ Arène
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('leaderboard')" :active="request()->routeIs('leaderboard')" class="text-slate-300">
                🏆 Rang
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('workshop')" :active="request()->routeIs('workshop')" class="text-slate-300">
                🏛️ Atelier
            </x-responsive-nav-link>
        </div>

        <div class="pt-4 pb-3 border-t border-white/[0.04] px-5">
            <div class="font-medium text-base text-slate-200">{{ Auth::user()->name }}</div>
            <div class="font-medium text-sm text-slate-500">{{ Auth::user()->email }}</div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')" class="text-slate-400">
                    {{ __('Profil') }}
                </x-responsive-nav-link>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault(); this.closest('form').submit();"
                            class="text-red-400">
                        {{ __('Déconnexion') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
