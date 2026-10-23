<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-display font-extrabold text-xl tracking-wide flex items-center gap-3">
                <span class="text-2xl">👤</span>
                <span class="heading-majestic">{{ __('Profil & Destinée') }}</span>
            </h2>
            <div class="text-xs text-slate-500 font-mono tracking-widest uppercase">Paramètres du compte</div>
        </div>
    </x-slot>

    <div class="section-breathe">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10">
            <div class="p-6 sm:p-10 glass-card rounded-3xl border border-white/[0.06] shadow-xl">
                <div class="max-w-xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <div class="p-6 sm:p-10 glass-card rounded-3xl border border-white/[0.06] shadow-xl">
                <div class="max-w-xl">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            <div class="p-6 sm:p-10 glass-card rounded-3xl border border-rose-500/10 shadow-xl">
                <div class="max-w-xl">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
