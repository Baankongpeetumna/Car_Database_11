<x-layouts::app :title="'Manage Categories'">
    @php
        // One colour per category (stable: based on category_id).
        $palette = [
            ['stroke' => 'stroke-red-500',     'dot' => 'bg-red-500',     'tint' => 'bg-red-500/15',     'text' => 'text-red-500'],
            ['stroke' => 'stroke-amber-500',   'dot' => 'bg-amber-500',   'tint' => 'bg-amber-500/15',   'text' => 'text-amber-500'],
            ['stroke' => 'stroke-sky-500',     'dot' => 'bg-sky-500',     'tint' => 'bg-sky-500/15',     'text' => 'text-sky-500'],
            ['stroke' => 'stroke-emerald-500', 'dot' => 'bg-emerald-500', 'tint' => 'bg-emerald-500/15', 'text' => 'text-emerald-500'],
            ['stroke' => 'stroke-violet-500',  'dot' => 'bg-violet-500',  'tint' => 'bg-violet-500/15',  'text' => 'text-violet-500'],
            ['stroke' => 'stroke-pink-500',    'dot' => 'bg-pink-500',    'tint' => 'bg-pink-500/15',    'text' => 'text-pink-500'],
            ['stroke' => 'stroke-cyan-500',    'dot' => 'bg-cyan-500',    'tint' => 'bg-cyan-500/15',    'text' => 'text-cyan-500'],
            ['stroke' => 'stroke-orange-500',  'dot' => 'bg-orange-500',  'tint' => 'bg-orange-500/15',  'text' => 'text-orange-500'],
        ];

        $items = $categories->getCollection();
        $total = (int) $items->sum('cars_count');
        $emptyCount = $items->filter(fn ($c) => (int) $c->cars_count === 0)->count();
        $top = $total > 0 ? $items->sortByDesc('cars_count')->first() : null;
        $paged = $categories->hasPages();

        // Donut geometry
        $r = 76;
        $C = 2 * pi() * $r;
        $nonEmpty = $items->filter(fn ($c) => (int) $c->cars_count > 0)->count();
        $gap = $nonEmpty > 1 ? 5 : 0;
        $offset = 0;
        $segments = [];
        $info = [];

        foreach ($items as $cat) {
            $count = (int) $cat->cars_count;
            $id = (int) $cat->category_id;
            $share = $total > 0 ? (int) round($count / $total * 100) : 0;
            $info[$id] = ['name' => $cat->category_name, 'count' => $count, 'share' => $share];

            if ($count > 0 && $total > 0) {
                $segLen = $count / $total * $C;
                $segments[] = [
                    'id' => $id,
                    'stroke' => $palette[$id % count($palette)]['stroke'],
                    'drawn' => max($segLen - $gap, 1),
                    'offset' => $offset,
                ];
                $offset += $segLen;
            }
        }
    @endphp

    <div class="relative min-h-full overflow-hidden bg-zinc-50 dark:bg-zinc-950">

        {{-- Background decoration: red glow, dot grid, and logo-style slashes --}}
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

        <div class="relative mx-auto max-w-6xl space-y-6 p-4 sm:p-6">

            @include('commerce.messages')

            {{-- Header (same scale as Members / Orders) --}}
            <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <div class="flex items-center gap-2 text-sm text-zinc-400">
                        <a href="{{ route('admin.dashboard') }}" class="hover:text-red-600">Admin</a>
                        <span>/</span>
                        <span class="text-zinc-600 dark:text-zinc-300">Categories</span>
                    </div>

                    <h1 class="font-display mt-1 text-4xl font-extrabold italic uppercase tracking-tight
                               text-zinc-900 sm:text-5xl dark:text-white">
                        Categories
                    </h1>
                    <div class="mt-2 h-1 w-14 -skew-x-12 bg-red-600"></div>
                </div>

                <a href="{{ route('admin.categories.create') }}"
                   class="inline-flex items-center gap-2 self-start rounded-lg bg-red-600 px-5 py-2.5 text-sm
                          font-semibold text-white shadow-lg shadow-red-600/25 transition hover:bg-red-700
                          sm:self-auto">
                    <span class="text-lg leading-none">+</span>
                    Add category
                </a>
            </div>


            {{-- Donut + list share one hover state --}}
            <div x-data="{ active: null, data: @js($info) }"
                 class="grid gap-6 lg:grid-cols-[22rem_1fr]">

                {{-- Left: where the cars are --}}
                <aside class="self-start overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm
                              lg:sticky lg:top-6 dark:border-zinc-800 dark:bg-zinc-900">

                    <div class="h-1 bg-red-600"></div>

                    <div class="relative p-6">
                        <div aria-hidden="true"
                             class="pointer-events-none absolute -right-10 -top-10 size-40 rounded-full bg-red-600/10 blur-3xl"></div>

                        <div class="relative flex items-center justify-between">
                            <p class="text-sm font-medium text-zinc-500">Showroom mix</p>
                            <span class="rounded-full bg-red-600/10 px-2.5 py-0.5 text-xs font-semibold text-red-600 dark:text-red-400">
                                {{ $categories->total() }} {{ \Illuminate\Support\Str::plural('category', $categories->total()) }}
                            </span>
                        </div>

                        <div class="relative mx-auto mt-5 aspect-square w-full max-w-[16rem]">
                            {{-- soft glow + inner disc --}}
                            <div aria-hidden="true" class="absolute inset-6 rounded-full bg-red-600/15 blur-3xl dark:bg-red-600/20"></div>
                            <div aria-hidden="true"
                                 class="absolute inset-[17%] rounded-full border border-zinc-200 bg-zinc-50
                                        shadow-inner dark:border-zinc-800 dark:bg-zinc-950/70"></div>

                            <svg viewBox="0 0 200 200" class="relative size-full" role="img"
                                 aria-label="Share of car models in each category">
                                {{-- decorative rings --}}
                                <circle cx="100" cy="100" r="94" fill="none" stroke-width="1.5" stroke-dasharray="1 5"
                                        class="stroke-zinc-400/60 dark:stroke-zinc-600/70"/>
                                <circle cx="100" cy="100" r="{{ $r }}" fill="none" stroke-width="16"
                                        class="stroke-zinc-200 dark:stroke-zinc-800"/>

                                @foreach ($segments as $seg)
                                    <circle cx="100" cy="100" r="{{ $r }}" fill="none" stroke-width="16"
                                            transform="rotate(-90 100 100)"
                                            stroke-dasharray="{{ round($seg['drawn'], 2) }} {{ round($C, 2) }}"
                                            stroke-dashoffset="-{{ round($seg['offset'], 2) }}"
                                            class="{{ $seg['stroke'] }} cursor-pointer transition-all duration-200"
                                            :class="active === {{ $seg['id'] }}
                                                ? 'opacity-100 drop-shadow-[0_0_6px_rgba(255,255,255,0.45)]'
                                                : (active !== null ? 'opacity-25' : 'opacity-100')"
                                            x-on:mouseenter="active = {{ $seg['id'] }}"
                                            x-on:mouseleave="active = null"/>
                                @endforeach
                            </svg>

                            <div class="pointer-events-none absolute inset-0 flex flex-col items-center justify-center px-10 text-center">
                                <p class="font-display text-5xl font-extrabold tabular-nums text-zinc-900 dark:text-white"
                                   x-text="active !== null ? data[active].count : {{ $total }}">{{ $total }}</p>
                                <p class="mt-0.5 line-clamp-1 text-sm font-medium text-zinc-700 dark:text-zinc-200"
                                   x-text="active !== null ? data[active].name : 'car models'">car models</p>
                                <p class="text-xs text-zinc-500"
                                   x-text="active !== null ? data[active].share + '% of the mix' : 'in total'">in total</p>
                            </div>
                        </div>

                        <div class="relative mt-6 grid grid-cols-2 gap-3">
                            <div class="rounded-xl border border-zinc-200 bg-zinc-50 p-3 dark:border-zinc-800 dark:bg-zinc-800/50">
                                <div class="flex items-center gap-2 text-xs text-zinc-500">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none"
                                         stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                         class="text-amber-500" aria-hidden="true">
                                        <path d="M6 9H4.5a2.5 2.5 0 0 1 0-5H6"/><path d="M18 9h1.5a2.5 2.5 0 0 0 0-5H18"/>
                                        <path d="M4 22h16"/><path d="M10 14.66V17c0 .55-.47.98-.97 1.21C7.85 18.75 7 20.24 7 22"/>
                                        <path d="M14 14.66V17c0 .55.47.98.97 1.21C16.15 18.75 17 20.24 17 22"/>
                                        <path d="M18 2H6v7a6 6 0 0 0 12 0V2Z"/>
                                    </svg>
                                    Biggest
                                </div>
                                <p class="mt-1 truncate text-sm font-semibold text-zinc-900 dark:text-white">
                                    {{ $top?->category_name ?? 'None yet' }}
                                </p>
                                @if ($top)
                                    <p class="text-xs text-zinc-500">{{ $top->cars_count }} {{ \Illuminate\Support\Str::plural('model', $top->cars_count) }}</p>
                                @endif
                            </div>

                            <div class="rounded-xl border border-zinc-200 bg-zinc-50 p-3 dark:border-zinc-800 dark:bg-zinc-800/50">
                                <div class="flex items-center gap-2 text-xs text-zinc-500">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none"
                                         stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                         class="{{ $emptyCount > 0 ? 'text-amber-500' : 'text-emerald-500' }}" aria-hidden="true">
                                        <circle cx="12" cy="12" r="10"/><path d="M8 12h8"/>
                                    </svg>
                                    Empty
                                </div>
                                <p class="mt-1 text-sm font-semibold {{ $emptyCount > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-zinc-900 dark:text-white' }}">
                                    {{ $emptyCount }} {{ \Illuminate\Support\Str::plural('category', $emptyCount) }}
                                </p>
                                <p class="text-xs text-zinc-500">{{ $emptyCount > 0 ? 'no models yet' : 'all in use' }}</p>
                            </div>
                        </div>

                        @if ($paged)
                            <p class="relative mt-3 text-xs text-zinc-500">Based on the categories on this page.</p>
                        @endif
                    </div>
                </aside>


                {{-- Right: the list --}}
                <section class="flex max-h-[75vh] flex-col overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm
                                dark:border-zinc-800 dark:bg-zinc-900">

                    <div class="h-1 shrink-0 bg-gradient-to-r from-red-600 via-red-500/60 to-transparent"></div>

                    <div class="flex shrink-0 items-center justify-between px-5 pt-5">
                        <div class="flex items-center gap-3">
                            <h2 class="font-display text-xl font-semibold text-zinc-900 dark:text-white">All categories</h2>
                            <span class="rounded-full bg-red-600/10 px-2.5 py-0.5 text-xs font-semibold text-red-600 dark:text-red-400">
                                {{ $categories->total() }}
                            </span>
                        </div>
                        <span class="hidden text-xs text-zinc-500 sm:block">Hover a row to preview it on the chart</span>
                    </div>

                    <form method="GET" action="{{ route('admin.categories.index') }}"
                          class="flex shrink-0 gap-3 border-b border-zinc-200 p-4 dark:border-zinc-800">
                        <div class="relative flex-1">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                                 stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                 class="pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-zinc-400">
                                <circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>
                            </svg>

                            <input type="text"
                                   name="q"
                                   value="{{ request('q') }}"
                                   placeholder="Search category"
                                   aria-label="Search category"
                                   class="w-full rounded-xl border border-zinc-300 bg-zinc-50 py-2.5 pl-11 pr-4 text-sm
                                          text-zinc-900 focus:border-red-500 focus:outline-none focus:ring-1 focus:ring-red-500
                                          dark:border-zinc-700 dark:bg-zinc-800 dark:text-white">
                        </div>

                        <button type="submit"
                                class="rounded-xl bg-red-600 px-5 py-2.5 text-sm font-semibold text-white
                                       shadow-lg shadow-red-600/25 transition hover:bg-red-700">
                            Search
                        </button>

                        @if (request()->filled('q'))
                            <a href="{{ route('admin.categories.index') }}"
                               class="rounded-xl border border-zinc-300 px-4 py-2.5 text-sm font-medium text-zinc-700
                                      transition hover:bg-zinc-100 dark:border-zinc-700 dark:text-zinc-300 dark:hover:bg-zinc-800">
                                Reset
                            </a>
                        @endif
                    </form>

                    @if ($categories->count())
                        <ul class="min-h-0 flex-1 divide-y divide-zinc-200 overflow-y-auto overscroll-contain [scrollbar-width:thin] dark:divide-zinc-800">
                            @foreach ($categories as $category)
                                @php
                                    $id = (int) $category->category_id;
                                    $count = (int) $category->cars_count;
                                    $pal = $palette[$id % count($palette)];
                                    $share = $info[$id]['share'];
                                    $initial = mb_strtoupper(mb_substr($category->category_name, 0, 1));
                                @endphp

                                <li x-on:mouseenter="active = {{ $id }}"
                                    x-on:mouseleave="active = null"
                                    :class="active === {{ $id }} ? 'bg-zinc-50 dark:bg-zinc-800/60' : ''"
                                    class="relative flex items-center gap-4 px-5 py-4 transition-colors">

                                    {{-- slash that lights up for the hovered row --}}
                                    <span aria-hidden="true"
                                          class="absolute left-0 top-3 bottom-3 w-1 origin-center -skew-x-12 rounded-r bg-red-600
                                                 transition-transform duration-200"
                                          :class="active === {{ $id }} ? 'scale-y-100' : 'scale-y-0'"></span>

                                    <div class="flex size-12 shrink-0 items-center justify-center rounded-xl text-xl font-bold
                                                {{ $pal['tint'] }} {{ $pal['text'] }}">
                                        {{ $initial }}
                                    </div>

                                    <div class="min-w-0 flex-1">
                                        <p class="font-display truncate text-lg font-semibold text-zinc-900 dark:text-white">
                                            {{ $category->category_name }}
                                            <span class="ml-1 text-xs font-normal text-zinc-400">#{{ $id }}</span>
                                        </p>

                                        @if ($count === 0)
                                            <p class="mt-1.5 text-xs font-medium text-amber-600 dark:text-amber-400">
                                                Empty, no car models yet
                                            </p>
                                        @else
                                            <div class="mt-2 flex items-center gap-3">
                                                <div class="h-1.5 flex-1 overflow-hidden rounded-full bg-zinc-200 dark:bg-zinc-800">
                                                    <div class="h-full rounded-full {{ $pal['dot'] }}"
                                                         style="width: {{ max($share, 3) }}%"></div>
                                                </div>
                                                <span class="w-10 text-right text-xs tabular-nums text-zinc-500">{{ $share }}%</span>
                                            </div>
                                        @endif
                                    </div>

                                    <div class="text-right">
                                        <p class="font-display text-2xl font-bold tabular-nums
                                                  {{ $count === 0 ? 'text-zinc-400 dark:text-zinc-600' : 'text-zinc-900 dark:text-white' }}">
                                            {{ number_format($count) }}
                                        </p>
                                        <p class="text-xs text-zinc-500">
                                            {{ \Illuminate\Support\Str::plural('model', $count) }}
                                        </p>
                                    </div>

                                    <div class="flex items-center gap-2">
                                        <a href="{{ route('admin.categories.edit', $category) }}"
                                           title="Edit {{ $category->category_name }}"
                                           aria-label="Edit {{ $category->category_name }}"
                                           class="flex size-9 items-center justify-center rounded-lg border border-zinc-300
                                                  text-zinc-500 transition hover:border-red-600 hover:bg-red-600 hover:text-white
                                                  dark:border-zinc-700 dark:text-zinc-400 dark:hover:border-red-600">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                                                 fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                                 stroke-linejoin="round" aria-hidden="true">
                                                <path d="M12 20h9"/>
                                                <path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/>
                                            </svg>
                                        </a>

                                        @if ($count > 0)
                                            <span title="Has car models, so it can't be deleted"
                                                  class="flex size-9 cursor-not-allowed items-center justify-center rounded-lg
                                                         border border-dashed border-zinc-300 text-zinc-400 dark:border-zinc-700">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                                                     fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                                     stroke-linejoin="round" aria-hidden="true">
                                                    <rect width="18" height="11" x="3" y="11" rx="2"/>
                                                    <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                                                </svg>
                                                <span class="sr-only">Has car models, so it can't be deleted</span>
                                            </span>
                                        @else
                                            <form method="POST"
                                                  action="{{ route('admin.categories.destroy', $category) }}"
                                                  data-confirm="Delete category {{ $category->category_name }}?"
                                                  onsubmit="return confirm(this.dataset.confirm)">
                                                @csrf
                                                @method('DELETE')

                                                <button type="submit"
                                                        title="Delete {{ $category->category_name }}"
                                                        aria-label="Delete {{ $category->category_name }}"
                                                        class="flex size-9 items-center justify-center rounded-lg border
                                                               border-zinc-300 text-zinc-500 transition hover:border-red-500
                                                               hover:text-red-500 dark:border-zinc-700 dark:text-zinc-400
                                                               dark:hover:border-red-500 dark:hover:text-red-400">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                                                         fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                                         stroke-linejoin="round" aria-hidden="true">
                                                        <path d="M3 6h18"/>
                                                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"/>
                                                        <path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                                                    </svg>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <div class="px-5 py-16 text-center">
                            <p class="font-display text-2xl font-semibold text-zinc-900 dark:text-white">
                                @if (request()->filled('q'))
                                    No categories match "{{ request('q') }}"
                                @else
                                    No categories yet
                                @endif
                            </p>
                            <p class="mt-2 text-sm text-zinc-500">
                                @if (request()->filled('q'))
                                    Try another name, or reset the search.
                                @else
                                    Add your first category so cars can be sorted into it.
                                @endif
                            </p>
                        </div>
                    @endif

                    @if ($paged)
                        <div class="shrink-0 border-t border-zinc-200 p-4 dark:border-zinc-800">
                            {{ $categories->links() }}
                        </div>
                    @endif
                </section>
            </div>

        </div>
    </div>
</x-layouts::app>