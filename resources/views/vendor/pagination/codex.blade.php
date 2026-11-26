@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination" class="flex flex-wrap items-center justify-between gap-3 pt-2">
        <div class="text-sm font-body text-ink-sepia">
            Affichage de
            <span class="font-display font-bold text-ink tabular-nums">{{ $paginator->firstItem() }}</span>
            à
            <span class="font-display font-bold text-ink tabular-nums">{{ $paginator->lastItem() }}</span>
            sur
            <span class="font-display font-bold text-ink tabular-nums">{{ $paginator->total() }}</span>
            résultats
        </div>

        <div class="inline-flex items-center gap-1.5">
            @if ($paginator->onFirstPage())
                <span class="px-3 py-1.5 rounded border border-parchment-border bg-parchment/50 text-ink-muted font-display text-xs font-bold opacity-60 cursor-not-allowed">
                    ← Préc.
                </span>
            @else
                <button type="button" wire:click="previousPage('{{ $paginator->getPageName() }}')" wire:loading.attr="disabled" class="btn-cartouche btn-cartouche--sm">
                    ← Préc.
                </button>
            @endif

            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="px-2 py-1 text-ink-muted font-display text-xs font-bold">{{ $element }}</span>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span aria-current="page" class="px-3 py-1.5 rounded border border-brass-edge bg-gradient-to-b from-brass-light to-brass text-[#231405] font-display text-xs font-bold tabular-nums shadow-sm">
                                {{ $page }}
                            </span>
                        @else
                            <button type="button" wire:click="gotoPage({{ $page }}, '{{ $paginator->getPageName() }}')" class="px-3 py-1.5 rounded border border-parchment-border bg-parchment-bright hover:border-brass-dark hover:bg-parchment-light text-ink font-display text-xs font-bold tabular-nums transition cursor-pointer">
                                {{ $page }}
                            </button>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <button type="button" wire:click="nextPage('{{ $paginator->getPageName() }}')" wire:loading.attr="disabled" class="btn-cartouche btn-cartouche--sm">
                    Suiv. →
                </button>
            @else
                <span class="px-3 py-1.5 rounded border border-parchment-border bg-parchment/50 text-ink-muted font-display text-xs font-bold opacity-60 cursor-not-allowed">
                    Suiv. →
                </span>
            @endif
        </div>
    </nav>
@endif
