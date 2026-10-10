<x-layouts::app :title="'Manage Brands'">

    @php
        // ---------------------------------------------------------------
        // Settings
        // ---------------------------------------------------------------
        $modelNameColumn = null;   // e.g. 'car_name'. Leave null to auto-detect from the list below.
        $visibleChips    = 4;      // chips shown before "+N more"
        $maxModelsListed = 100;    // hard cap of names rendered per brand (protects page size)

        // Works with a plain collection or a paginator.
        $isPaginator = $brands instanceof \Illuminate\Pagination\AbstractPaginator;
        $items = $isPaginator ? $brands->getCollection() : collect($brands);
        $total = $brands instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator
            ? $brands->total()
            : $items->count();

        $codes = [
            'japan' => 'JP', 'germany' => 'DE', 'usa' => 'US', 'united states' => 'US', 'south korea' => 'KR',
            'korea' => 'KR', 'italy' => 'IT', 'united kingdom' => 'GB', 'uk' => 'GB', 'france' => 'FR',
            'china' => 'CN', 'sweden' => 'SE', 'india' => 'IN', 'thailand' => 'TH', 'spain' => 'ES',
        ];
        $code = fn ($c) => filled($c)
            ? ($codes[mb_strtolower(trim($c))] ?? (strtoupper(substr(preg_replace('/[^a-z]/i', '', $c), 0, 2)) ?: '--'))
            : '--';

        // One colour per brand (by id), full class names so Tailwind keeps them.
        $palette = [
            ['tone' => 'bg-red-500/15 text-red-500',         'bar' => 'bg-red-500'],
            ['tone' => 'bg-amber-500/15 text-amber-500',     'bar' => 'bg-amber-500'],
            ['tone' => 'bg-sky-500/15 text-sky-500',         'bar' => 'bg-sky-500'],
            ['tone' => 'bg-emerald-500/15 text-emerald-500', 'bar' => 'bg-emerald-500'],
            ['tone' => 'bg-violet-500/15 text-violet-500',   'bar' => 'bg-violet-500'],
            ['tone' => 'bg-pink-500/15 text-pink-500',       'bar' => 'bg-pink-500'],
            ['tone' => 'bg-cyan-500/15 text-cyan-500',       'bar' => 'bg-cyan-500'],
            ['tone' => 'bg-orange-500/15 text-orange-500',   'bar' => 'bg-orange-500'],
        ];

        // Turns one car into a display name. Never returns an empty string.
        $carLabel = function ($car) use ($modelNameColumn) {
            $attrs = $car->getAttributes();
            $key   = $modelNameColumn
                ?: collect(['car_name', 'name', 'model_name', 'model', 'title', 'car_model'])
                    ->first(fn ($k) => array_key_exists($k, $attrs));
            $name  = $key ? trim((string) ($attrs[$key] ?? '')) : '';

            return $name !== '' ? $name : 'Car #'.$car->getKey();
        };

        $rows = $items
            ->sortBy(fn ($b) => (string) $b->brand_name, SORT_NATURAL | SORT_FLAG_CASE)
            ->values()
            ->map(function ($b) use ($palette, $code, $carLabel, $maxModelsListed) {
                $p    = $palette[(int) $b->brand_id % count($palette)];
                $name = trim((string) $b->brand_name) !== '' ? trim((string) $b->brand_name) : 'Unnamed brand';

                // Tip: eager load in the controller -> Brand::with('cars')->withCount('cars')
                $cars = $b->relationLoaded('cars') ? $b->cars : $b->cars()->get();
                $all  = $cars->map($carLabel)->sort(SORT_NATURAL | SORT_FLAG_CASE)->values()->all();

                return [
                    'id'      => (int) $b->brand_id,
                    'model'   => $b,
                    'name'    => $name,
                    'country' => filled($b->country) ? $b->country : 'Other',
                    'code'    => $code($b->country),
                    'count'   => (int) ($b->cars_count ?? count($all)),
                    'models'  => array_slice($all, 0, $maxModelsListed),
                    'extra'   => max(count($all) - $maxModelsListed, 0),
                    'tone'    => $p['tone'],
                    'bar'     => $p['bar'],
                ];
            });

        $totalModels = (int) $rows->sum('count');
        $rows = $rows->map(fn ($r) => $r + ['pct' => $totalModels > 0 ? (int) round($r['count'] / $totalModels * 100) : 0]);

        $segments  = $rows->where('count', '>', 0)->sortByDesc('count')->values();
        $biggest   = $rows->sortByDesc('count')->first();
        $emptyCnt  = $rows->where('count', 0)->count();
        $countries = $rows->groupBy('country')->map(fn ($g, $c) => [
            'name' => $c, 'code' => $g->first()['code'], 'brands' => $g->count(), 'models' => $g->sum('count'),
        ])->sortByDesc('brands')->values();

        $jsRows = $rows->map(fn ($r) => [
            'id' => $r['id'], 'name' => $r['name'], 'country' => $r['country'], 'count' => $r['count'], 'pct' => $r['pct'],
        ])->values();
    @endphp

    <style>[x-cloak] { display: none !important; }</style>

    <script>
        window.brandIndex = function () {
            return {
                active: null,
                total: @js($totalModels),
                items: @js($jsRows),
                get current() { return this.items.find(i => i.id === this.active) || null; },
            };
        };
    </script>

    <div class="relative min-h-full overflow-hidden bg-zinc-50 dark:bg-zinc-950">

        {{-- Background decoration --}}
        <div aria-hidden="true"
             class="pointer-events-none absolute inset-x-0 top-0 hidden h-80 dark:block
                    bg-[radial-gradient(ellipse_at_top,rgba(220,38,38,0.18),transparent_65%)]"></div>
        <div aria-hidden="true"
             class="pointer-events-none absolute inset-x-0 top-0 h-96 opacity-70
                    bg-[radial-gradient(rgba(0,0,0,0.08)_1px,transparent_1px)]
                    dark:bg-[radial-gradient(rgba(255,255,255,0.07)_1px,transparent_1px)]
                    [background-size:22px_22px] [mask-image:linear-gradient(to_bottom,black,transparent)]"></div>
        <div aria-hidden="true"
             class="pointer-events-none absolute right-8 top-6 hidden flex-col items-end gap-1.5 lg:flex">
            <span class="h-2 w-28 -skew-x-12 bg-red-600/70"></span>
            <span class="h-2 w-20 -skew-x-12 bg-red-600/45"></span>
            <span class="h-2 w-12 -skew-x-12 bg-red-600/25"></span>
        </div>

        <div class="relative mx-auto max-w-7xl space-y-6 p-4 sm:p-6">

            @include('commerce.messages')

            {{-- Header --}}
            <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <div class="flex items-center gap-2 text-sm text-zinc-400">
                        <a href="{{ route('admin.dashboard') }}" class="hover:text-red-600">Admin</a>
                        <span>/</span>
                        <span class="text-zinc-600 dark:text-zinc-300">Brands</span>
                    </div>

                    <h1 class="font-display mt-1 text-4xl font-black uppercase italic leading-none tracking-tight
                               text-zinc-900 sm:text-5xl dark:text-white">
                        Brands
                    </h1>
                    <div class="mt-2 h-1 w-14 -skew-x-12 bg-red-600"></div>
                </div>

                <a href="{{ route('admin.brands.create') }}"
                   class="inline-flex items-center justify-center gap-2 self-start rounded-xl bg-red-600 px-5 py-2.5
                          text-sm font-semibold text-white shadow-lg shadow-red-600/30 transition hover:bg-red-700 sm:self-auto">
                    <span class="text-lg leading-none">+</span> Add brand
                </a>
            </div>


            <div x-data="brandIndex()" class="grid items-start gap-6 lg:grid-cols-[21rem_1fr]">

                {{-- ===================== LEFT: showroom mix ===================== --}}
                <aside class="overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm
                              dark:border-zinc-800 dark:bg-zinc-900 lg:sticky lg:top-6">
                    <div class="h-1 bg-gradient-to-r from-red-600 via-red-500/60 to-transparent"></div>

                    <div class="space-y-5 p-5">
                        <div class="flex items-center justify-between">
                            <p class="text-sm font-medium text-zinc-500">Showroom mix</p>
                            <span class="rounded-full bg-red-500/10 px-2.5 py-0.5 text-xs font-semibold text-red-500">
                                {{ number_format($total) }} {{ \Illuminate\Support\Str::plural('brand', $total) }}
                            </span>
                        </div>

                        {{-- Readout: total, or the hovered brand --}}
                        <div class="min-h-[8.5rem]">
                            <p class="truncate text-xs font-medium uppercase tracking-wider text-zinc-500"
                               x-text="current ? current.name : 'Car models in total'"></p>
                            <p class="font-display mt-1 text-7xl font-black italic leading-none tabular-nums text-zinc-900 dark:text-white"
                               x-text="current ? current.count : total">{{ $totalModels }}</p>
                            <p class="mt-2 text-xs text-zinc-500"
                               x-text="current ? current.pct + '% of the mix · ' + current.country
                                               : 'across {{ $rows->count() }} {{ \Illuminate\Support\Str::plural('brand', $rows->count()) }}'">
                                across {{ $rows->count() }} {{ \Illuminate\Support\Str::plural('brand', $rows->count()) }}
                            </p>
                        </div>

                        {{-- Skewed segmented bar: one slice per brand, width = its models --}}
                        <div>
                            @if ($segments->isEmpty())
                                <div class="h-12 -skew-x-12 border border-dashed border-zinc-300 dark:border-zinc-700"></div>
                            @else
                                <div class="flex h-12 gap-1 px-1.5">
                                    @foreach ($segments as $s)
                                        <button type="button"
                                                x-on:mouseenter="active = {{ $s['id'] }}" x-on:mouseleave="active = null"
                                                title="{{ $s['name'] }} · {{ $s['count'] }}"
                                                class="block h-full -skew-x-12 {{ $s['bar'] }} transition-all duration-200"
                                                style="flex: {{ $s['count'] }} 1 0%; min-width: 0.5rem;"
                                                x-bind:class="active === {{ $s['id'] }}
                                                    ? 'opacity-100 -translate-y-1.5 shadow-lg'
                                                    : (active === null ? 'opacity-100' : 'opacity-25')">
                                        </button>
                                    @endforeach
                                </div>
                            @endif

                            {{-- ruler --}}
                            <div class="mt-2 flex justify-between px-1.5" aria-hidden="true">
                                @for ($i = 0; $i < 21; $i++)
                                    <span class="w-px bg-zinc-300 dark:bg-zinc-700 {{ $i % 5 === 0 ? 'h-2.5' : 'h-1.5' }}"></span>
                                @endfor
                            </div>
                            <div class="mt-1 flex justify-between px-1 text-[10px] tabular-nums text-zinc-400">
                                <span>0</span><span>{{ $totalModels }}</span>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div class="rounded-xl border border-zinc-200 bg-zinc-50 p-3 dark:border-zinc-800 dark:bg-zinc-800/50">
                                <p class="text-[11px] text-zinc-500">Biggest</p>
                                <p class="mt-0.5 truncate text-sm font-semibold text-zinc-900 dark:text-white">
                                    {{ $biggest && $biggest['count'] > 0 ? $biggest['name'] : '—' }}
                                </p>
                                <p class="text-[11px] text-zinc-500">
                                    {{ $biggest ? $biggest['count'] : 0 }} {{ \Illuminate\Support\Str::plural('model', $biggest['count'] ?? 0) }}
                                </p>
                            </div>
                            <div class="rounded-xl border border-zinc-200 bg-zinc-50 p-3 dark:border-zinc-800 dark:bg-zinc-800/50">
                                <p class="text-[11px] text-zinc-500">Empty</p>
                                <p class="mt-0.5 text-sm font-semibold text-zinc-900 dark:text-white">
                                    {{ $emptyCnt }} {{ \Illuminate\Support\Str::plural('brand', $emptyCnt) }}
                                </p>
                                <p class="text-[11px] text-zinc-500">{{ $emptyCnt === 0 ? 'all in use' : 'no cars yet' }}</p>
                            </div>
                        </div>

                        {{-- By country --}}
                        @if ($countries->isNotEmpty())
                            <div>
                                <p class="text-xs font-medium uppercase tracking-wider text-zinc-500">By country</p>
                                <ul class="mt-2 divide-y divide-zinc-100 dark:divide-zinc-800">
                                    @foreach ($countries as $c)
                                        <li>
                                            <a href="{{ route('admin.brands.index', ['q' => $c['name']]) }}"
                                               class="group flex items-center gap-3 py-2 transition">
                                                <span class="w-9 shrink-0 -skew-x-12 rounded-sm bg-zinc-200/80 py-0.5 text-center text-[10px] font-bold tracking-wider
                                                             text-zinc-600 transition group-hover:bg-red-600 group-hover:text-white dark:bg-zinc-700 dark:text-zinc-200">
                                                    <span class="inline-block skew-x-12">{{ $c['code'] }}</span>
                                                </span>
                                                <span class="min-w-0 flex-1 truncate text-sm font-medium text-zinc-800 transition group-hover:text-red-600 dark:text-zinc-200">
                                                    {{ $c['name'] }}
                                                </span>
                                                <span class="flex items-center gap-1" aria-hidden="true">
                                                    @for ($i = 0; $i < min($c['brands'], 6); $i++)
                                                        <span class="h-2 w-3 -skew-x-12 bg-red-600"></span>
                                                    @endfor
                                                </span>
                                                <span class="w-14 shrink-0 text-right text-[11px] tabular-nums text-zinc-500">
                                                    {{ $c['brands'] }} · {{ $c['models'] }}
                                                </span>
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                                <p class="mt-1 text-[10px] text-zinc-400">brands · models</p>
                            </div>
                        @endif
                    </div>
                </aside>


                {{-- ===================== RIGHT: all brands ===================== --}}
                <section class="min-w-0 overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm
                                dark:border-zinc-800 dark:bg-zinc-900">
                    <div class="h-1 bg-gradient-to-r from-red-600 via-red-500/60 to-transparent"></div>

                    <div class="space-y-4 p-5">
                        <div class="flex items-center justify-between gap-3">
                            <div class="flex items-center gap-3">
                                <h2 class="font-display text-xl font-semibold text-zinc-900 dark:text-white">All brands</h2>
                                <span class="rounded-full bg-red-500/10 px-2.5 py-0.5 text-xs font-semibold text-red-500">{{ number_format($total) }}</span>
                            </div>
                            <p class="hidden text-xs text-zinc-500 sm:block">Hover a row to preview it on the bar</p>
                        </div>

                        <form method="GET" action="{{ route('admin.brands.index') }}" class="flex flex-col gap-2 sm:flex-row">
                            <div class="relative flex-1">
                                <svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" viewBox="0 0 24 24" fill="none"
                                     stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"
                                     class="pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-zinc-400">
                                    <circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>
                                </svg>
                                <input type="text" name="q" value="{{ request('q') }}" placeholder="Search brand or country"
                                       aria-label="Search brand or country"
                                       class="h-11 w-full rounded-lg border border-zinc-300 bg-zinc-50 pl-10 pr-4 text-sm text-zinc-900
                                              placeholder:text-zinc-400 transition focus:border-red-500 focus:outline-none focus:ring-2 focus:ring-red-500/20
                                              dark:border-zinc-700 dark:bg-zinc-800/60 dark:text-white">
                            </div>
                            <button type="submit"
                                    class="h-11 rounded-lg bg-red-600 px-6 text-sm font-semibold text-white shadow-md shadow-red-600/30 transition hover:bg-red-700">
                                Search
                            </button>
                            @if (request()->filled('q'))
                                <a href="{{ route('admin.brands.index') }}"
                                   class="inline-flex h-11 items-center justify-center rounded-lg border border-zinc-300 px-5 text-sm font-medium
                                          text-zinc-700 transition hover:border-red-500 hover:text-red-600
                                          dark:border-zinc-700 dark:text-zinc-300">
                                    Reset
                                </a>
                            @endif
                        </form>
                    </div>

                    @if ($rows->isEmpty())
                        <div class="border-t border-zinc-200 px-6 py-16 text-center dark:border-zinc-800">
                            <p class="font-display text-2xl font-bold uppercase italic text-zinc-900 dark:text-white">
                                @if (request()->filled('q')) No brands match "{{ request('q') }}" @else No brands yet @endif
                            </p>
                            <p class="mt-2 text-sm text-zinc-500">
                                @if (request()->filled('q'))
                                    Try another name or country, or reset the search.
                                @else
                                    Add your first brand so cars can be assigned to it.
                                @endif
                            </p>
                            @unless (request()->filled('q'))
                                <a href="{{ route('admin.brands.create') }}"
                                   class="mt-5 inline-flex items-center justify-center rounded-xl bg-red-600 px-5 py-2.5 text-sm font-semibold text-white
                                          shadow-md shadow-red-600/30 transition hover:bg-red-700">
                                    + Add brand
                                </a>
                            @endunless
                        </div>
                    @else
                        {{-- Scrolls inside the box once rows exceed max-h --}}
                        <ul class="max-h-[30rem] divide-y divide-zinc-100 overflow-y-auto border-t border-zinc-100 dark:divide-zinc-800 dark:border-zinc-800">
                            @foreach ($rows as $r)
                                @php
                                    $b          = $r['model'];
                                    $modelTotal = count($r['models']);
                                    $hiddenN    = max($r['count'] - $visibleChips, 0); // label counts real total, not the capped list
                                @endphp

                                <li class="group relative flex items-center gap-3 px-4 py-4 transition hover:bg-zinc-50 sm:gap-4 sm:px-5 dark:hover:bg-zinc-800/50"
                                    x-on:mouseenter="active = {{ $r['id'] }}" x-on:mouseleave="active = null">

                                    <span class="absolute inset-y-2 left-0 w-1 -skew-x-12 bg-red-600 opacity-0 transition group-hover:opacity-100"></span>

                                    {{-- Skewed monogram (hidden on very small screens to give the text room) --}}
                                    <div class="hidden size-11 shrink-0 -skew-x-12 place-items-center rounded-md text-lg font-black sm:grid {{ $r['tone'] }}">
                                        <span class="inline-block skew-x-12">{{ mb_strtoupper(mb_substr($r['name'], 0, 1)) }}</span>
                                    </div>

                                    <div class="min-w-0 flex-1">
                                        <div class="flex flex-wrap items-center gap-x-2.5 gap-y-1">
                                            <p class="font-display max-w-full truncate text-lg font-black uppercase italic leading-none tracking-tight
                                                      text-zinc-900 transition group-hover:text-red-600 dark:text-white dark:group-hover:text-red-500">
                                                {{ $r['name'] }}
                                            </p>
                                            <span class="text-[11px] text-zinc-400">#{{ $r['id'] }}</span>
                                            <span class="-skew-x-12 rounded-sm bg-zinc-200/80 px-1.5 py-0.5 text-[10px] font-bold tracking-wider text-zinc-600 dark:bg-zinc-700 dark:text-zinc-200"
                                                  title="{{ $r['country'] }}">
                                                <span class="inline-block skew-x-12">{{ $r['code'] }}</span>
                                                <span class="inline-block skew-x-12 font-medium tracking-normal">{{ $r['country'] }}</span>
                                            </span>
                                        </div>

                                        <div class="mt-2.5 flex items-center gap-3">
                                            <div class="h-1.5 flex-1 -skew-x-12 overflow-hidden bg-zinc-200 dark:bg-zinc-800">
                                                <div class="h-full {{ $r['bar'] }}" style="width: {{ $r['pct'] }}%"></div>
                                            </div>
                                            <span class="w-9 shrink-0 text-right text-[11px] tabular-nums text-zinc-400">{{ $r['pct'] }}%</span>
                                        </div>

                                        {{-- Models of this brand: first few chips, click "+N more" to expand (works on touch too) --}}
                                        @if ($modelTotal > 0)
                                            <div x-data="{ open: false }" class="mt-2.5 min-w-0">
                                                <div class="flex min-w-0 flex-wrap gap-1.5"
                                                     x-bind:class="open ? 'max-h-36 overflow-y-auto pr-1' : ''">
                                                    @foreach ($r['models'] as $m)
                                                        <span @if ($loop->index >= $visibleChips) x-show="open" x-cloak @endif
                                                              title="{{ $m }}"
                                                              class="max-w-[11rem] truncate rounded-md border border-zinc-200 bg-zinc-50 px-2 py-0.5 text-xs text-zinc-600
                                                                     dark:border-zinc-700 dark:bg-zinc-800/60 dark:text-zinc-300">{{ $m }}</span>
                                                    @endforeach

                                                    @if ($r['extra'] > 0)
                                                        <span x-show="open" x-cloak class="px-1 py-0.5 text-xs text-zinc-500">
                                                            and {{ number_format($r['extra']) }} more not listed
                                                        </span>
                                                    @endif

                                                    @if ($hiddenN > 0)
                                                        <button type="button" x-show="!open" x-on:click="open = true"
                                                                aria-label="Show all {{ $r['count'] }} models of {{ $r['name'] }}"
                                                                class="rounded-md px-1.5 py-0.5 text-xs font-semibold text-red-600 transition hover:bg-red-500/10 dark:text-red-400">
                                                            +{{ number_format($hiddenN) }} more
                                                        </button>
                                                    @endif
                                                </div>

                                                @if ($hiddenN > 0)
                                                    <button type="button" x-show="open" x-cloak x-on:click="open = false" aria-expanded="true"
                                                            class="mt-1.5 rounded-md px-1.5 py-0.5 text-xs font-semibold text-red-600 transition hover:bg-red-500/10 dark:text-red-400">
                                                        Show less
                                                    </button>
                                                @endif
                                            </div>
                                        @else
                                            <p class="mt-2 text-xs text-zinc-400">No models yet</p>
                                        @endif
                                    </div>

                                    <div class="w-14 shrink-0 text-right sm:w-16">
                                        <p class="font-display text-2xl font-bold leading-none tabular-nums
                                                  {{ $r['count'] === 0 ? 'text-zinc-300 dark:text-zinc-600' : 'text-zinc-900 dark:text-white' }}">
                                            {{ number_format($r['count']) }}
                                        </p>
                                        <p class="mt-1 text-[11px] text-zinc-500">{{ \Illuminate\Support\Str::plural('model', $r['count']) }}</p>
                                    </div>

                                    {{-- Actions --}}
                                    <div class="flex shrink-0 items-center gap-2">
                                        <a href="{{ route('admin.brands.edit', $b) }}" title="Edit {{ $r['name'] }}" aria-label="Edit {{ $r['name'] }}"
                                           class="grid size-10 place-items-center rounded-lg border border-red-200 bg-red-50 text-red-600 transition
                                                  hover:bg-red-600 hover:text-white
                                                  dark:border-red-900/50 dark:bg-red-950/30 dark:text-red-400 dark:hover:bg-red-600 dark:hover:text-white">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                 stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                <path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4Z"/>
                                            </svg>
                                        </a>

                                        @if ($r['count'] > 0)
                                            <span title="In use by {{ $r['count'] }} {{ \Illuminate\Support\Str::plural('model', $r['count']) }}, so it can't be deleted"
                                                  class="grid size-10 cursor-not-allowed place-items-center rounded-lg border border-dashed border-zinc-300 text-zinc-300
                                                         dark:border-zinc-700 dark:text-zinc-600">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                     stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                    <rect width="18" height="11" x="3" y="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                                                </svg>
                                            </span>
                                        @else
                                            <form method="POST" action="{{ route('admin.brands.destroy', $b) }}"
                                                  data-confirm="Delete brand {{ $r['name'] }}?"
>
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" title="Delete {{ $r['name'] }}" aria-label="Delete {{ $r['name'] }}"
                                                        class="grid size-10 place-items-center rounded-lg border border-zinc-300 bg-white text-zinc-500 transition
                                                               hover:border-red-600 hover:bg-red-600 hover:text-white
                                                               dark:border-zinc-700 dark:bg-zinc-800">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                         stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                        <path d="M3 6h18"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"/>
                                                        <path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                                                    </svg>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @endif

                    @if ($isPaginator && $brands->hasPages())
                        <div class="border-t border-zinc-200 px-5 py-4 dark:border-zinc-800">
                            {{ $brands->links() }}
                        </div>
                    @endif
                </section>
            </div>

        </div>
    </div>
</x-layouts::app>