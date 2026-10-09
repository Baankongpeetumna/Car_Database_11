<x-layouts::app :title="'Manage Reviews'">
    @include('commerce.messages')

    @php
        $counts = $ratingCounts ?? null;

        // Fallback: if the controller doesn't pass the stats, query them here (adjust the model name if needed)
        if ($counts === null) {
            $reviewModel = \App\Models\Review::class;
            $counts = class_exists($reviewModel)
                ? $reviewModel::selectRaw('rating, COUNT(*) as c')->groupBy('rating')->pluck('c', 'rating')->all()
                : [];
        }

        $counts  = collect($counts)->mapWithKeys(fn ($c, $r) => [(int) $r => (int) $c])->all();
        $total   = $totalReviews ?? array_sum($counts);
        $average = $averageRating
            ?? ($total ? array_sum(array_map(fn ($r, $c) => $r * $c, array_keys($counts), $counts)) / $total : 0);

        $active = (int) request('rating');
        $keep   = request()->except('page', 'rating');
        $url    = fn ($r = null) => route('admin.reviews.index', $r ? $keep + ['rating' => $r] : $keep);

        $panel = 'rounded-2xl border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900';

        // One color per star level (full class names so Tailwind picks them up)
        $tones = [
            5 => ['bar' => 'bg-emerald-500', 'text' => 'text-emerald-500', 'ring' => 'ring-emerald-500', 'edge' => 'border-l-emerald-500', 'solid' => 'border-emerald-500 bg-emerald-500 text-white'],
            4 => ['bar' => 'bg-lime-500',    'text' => 'text-lime-500',    'ring' => 'ring-lime-500',    'edge' => 'border-l-lime-500',    'solid' => 'border-lime-500 bg-lime-500 text-white'],
            3 => ['bar' => 'bg-amber-500',   'text' => 'text-amber-500',   'ring' => 'ring-amber-500',   'edge' => 'border-l-amber-500',   'solid' => 'border-amber-500 bg-amber-500 text-white'],
            2 => ['bar' => 'bg-orange-500',  'text' => 'text-orange-500',  'ring' => 'ring-orange-500',  'edge' => 'border-l-orange-500',  'solid' => 'border-orange-500 bg-orange-500 text-white'],
            1 => ['bar' => 'bg-red-500',     'text' => 'text-red-500',     'ring' => 'ring-red-500',     'edge' => 'border-l-red-500',     'solid' => 'border-red-500 bg-red-500 text-white'],
        ];
    @endphp

    <div class="relative mx-auto w-full max-w-5xl px-4 pb-10 pt-2">

        {{-- Background: dot grid, red glow (dark), diagonal stripes --}}
        <div class="pointer-events-none absolute inset-x-0 -top-8 h-72 opacity-60 [background-image:radial-gradient(circle,rgba(128,128,128,0.35)_1px,transparent_1px)] [background-size:24px_24px] [mask-image:linear-gradient(to_bottom,black,transparent)]"></div>
        <div class="pointer-events-none absolute inset-x-0 -top-8 hidden h-72 bg-[radial-gradient(ellipse_at_top,rgba(220,38,38,0.16),transparent_70%)] dark:block"></div>
        <div class="pointer-events-none absolute -right-2 top-0 hidden space-y-1.5 md:block" aria-hidden="true">
            <div class="h-2 w-24 -skew-x-[30deg] bg-red-700/80"></div>
            <div class="ml-4 h-2 w-20 -skew-x-[30deg] bg-red-700/60"></div>
            <div class="ml-8 h-2 w-14 -skew-x-[30deg] bg-red-700/40"></div>
        </div>

        {{-- Header (same pattern as Categories) --}}
        <header class="relative mb-8 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <div class="flex items-center gap-2 text-sm text-zinc-400">
                    <a href="{{ route('admin.dashboard') }}" class="hover:text-red-600">Admin</a>
                    <span>/</span>
                    <span class="text-zinc-600 dark:text-zinc-300">Reviews</span>
                </div>

                <h1 class="font-display mt-1 text-4xl font-black uppercase italic leading-none tracking-tight
                           text-zinc-900 sm:text-5xl dark:text-white">
                    Reviews
                </h1>
                <div class="mt-2 h-1 w-14 -skew-x-12 bg-red-600"></div>
            </div>

            <span class="w-fit rounded-full border border-zinc-300 px-4 py-1.5 text-sm dark:border-zinc-700">
                <b>{{ $reviews->total() }}</b>
                {{ \Illuminate\Support\Str::plural('review', $reviews->total()) }}
            </span>
        </header>

        {{-- Summary: average + clickable rating distribution --}}
        <section class="{{ $panel }} relative mb-5 grid gap-6 p-5 md:grid-cols-[200px_1fr]">
            <div class="flex flex-col justify-center md:border-r md:border-zinc-200 md:pr-6 dark:md:border-zinc-800">
                <div class="text-6xl font-black leading-none">{{ number_format($average, 1) }}</div>
                @if ($total)
                    <div class="mt-2">@include('reviews._stars', ['rating' => round($average)])</div>
                @endif
                <div class="mt-1 text-sm text-zinc-500">
                    {{ $total ? "Based on {$total} " . \Illuminate\Support\Str::plural('review', $total) : 'No reviews yet' }}
                </div>
            </div>

            <div class="space-y-1">
                @for ($i = 5; $i >= 1; $i--)
                    @php
                        $n   = $counts[$i] ?? 0;
                        $pct = $total ? round($n / $total * 100) : 0;
                    @endphp
                    <a href="{{ $url($active === $i ? null : $i) }}"
                       class="flex items-center gap-3 rounded-lg px-2 py-1.5 text-sm transition hover:bg-zinc-100 dark:hover:bg-zinc-800 {{ $active === $i ? 'bg-zinc-100 ring-1 dark:bg-zinc-800 ' . $tones[$i]['ring'] : '' }}">
                        <span class="w-12 shrink-0 font-semibold">{{ $i }} <span class="{{ $tones[$i]['text'] }}">★</span></span>
                        <span class="h-2.5 flex-1 overflow-hidden rounded-full bg-zinc-200 dark:bg-zinc-800">
                            <span class="block h-full rounded-full {{ $tones[$i]['bar'] }}" style="width: {{ $pct }}%"></span>
                        </span>
                        <span class="w-16 shrink-0 text-right text-zinc-500">{{ $n }} ({{ $pct }}%)</span>
                    </a>
                @endfor
            </div>
        </section>

        {{-- Filter chips + search --}}
        <div class="mb-5 flex flex-wrap items-center gap-2">
            <a href="{{ $url() }}"
               class="rounded-full border px-4 py-1.5 text-sm font-semibold {{ $active ? 'border-zinc-300 dark:border-zinc-700' : 'border-transparent bg-zinc-900 text-white dark:bg-white dark:text-zinc-900' }}">
                All <span class="ml-1 opacity-60">{{ $total }}</span>
            </a>

            @for ($i = 5; $i >= 1; $i--)
                <a href="{{ $url($i) }}"
                   class="rounded-full border px-4 py-1.5 text-sm {{ $active === $i ? 'font-semibold ' . $tones[$i]['solid'] : 'border-zinc-300 dark:border-zinc-700' }}">
                    {{ $i }} <span class="{{ $active === $i ? '' : $tones[$i]['text'] }}">★</span>
                    <span class="ml-1 opacity-70">{{ $counts[$i] ?? 0 }}</span>
                </a>
            @endfor

            <form method="GET" action="{{ route('admin.reviews.index') }}" class="ml-auto flex gap-2">
                @if ($active)
                    <input type="hidden" name="rating" value="{{ $active }}">
                @endif
                <input type="text" name="q" value="{{ request('q') }}"
                       placeholder="Search review, car or member"
                       aria-label="Search review, car or member"
                       class="w-64 rounded-full border border-zinc-300 bg-white px-4 py-1.5 text-sm dark:border-zinc-700 dark:bg-zinc-900">
                <button type="submit" class="rounded-full bg-red-600 px-4 py-1.5 text-sm font-semibold text-white hover:bg-red-500">
                    Search
                </button>
                @if (request('q') || $active)
                    <a href="{{ route('admin.reviews.index') }}"
                       class="rounded-full border border-zinc-300 px-4 py-1.5 text-sm dark:border-zinc-700">
                        Reset
                    </a>
                @endif
            </form>
        </div>

        {{-- Review cards (neutral, no colored tags) --}}
        <div class="space-y-3">
            @forelse ($reviews as $review)
                <article class="{{ $panel }} {{ $tones[$review->rating]['edge'] ?? '' }} grid gap-4 border-l-4 p-4 md:grid-cols-[210px_1fr_auto]">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-zinc-100 font-bold text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">
                            {{ mb_strtoupper(mb_substr($review->member?->name ?? '?', 0, 1)) }}
                        </div>
                        <div class="min-w-0">
                            <div class="truncate font-semibold">{{ $review->member?->name }}</div>
                            <div class="truncate text-xs text-zinc-500">{{ $review->member?->email }}</div>
                        </div>
                    </div>

                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-x-3 gap-y-1">
                            <span class="flex items-center gap-1" aria-label="{{ $review->rating }} out of 5 stars">
                                <span class="text-lg tracking-tight">
                                    <span class="{{ $tones[$review->rating]['text'] ?? 'text-amber-500' }}">{{ str_repeat('★', $review->rating) }}</span><span class="text-zinc-300 dark:text-zinc-700">{{ str_repeat('★', 5 - $review->rating) }}</span>
                                </span>
                                <span class="text-sm font-bold {{ $tones[$review->rating]['text'] ?? '' }}">{{ $review->rating }}.0</span>
                            </span>
                            <a href="{{ route('products.show', $review->car_id) }}#reviews"
                               class="text-sm font-semibold underline-offset-2 hover:text-red-500 hover:underline">
                                {{ $review->car?->brand?->brand_name }} {{ $review->car?->model_name }}
                            </a>
                        </div>

                        <p class="mt-2 whitespace-pre-wrap break-words text-[15px] leading-relaxed">{{ $review->comment }}</p>

                        <div class="mt-2 text-xs text-zinc-500">
                            #{{ $review->review_id }} · {{ $review->created_at?->format('d M Y, H:i') }}
                        </div>
                    </div>

                    <form method="POST"
                          action="{{ route('admin.reviews.destroy', $review) }}"
                          data-confirm="Delete review #{{ $review->review_id }}?"
                          onsubmit="return confirm(this.dataset.confirm)"
                          class="md:self-start">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                                class="rounded-lg border border-zinc-300 px-3 py-1.5 text-sm text-zinc-600 transition hover:border-red-600 hover:bg-red-600 hover:text-white dark:border-zinc-700 dark:text-zinc-300">
                            Delete
                        </button>
                    </form>
                </article>
            @empty
                <div class="rounded-xl border border-dashed border-zinc-300 p-10 text-center text-sm text-zinc-500 dark:border-zinc-700">
                    No reviews match these filters.
                    <div class="mt-3">
                        <a href="{{ route('admin.reviews.index') }}" class="text-red-500 hover:underline">Clear filters</a>
                    </div>
                </div>
            @endforelse
        </div>

        <div class="mt-6">
            {{ $reviews->links() }}
        </div>
    </div>
</x-layouts::app>