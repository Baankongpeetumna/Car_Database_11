@if ($paginator->hasPages())
    <nav class="flex flex-wrap items-center justify-between gap-3" aria-label="Pagination">
        <p class="text-sm text-zinc-500">
            Showing {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} of {{ number_format($paginator->total()) }}
        </p>

        <div class="flex items-center gap-1.5">
            @if ($paginator->onFirstPage())
                <span class="flex size-10 items-center justify-center rounded-xl border border-line text-zinc-300"><x-store.icon name="chevron-left" class="size-4" /></span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="flex size-10 items-center justify-center rounded-xl border border-line bg-white text-ink hover:border-ink" aria-label="Previous page"><x-store.icon name="chevron-left" class="size-4" /></a>
            @endif

            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="flex size-10 items-center justify-center text-sm text-zinc-400">{{ $element }}</span>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span aria-current="page" class="flex size-10 items-center justify-center rounded-xl bg-race font-display text-sm font-semibold text-white">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}" class="flex size-10 items-center justify-center rounded-xl border border-line bg-white font-display text-sm font-semibold text-ink hover:border-ink">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="flex size-10 items-center justify-center rounded-xl border border-line bg-white text-ink hover:border-ink" aria-label="Next page"><x-store.icon name="chevron-right" class="size-4" /></a>
            @else
                <span class="flex size-10 items-center justify-center rounded-xl border border-line text-zinc-300"><x-store.icon name="chevron-right" class="size-4" /></span>
            @endif
        </div>
    </nav>
@endif
