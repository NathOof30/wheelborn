<nav x-data="{ open: false }" class="relative z-50 bg-slate-900/95 backdrop-blur-md border-b border-slate-800 text-slate-200">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex items-center gap-8">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-2 group">
                        <span class="text-2xl filter drop-shadow group-hover:scale-110 transition-transform">🎡</span>
                        <span class="font-extrabold text-xl tracking-wider bg-gradient-to-r from-amber-400 via-indigo-400 to-cyan-400 bg-clip-text text-transparent">
                            WHEELBORN
                        </span>
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-6 sm:-my-px sm:flex">
                    <a href="{{ route('dashboard') }}" class="inline-flex items-center px-1 pt-1 border-b-2 font-medium text-sm leading-5 transition duration-150 {{ request()->routeIs('dashboard') ? 'border-amber-400 text-amber-400' : 'border-transparent text-slate-400 hover:text-slate-200 hover:border-slate-600' }}">
                        🎡 Tirage
                    </a>
                    <a href="{{ route('collection') }}" class="inline-flex items-center px-1 pt-1 border-b-2 font-medium text-sm leading-5 transition duration-150 {{ request()->routeIs('collection') ? 'border-indigo-400 text-indigo-400' : 'border-transparent text-slate-400 hover:text-slate-200 hover:border-slate-600' }}">
                        🛡️ Mes Héros
                    </a>
                    <a href="{{ route('arena') }}" class="inline-flex items-center px-1 pt-1 border-b-2 font-medium text-sm leading-5 transition duration-150 {{ request()->routeIs('arena') ? 'border-rose-400 text-rose-400' : 'border-transparent text-slate-400 hover:text-slate-200 hover:border-slate-600' }}">
                        ⚔️ Arène
                    </a>
                    <a href="{{ route('leaderboard') }}" class="inline-flex items-center px-1 pt-1 border-b-2 font-medium text-sm leading-5 transition duration-150 {{ request()->routeIs('leaderboard') ? 'border-amber-400 text-amber-400' : 'border-transparent text-slate-400 hover:text-slate-200 hover:border-slate-600' }}">
                        🏆 Classement
                    </a>
                </div>
            </div>

            <!-- Stats Bar (Coins, Elo, Charges) & User Profile -->
            <div class="hidden sm:flex sm:items-center sm:gap-4">
                <!-- HUD Badges Réactif Livewire -->
                <livewire:player-hud />

                <!-- Settings Dropdown -->
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center gap-2 px-3 py-2 border {{ Auth::user()->isAdmin() ? 'border-amber-500/50 bg-amber-500/10 text-amber-300' : 'border-slate-700 bg-slate-800/80 text-slate-300' }} text-sm leading-4 font-medium rounded-xl hover:text-white hover:bg-slate-700 focus:outline-none transition ease-in-out duration-150">
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
                        <x-dropdown-link :href="route('profile.edit')" class="text-slate-300 hover:bg-slate-800">
                            {{ __('Profil') }}
                        </x-dropdown-link>

                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault(); this.closest('form').submit();"
                                    class="text-red-400 hover:bg-slate-800">
                                {{ __('Déconnexion') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger (mobile) -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-slate-400 hover:text-white hover:bg-slate-800 focus:outline-none transition">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden bg-slate-900 border-b border-slate-800">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" class="text-slate-200">
                🎡 Tirage
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('collection')" :active="request()->routeIs('collection')" class="text-slate-200">
                🛡️ Mes Héros
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('arena')" :active="request()->routeIs('arena')" class="text-slate-200">
                ⚔️ Arène
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('leaderboard')" :active="request()->routeIs('leaderboard')" class="text-slate-200">
                🏆 Classement
            </x-responsive-nav-link>
        </div>

        <div class="pt-4 pb-3 border-t border-slate-800 px-4">
            <div class="font-medium text-base text-slate-200">{{ Auth::user()->name }}</div>
            <div class="font-medium text-sm text-slate-400">{{ Auth::user()->email }}</div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')" class="text-slate-300">
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
