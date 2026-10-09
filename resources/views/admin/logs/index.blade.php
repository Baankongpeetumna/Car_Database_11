<x-layouts::app :title="'Activity Log'">
    @include('commerce.messages')

    @php
        $field = 'rounded-full border border-zinc-300 bg-white px-4 py-1.5 text-sm dark:border-zinc-700 dark:bg-zinc-900';

        // icon bubble + verb color + sentence verb per action
        $actionStyles = [
            'created'        => ['icon' => 'bg-emerald-500 text-white', 'verb' => 'text-emerald-600 dark:text-emerald-400', 'chip' => 'border-emerald-500 bg-emerald-500 text-white', 'text' => 'added',                     'path' => 'M12 5v14M5 12h14'],
            'updated'        => ['icon' => 'bg-blue-500 text-white',    'verb' => 'text-blue-600 dark:text-blue-400',       'chip' => 'border-blue-500 bg-blue-500 text-white',       'text' => 'changed',                   'path' => 'M12 20h9M16.5 3.5a2.1 2.1 0 013 3L7 19l-4 1 1-4z'],
            'deleted'        => ['icon' => 'bg-red-500 text-white',     'verb' => 'text-red-600 dark:text-red-400',         'chip' => 'border-red-500 bg-red-500 text-white',         'text' => 'deleted',                   'path' => 'M3 6h18M8 6V4h8v2M6 6l1 14h10l1-14'],
            'restocked'      => ['icon' => 'bg-amber-500 text-white',   'verb' => 'text-amber-600 dark:text-amber-400',     'chip' => 'border-amber-500 bg-amber-500 text-white',     'text' => 'restocked',                 'path' => 'M21 8l-9-5-9 5v8l9 5 9-5zM3 8l9 5 9-5M12 13v8'],
            'status_changed' => ['icon' => 'bg-purple-500 text-white',  'verb' => 'text-purple-600 dark:text-purple-400',   'chip' => 'border-purple-500 bg-purple-500 text-white',   'text' => 'changed the status of',     'path' => 'M21 12a9 9 0 11-3-6.7M21 3v6h-6'],
            'role_changed'   => ['icon' => 'bg-zinc-500 text-white',    'verb' => 'text-zinc-600 dark:text-zinc-300',       'chip' => 'border-zinc-500 bg-zinc-500 text-white',       'text' => 'changed the role of',       'path' => 'M12 3l8 3v6c0 5-3.5 8-8 9-4.5-1-8-4-8-9V6z'],
        ];
        $fallback = ['icon' => 'bg-zinc-500 text-white', 'verb' => 'text-zinc-600 dark:text-zinc-300', 'chip' => 'border-red-600 bg-red-600 text-white', 'text' => 'updated', 'path' => 'M12 12h.01'];

        // Show log values in a readable way
        $show = function ($value) {
            if ($value === null || $value === '') {
                return '—';
            }

            if (is_bool($value)) {
                return $value ? 'true' : 'false';
            }

            return Str::limit(is_scalar($value) ? (string) $value : json_encode($value), 80);
        };

        $activeAction = request('action');
        $chipUrl = fn ($a = null) => route('admin.logs.index', request()->except('page', 'action') + ($a ? ['action' => $a] : []));

        // Times are stored in UTC; show them in the shop's local time (Thailand, UTC+7)
        $tz    = 'Asia/Bangkok';
        $local = fn ($dt) => $dt?->copy()->timezone($tz);

        $groups = $logs->getCollection()->groupBy(fn ($log) => $local($log->created_at)?->toDateString() ?? 'unknown');

        $dayLabel = function ($date) use ($tz) {
            if ($date === 'unknown') {
                return 'Unknown date';
            }
            $d   = \Illuminate\Support\Carbon::parse($date, $tz);
            $now = \Illuminate\Support\Carbon::now($tz);

            return $d->isSameDay($now) ? 'Today' : ($d->isSameDay($now->copy()->subDay()) ? 'Yesterday' : $d->format('l, d M Y'));
        };
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

        {{-- Header --}}
        <header class="relative mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <div class="flex items-center gap-2 text-sm text-zinc-400">
                    <a href="{{ route('admin.dashboard') }}" class="hover:text-red-600">Admin</a>
                    <span>/</span>
                    <span class="text-zinc-600 dark:text-zinc-300">Activity Log</span>
                </div>

                <h1 class="font-display mt-1 text-4xl font-black uppercase italic leading-none tracking-tight
                           text-zinc-900 sm:text-5xl dark:text-white">
                    Activity Log
                </h1>
                <div class="mt-2 h-1 w-14 -skew-x-12 bg-red-600"></div>
            </div>

            <span class="w-fit rounded-full border border-zinc-300 px-4 py-1.5 text-sm dark:border-zinc-700">
                <b>{{ $logs->total() }}</b> {{ \Illuminate\Support\Str::plural('entry', $logs->total()) }}
            </span>
        </header>

        <p class="relative mb-5 max-w-2xl text-sm text-zinc-500">
            Every change an admin makes to cars, brands, categories, membership tiers, reviews and order status.
        </p>

        {{-- Action chips --}}
        <div class="relative mb-3 flex flex-wrap items-center gap-2">
            <a href="{{ $chipUrl() }}"
               class="rounded-full border px-4 py-1.5 text-sm font-semibold {{ $activeAction ? 'border-zinc-300 dark:border-zinc-700' : 'border-transparent bg-zinc-900 text-white dark:bg-white dark:text-zinc-900' }}">
                All actions
            </a>

            @foreach ($actions as $action)
                <a href="{{ $chipUrl($action) }}"
                   class="rounded-full border px-4 py-1.5 text-sm {{ $activeAction === $action ? 'font-semibold ' . ($actionStyles[$action]['chip'] ?? $fallback['chip']) : 'border-zinc-300 dark:border-zinc-700' }}">
                    {{ Str::headline($action) }}
                </a>
            @endforeach
        </div>

        {{-- Filters --}}
        <form method="GET" action="{{ route('admin.logs.index') }}" class="relative mb-8 flex flex-wrap gap-2">
            @if ($activeAction)
                <input type="hidden" name="action" value="{{ $activeAction }}">
            @endif

            <input type="text" name="q" value="{{ request('q') }}"
                   placeholder="Search name (e.g. Camry)"
                   aria-label="Search name"
                   class="{{ $field }} w-64">

            <select name="subject" aria-label="Table" class="{{ $field }}">
                <option value="">All tables</option>
                @foreach ($subjects as $value => $label)
                    <option value="{{ $value }}" @selected(request('subject') === $value)>{{ $label }}</option>
                @endforeach
            </select>

            <select name="admin" aria-label="Admin" class="{{ $field }}">
                <option value="">All admins</option>
                @foreach ($admins as $admin)
                    <option value="{{ $admin->member_id }}"
                            @selected((string) request('admin') === (string) $admin->member_id)>
                        {{ $admin->name }}
                    </option>
                @endforeach
            </select>

            <button type="submit" class="rounded-full bg-red-600 px-5 py-1.5 text-sm font-semibold text-white hover:bg-red-500">
                Filter
            </button>

            <a href="{{ route('admin.logs.index') }}"
               class="rounded-full border border-zinc-300 px-4 py-1.5 text-sm dark:border-zinc-700">
                Reset
            </a>
        </form>

        {{-- Timeline grouped by day --}}
        <div class="relative space-y-10">
            @forelse ($groups as $date => $items)
                <section>
                    <h2 class="mb-4 text-lg font-bold">
                        {{ $dayLabel($date) }}
                        <span class="ml-2 text-sm font-normal text-zinc-500">
                            {{ $items->count() }} {{ \Illuminate\Support\Str::plural('change', $items->count()) }}
                        </span>
                    </h2>

                    <ol class="ml-4 space-y-4 border-l-2 border-zinc-200 dark:border-zinc-800">
                        @foreach ($items as $log)
                            @php
                                $style   = $actionStyles[$log->action] ?? $fallback;
                                $subject = \Illuminate\Support\Str::singular($subjects[$log->subject_type] ?? $log->subject_type);
                                $isBulk  = in_array($log->action, ['created', 'deleted'], true);
                            @endphp

                            <li class="relative pl-8">
                                {{-- Icon on the timeline --}}
                                <span class="absolute -left-[17px] top-3 flex h-8 w-8 items-center justify-center rounded-full ring-4 ring-white dark:ring-zinc-950 {{ $style['icon'] }}">
                                    <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <path d="{{ $style['path'] }}"/>
                                    </svg>
                                </span>

                                <article class="rounded-2xl border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
                                    {{-- 1. Plain sentence: who did what to what --}}
                                    <div class="flex items-start justify-between gap-3">
                                        <p class="text-[15px] leading-relaxed">
                                            <b>
                                                @if ($log->member_id === null)
                                                    {{-- empty member_id = done from terminal (php artisan member:role) --}}
                                                    System (terminal)
                                                @else
                                                    {{ $log->member?->name ?? 'Unknown admin' }}
                                                @endif
                                            </b>
                                            <span class="font-semibold {{ $style['verb'] }}">{{ $style['text'] }}</span>
                                            {{ $subject }}
                                            <b>{{ $log->description ?: '#' . $log->subject_id }}</b>
                                        </p>

                                        <time class="shrink-0 pt-0.5 text-xs tabular-nums text-zinc-500"
                                              title="{{ $local($log->created_at)?->format('d/m/Y H:i:s') }}">
                                            {{ $local($log->created_at)?->format('H:i') }}
                                        </time>
                                    </div>

                                    {{-- 2. What exactly changed --}}
                                    @if ($log->changes)
                                        <ul class="mt-3 space-y-1.5 border-t border-zinc-100 pt-3 text-sm dark:border-zinc-800">
                                            @foreach ($log->changes as $column => [$old, $new])
                                                <li class="flex flex-wrap items-baseline gap-x-2">
                                                    <span class="w-40 shrink-0 text-zinc-500">{{ Str::headline($column) }}</span>

                                                    @if ($log->action === 'created')
                                                        <span class="font-semibold">{{ $show($new) }}</span>
                                                    @elseif ($log->action === 'deleted')
                                                        <span class="text-zinc-500">{{ $show($old) }}</span>
                                                    @else
                                                        <span class="text-red-500 line-through decoration-1">{{ $show($old) }}</span>
                                                        <span class="text-zinc-400">→</span>
                                                        <span class="font-semibold text-emerald-600 dark:text-emerald-400">{{ $show($new) }}</span>
                                                    @endif
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif

                                    {{-- 3. Where (small, last) --}}
                                    <div class="mt-3 text-xs text-zinc-500">
                                        {{ $subjects[$log->subject_type] ?? $log->subject_type }} · ID {{ $log->subject_id }}
                                    </div>
                                </article>
                            </li>
                        @endforeach
                    </ol>
                </section>
            @empty
                <div class="rounded-xl border border-dashed border-zinc-300 p-10 text-center text-sm text-zinc-500 dark:border-zinc-700">
                    No activity matches these filters.
                    <div class="mt-3">
                        <a href="{{ route('admin.logs.index') }}" class="text-red-500 hover:underline">Clear filters</a>
                    </div>
                </div>
            @endforelse
        </div>

        <div class="relative mt-8">
            {{ $logs->links() }}
        </div>
    </div>
</x-layouts::app>