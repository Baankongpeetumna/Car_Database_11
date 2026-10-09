@php
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\Str;

    // ---- Adjust variable / route names here to match the project ----
    $q         = request('q');
    $roleValue = request('role');
    $tierValue = request('tier_id');
    $tierList  = $tiers ?? collect();
    $total     = method_exists($members, 'total') ? $members->total() : $members->count();

    // Optional counts from the controller. Hidden when not provided.
    $roleCounts = $roleCounts ?? null;   // ['all' => 3, 'admin' => 1, 'member' => 2]
    $tierCounts = $tierCounts ?? [];     // [tier_id => count]

    $indexUrl  = Route::has('admin.members.index') ? route('admin.members.index') : url()->current();
    $hasFilter = filled($q) || filled($roleValue) || filled($tierValue);
    $with      = fn (array $changes) => request()->fullUrlWithQuery($changes + ['page' => null]);

    // One color per tier, matching the Membership Tiers page (full class names so Tailwind keeps them)
    $tierTheme = [
        'basic'    => ['bar' => 'from-zinc-300 to-zinc-400',      'avatar' => 'bg-zinc-600',    'badge' => 'bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300',             'dot' => 'bg-zinc-400',    'on' => 'border-zinc-700 bg-zinc-700 text-white',       'stat' => 'bg-zinc-50 dark:bg-zinc-800/60'],
        'silver'   => ['bar' => 'from-sky-300 to-cyan-400',       'avatar' => 'bg-sky-500',     'badge' => 'bg-sky-100 text-sky-700 dark:bg-sky-950/60 dark:text-sky-300',              'dot' => 'bg-sky-500',     'on' => 'border-sky-500 bg-sky-500 text-white',         'stat' => 'bg-sky-50 dark:bg-sky-950/30'],
        'gold'     => ['bar' => 'from-amber-300 to-amber-500',    'avatar' => 'bg-amber-500',   'badge' => 'bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300',      'dot' => 'bg-amber-500',   'on' => 'border-amber-500 bg-amber-500 text-white',     'stat' => 'bg-amber-50 dark:bg-amber-950/30'],
        'platinum' => ['bar' => 'from-violet-400 to-fuchsia-500', 'avatar' => 'bg-violet-600',  'badge' => 'bg-violet-100 text-violet-700 dark:bg-violet-950/60 dark:text-violet-300',  'dot' => 'bg-violet-500',  'on' => 'border-violet-600 bg-violet-600 text-white',   'stat' => 'bg-violet-50 dark:bg-violet-950/30'],
        'diamond'  => ['bar' => 'from-emerald-400 to-teal-500',   'avatar' => 'bg-emerald-600', 'badge' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300','dot' => 'bg-emerald-500', 'on' => 'border-emerald-600 bg-emerald-600 text-white', 'stat' => 'bg-emerald-50 dark:bg-emerald-950/30'],
        'elite'    => ['bar' => 'from-pink-400 to-rose-500',      'avatar' => 'bg-pink-600',    'badge' => 'bg-pink-100 text-pink-700 dark:bg-pink-950/60 dark:text-pink-300',          'dot' => 'bg-pink-500',    'on' => 'border-pink-600 bg-pink-600 text-white',       'stat' => 'bg-pink-50 dark:bg-pink-950/30'],
        'king'     => ['bar' => 'from-red-500 to-orange-500',     'avatar' => 'bg-red-600',     'badge' => 'bg-red-100 text-red-700 dark:bg-red-950/60 dark:text-red-300',              'dot' => 'bg-red-500',     'on' => 'border-red-600 bg-red-600 text-white',         'stat' => 'bg-red-50 dark:bg-red-950/30'],
    ];

    // Tiers added later (not named above) get a stable color from this list by tier id, never plain gray
    $extraThemes = [
        ['bar' => 'from-teal-400 to-cyan-500',    'avatar' => 'bg-teal-600',   'badge' => 'bg-teal-100 text-teal-700 dark:bg-teal-950/60 dark:text-teal-300',       'dot' => 'bg-teal-500',   'on' => 'border-teal-600 bg-teal-600 text-white',     'stat' => 'bg-teal-50 dark:bg-teal-950/30'],
        ['bar' => 'from-orange-400 to-red-500',   'avatar' => 'bg-orange-600', 'badge' => 'bg-orange-100 text-orange-700 dark:bg-orange-950/60 dark:text-orange-300', 'dot' => 'bg-orange-500', 'on' => 'border-orange-600 bg-orange-600 text-white', 'stat' => 'bg-orange-50 dark:bg-orange-950/30'],
        ['bar' => 'from-indigo-400 to-blue-500',  'avatar' => 'bg-indigo-600', 'badge' => 'bg-indigo-100 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-300', 'dot' => 'bg-indigo-500', 'on' => 'border-indigo-600 bg-indigo-600 text-white', 'stat' => 'bg-indigo-50 dark:bg-indigo-950/30'],
        ['bar' => 'from-lime-400 to-green-500',   'avatar' => 'bg-lime-600',   'badge' => 'bg-lime-100 text-lime-700 dark:bg-lime-950/60 dark:text-lime-300',       'dot' => 'bg-lime-500',   'on' => 'border-lime-600 bg-lime-600 text-white',     'stat' => 'bg-lime-50 dark:bg-lime-950/30'],
    ];

    $themeFor = function ($name, $id) use ($tierTheme, $extraThemes) {
        $key = Str::lower(trim((string) $name));

        return $tierTheme[$key] ?? $extraThemes[abs((int) $id) % count($extraThemes)];
    };

    // Role segments: each has its own color when active
    $roleTabs = [
        ['value' => '',       'label' => 'All',    'key' => 'all',    'on' => 'bg-zinc-900 text-white dark:bg-white dark:text-zinc-900'],
        ['value' => 'admin',  'label' => 'Admin',  'key' => 'admin',  'on' => 'bg-red-600 text-white'],
        ['value' => 'member', 'label' => 'Member', 'key' => 'member', 'on' => 'bg-sky-600 text-white'],
    ];
@endphp

<x-layouts::app :title="'Members'">
    <div class="min-h-full bg-zinc-50 dark:bg-zinc-950">
        <div class="mx-auto max-w-7xl space-y-5 p-2 sm:p-4">

            {{-- Header: no card, sits directly on the page --}}
            <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <div class="flex items-center gap-2 text-sm text-zinc-400">
                        <a href="{{ route('admin.dashboard') }}" class="hover:text-red-600">Admin</a>
                        <span>/</span>
                        <span class="text-zinc-600 dark:text-zinc-300">Members</span>
                    </div>

                    <h1 class="font-display mt-1 text-4xl font-black uppercase italic leading-none tracking-tight
                               text-zinc-900 sm:text-5xl dark:text-white">
                        Members
                    </h1>
                    <div class="mt-2 h-1 w-14 -skew-x-12 bg-red-600"></div>
                </div>

                <p class="shrink-0 pb-1 text-sm text-zinc-500">
                    <span class="font-semibold text-zinc-900 dark:text-white">{{ number_format($total) }}</span>
                    {{ Str::plural('account', $total) }}
                </p>
            </div>


            {{-- One toolbar: role + search on top, tier below --}}
            <div class="rounded-2xl border border-zinc-200 bg-white p-3 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">

                <div class="flex flex-col gap-3 lg:flex-row lg:items-center">

                    {{-- Role segmented control --}}
                    <div class="inline-flex shrink-0 rounded-xl bg-zinc-100 p-1 dark:bg-zinc-800" role="tablist" aria-label="Role">
                        @foreach ($roleTabs as $tab)
                            @php
                                $active = (string) $roleValue === $tab['value'];
                                $count  = $roleCounts[$tab['key']] ?? null;
                            @endphp
                            <a href="{{ $with(['role' => $tab['value'] ?: null]) }}"
                               class="inline-flex items-center gap-1.5 rounded-lg px-4 py-1.5 text-sm font-semibold transition
                                      {{ $active
                                            ? $tab['on'].' shadow-sm'
                                            : 'text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white' }}">
                                {{ $tab['label'] }}
                                @if ($count !== null)
                                    <span class="text-xs {{ $active ? 'opacity-80' : 'text-zinc-400' }}">{{ $count }}</span>
                                @endif
                            </a>
                        @endforeach
                    </div>

                    {{-- Search --}}
                    <form method="GET" action="{{ $indexUrl }}" class="flex flex-1 gap-2">
                        @if ($roleValue) <input type="hidden" name="role" value="{{ $roleValue }}"> @endif
                        @if ($tierValue) <input type="hidden" name="tier_id" value="{{ $tierValue }}"> @endif

                        <div class="relative flex-1">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                 stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"
                                 class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-zinc-400">
                                <circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>
                            </svg>
                            <input type="text" name="q" value="{{ $q }}" placeholder="Search name or email"
                                   class="h-10 w-full rounded-xl border border-zinc-200 bg-zinc-50 pl-10 pr-3 text-sm text-zinc-900
                                          placeholder:text-zinc-400 focus:border-red-500 focus:outline-none focus:ring-2 focus:ring-red-500/20
                                          dark:border-zinc-700 dark:bg-zinc-800 dark:text-white">
                        </div>
                        <button type="submit"
                                class="h-10 rounded-xl bg-red-600 px-5 text-sm font-semibold text-white transition hover:bg-red-500
                                       focus:outline-none focus:ring-2 focus:ring-red-500/40">
                            Search
                        </button>
                    </form>
                </div>

                {{-- Tier row + result summary --}}
                @if ($tierList->isNotEmpty() || $hasFilter)
                    <div class="mt-3 flex flex-wrap items-center gap-2 border-t border-zinc-100 pt-3 dark:border-zinc-800">

                        @if ($tierList->isNotEmpty())
                            <span class="mr-1 text-xs font-medium text-zinc-500">Tier</span>

                            <a href="{{ $with(['tier_id' => null]) }}"
                               class="rounded-full border px-3 py-1 text-xs font-semibold transition
                                      {{ blank($tierValue)
                                            ? 'border-zinc-900 bg-zinc-900 text-white dark:border-white dark:bg-white dark:text-zinc-900'
                                            : 'border-zinc-200 text-zinc-600 hover:border-zinc-400 dark:border-zinc-700 dark:text-zinc-300' }}">
                                All
                            </a>

                            @foreach ($tierList as $t)
                                @php
                                    $tid   = $t->getKey();
                                    $tname = $t->tier_name ?? $t->name ?? $tid;
                                    $theme = $themeFor($tname, $tid);
                                    $on    = (string) $tierValue === (string) $tid;
                                    $tc    = $tierCounts[$tid] ?? null;
                                @endphp
                                <a href="{{ $with(['tier_id' => $tid]) }}"
                                   class="inline-flex items-center gap-1.5 rounded-full border px-3 py-1 text-xs font-semibold transition
                                          {{ $on
                                                ? $theme['on']
                                                : 'border-zinc-200 text-zinc-700 hover:border-zinc-400 dark:border-zinc-700 dark:text-zinc-300' }}">
                                    <span class="h-2 w-2 rounded-full {{ $on ? 'bg-white' : $theme['dot'] }}"></span>
                                    {{ $tname }}
                                    @if ($tc !== null)
                                        <span class="{{ $on ? 'text-white/80' : 'text-zinc-400' }}">{{ $tc }}</span>
                                    @endif
                                </a>
                            @endforeach
                        @endif

                        @if ($hasFilter)
                            <div class="ml-auto flex items-center gap-3 text-xs text-zinc-500">
                                <span>{{ number_format($total) }} {{ Str::plural('result', $total) }}</span>
                                <a href="{{ $indexUrl }}" class="font-semibold text-red-600 hover:text-red-700">Clear all</a>
                            </div>
                        @endif
                    </div>
                @endif
            </div>


            {{-- Member cards --}}
            @if ($members->isEmpty())
                <div class="rounded-2xl border border-dashed border-zinc-300 bg-white px-6 py-16 text-center dark:border-zinc-700 dark:bg-zinc-900">
                    <p class="text-lg font-semibold text-zinc-900 dark:text-white">No members match your filters</p>
                    <p class="mt-1 text-sm text-zinc-500">Try a different search or clear the filters to see everyone.</p>
                    <a href="{{ $indexUrl }}"
                       class="mt-5 inline-flex items-center justify-center rounded-xl bg-red-600 px-5 py-2.5 text-sm font-semibold text-white
                              transition hover:bg-red-500">
                        Show all members
                    </a>
                </div>
            @else
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">
                    @foreach ($members as $member)
                        @php
                            $isAdmin   = ($member->role ?? null) === 'admin' || ($member->is_admin ?? false);
                            $isMe      = auth()->id() === $member->getKey();
                            $ordersCnt = (int) ($member->orders_count ?? 0);
                            $tierName  = $member->tier?->tier_name ?? $member->tier?->name ?? 'Basic';
                            $theme     = $themeFor($tierName, $member->tier_id);
                            $initial   = Str::upper(Str::substr($member->name ?? '?', 0, 1));
                            $editUrl   = Route::has('admin.members.edit') ? route('admin.members.edit', $member->getKey()) : '#';
                            $deleteUrl = Route::has('admin.members.destroy') ? route('admin.members.destroy', $member->getKey()) : null;
                            $canDelete = $deleteUrl && !$isMe && !$isAdmin && $ordersCnt === 0;
                        @endphp

                        <div class="relative flex flex-col overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm
                                    dark:border-zinc-800 dark:bg-zinc-900">

                            {{-- Tier color bar --}}
                            <div class="h-1.5 w-full bg-gradient-to-r {{ $theme['bar'] }}"></div>

                            <div class="flex flex-1 flex-col p-5">

                                <div class="flex items-start gap-4">
                                    <div class="grid h-14 w-14 shrink-0 place-items-center rounded-2xl text-xl font-bold text-white {{ $theme['avatar'] }}">
                                        {{ $initial }}
                                    </div>

                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-start justify-between gap-2">
                                            <p class="truncate text-base font-semibold text-zinc-900 dark:text-white" title="{{ $member->name }}">
                                                {{ $member->name }}
                                            </p>
                                            <span class="shrink-0 text-[11px] text-zinc-400">#{{ $member->getKey() }}</span>
                                        </div>
                                        <p class="truncate text-sm text-zinc-500">{{ $member->email }}</p>
                                        @if (!empty($member->phone))
                                            <p class="text-sm text-zinc-400">{{ $member->phone }}</p>
                                        @endif
                                    </div>
                                </div>

                                {{-- Badges --}}
                                <div class="mt-4 flex flex-wrap items-center gap-1.5">
                                    @if ($isAdmin)
                                        <span class="rounded-full bg-red-100 px-2.5 py-1 text-[11px] font-semibold text-red-700 dark:bg-red-950/60 dark:text-red-300">Admin</span>
                                    @else
                                        <span class="rounded-full bg-sky-100 px-2.5 py-1 text-[11px] font-semibold text-sky-700 dark:bg-sky-950/60 dark:text-sky-300">Member</span>
                                    @endif

                                    <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-[11px] font-semibold {{ $theme['badge'] }}">
                                        <span class="h-1.5 w-1.5 rounded-full {{ $theme['dot'] }}"></span>{{ $tierName }}
                                    </span>

                                    @if ($isMe)
                                        <span class="rounded-full bg-emerald-100 px-2.5 py-1 text-[11px] font-semibold text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300">You</span>
                                    @endif
                                </div>

                                {{-- Stats: one tinted strip with a divider (instead of two boxes) --}}
                                <div class="mt-4 grid grid-cols-2 divide-x divide-zinc-200/80 rounded-xl py-3 dark:divide-zinc-700/60 {{ $theme['stat'] }}">
                                    <div class="px-4">
                                        <p class="text-xs text-zinc-500">Points</p>
                                        <p class="font-display mt-0.5 text-2xl font-bold leading-tight text-zinc-900 dark:text-white">
                                            {{ number_format((int) ($member->points ?? 0)) }}
                                        </p>
                                    </div>
                                    <div class="px-4">
                                        <p class="text-xs text-zinc-500">Orders</p>
                                        <p class="font-display mt-0.5 text-2xl font-bold leading-tight text-zinc-900 dark:text-white">{{ $ordersCnt }}</p>
                                    </div>
                                </div>

                                {{-- Actions --}}
                                <div class="mt-5 flex items-center gap-2">
                                    <a href="{{ $editUrl }}"
                                       class="inline-flex h-10 flex-1 items-center justify-center gap-2 rounded-xl bg-red-600 px-4 text-sm font-semibold
                                              text-white transition hover:bg-red-500 focus:outline-none focus:ring-2 focus:ring-red-500/40">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>
                                        Edit
                                    </a>

                                    @if ($canDelete)
                                        <form method="POST" action="{{ $deleteUrl }}"
                                              onsubmit="return confirm('Delete {{ e($member->name) }}? This cannot be undone.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" title="Delete member" aria-label="Delete member"
                                                    class="grid h-10 w-10 place-items-center rounded-xl border border-zinc-300 bg-white text-zinc-500
                                                           transition hover:border-red-600 hover:bg-red-600 hover:text-white
                                                           dark:border-zinc-700 dark:bg-zinc-800">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 6h18"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"/><path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                            </button>
                                        </form>
                                    @else
                                        <span class="grid h-10 w-10 cursor-not-allowed place-items-center rounded-xl border border-dashed border-zinc-300
                                                     text-zinc-300 dark:border-zinc-700 dark:text-zinc-600"
                                              title="{{ $ordersCnt > 0 ? 'Has order history, so it can\'t be deleted' : 'This account can\'t be deleted' }}">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect width="18" height="11" x="3" y="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                                        </span>
                                    @endif
                                </div>

                                @if (!$canDelete && $ordersCnt > 0)
                                    <p class="mt-2 text-[11px] text-zinc-400">Has order history, so it can't be deleted.</p>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>

                @if (method_exists($members, 'hasPages') && $members->hasPages())
                    <div class="pt-2">
                        {{ $members->withQueryString()->links() }}
                    </div>
                @endif
            @endif

        </div>
    </div>
</x-layouts::app>