@php
    use App\Support\TierVisual;
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\Str;

    $q = request('q');
    $roleValue = request('role');
    $tierValue = request('tier_id');

    $tierList = $tiers ?? collect();
    $total = method_exists($members, 'total')
        ? $members->total()
        : $members->count();

    $roleCounts = $roleCounts ?? null;
    $tierCounts = $tierCounts ?? [];

    $indexUrl = Route::has('admin.members.index')
        ? route('admin.members.index')
        : url()->current();

    $hasFilter = filled($q) || filled($roleValue) || filled($tierValue);

    $with = fn (array $changes) => request()->fullUrlWithQuery(
        $changes + ['page' => null]
    );

    $roleTabs = [
        [
            'value' => '',
            'label' => 'All',
            'key' => 'all',
            'on' => 'bg-zinc-900 text-white dark:bg-white dark:text-zinc-900',
        ],
        [
            'value' => 'admin',
            'label' => 'Admin',
            'key' => 'admin',
            'on' => 'bg-red-600 text-white',
        ],
        [
            'value' => 'member',
            'label' => 'Member',
            'key' => 'member',
            'on' => 'bg-sky-600 text-white',
        ],
    ];
@endphp

<x-layouts::app :title="'Members'">
    @include('partials.tier-colors')

    <div class="min-h-full bg-zinc-50 dark:bg-zinc-950">
        <div class="mx-auto max-w-7xl space-y-5 p-2 sm:p-4">

            @include('commerce.messages')

            <header class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <nav class="flex items-center gap-2 text-sm text-zinc-400"
                         aria-label="Breadcrumb">
                        <a href="{{ route('admin.dashboard') }}"
                           class="hover:text-red-600">
                            Admin
                        </a>
                        <span>/</span>
                        <span class="text-zinc-600 dark:text-zinc-300">
                            Members
                        </span>
                    </nav>

                    <h1 class="font-display mt-1 text-4xl font-black uppercase italic leading-none tracking-tight text-zinc-900 sm:text-5xl dark:text-white">
                        Members
                    </h1>

                    <div class="mt-2 h-1 w-14 -skew-x-12 bg-red-600"></div>
                </div>

                <p class="shrink-0 pb-1 text-sm text-zinc-500">
                    <span class="font-semibold text-zinc-900 dark:text-white">
                        {{ number_format($total) }}
                    </span>
                    {{ Str::plural('account', $total) }}
                </p>
            </header>

            <div class="rounded-2xl border border-zinc-200 bg-white p-3 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                <div class="flex flex-col gap-3 lg:flex-row lg:items-center">

                    <div class="inline-flex shrink-0 rounded-xl bg-zinc-100 p-1 dark:bg-zinc-800"
                         aria-label="Filter by role">
                        @foreach ($roleTabs as $tab)
                            @php
                                $active = (string) $roleValue === $tab['value'];
                                $count = $roleCounts[$tab['key']] ?? null;
                            @endphp

                            <a href="{{ $with(['role' => $tab['value'] ?: null]) }}"
                               @if ($active) aria-current="true" @endif
                               class="inline-flex items-center gap-1.5 rounded-lg px-4 py-1.5 text-sm font-semibold transition {{ $active ? $tab['on'].' shadow-sm' : 'text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white' }}">
                                {{ $tab['label'] }}

                                @if ($count !== null)
                                    <span class="text-xs {{ $active ? 'opacity-80' : 'text-zinc-400' }}">
                                        {{ number_format($count) }}
                                    </span>
                                @endif
                            </a>
                        @endforeach
                    </div>

                    <form method="GET"
                          action="{{ $indexUrl }}"
                          class="flex flex-1 gap-2">
                        @if (filled($roleValue))
                            <input type="hidden" name="role" value="{{ $roleValue }}">
                        @endif

                        @if (filled($tierValue))
                            <input type="hidden" name="tier_id" value="{{ $tierValue }}">
                        @endif

                        <div class="relative flex-1">
                            <svg xmlns="http://www.w3.org/2000/svg"
                                 width="18" height="18" viewBox="0 0 24 24"
                                 fill="none" stroke="currentColor"
                                 stroke-width="2" stroke-linecap="round"
                                 stroke-linejoin="round" aria-hidden="true"
                                 class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-zinc-400">
                                <circle cx="11" cy="11" r="8"/>
                                <path d="m21 21-4.3-4.3"/>
                            </svg>

                            <input type="text"
                                   name="q"
                                   value="{{ $q }}"
                                   placeholder="Search name or email"
                                   aria-label="Search name or email"
                                   class="h-10 w-full rounded-xl border border-zinc-200 bg-zinc-50 pl-10 pr-3 text-sm text-zinc-900 placeholder:text-zinc-400 focus:border-red-500 focus:outline-none focus:ring-2 focus:ring-red-500/20 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white">
                        </div>

                        <button type="submit"
                                class="h-10 rounded-xl bg-red-600 px-5 text-sm font-semibold text-white transition hover:bg-red-500 focus:outline-none focus:ring-2 focus:ring-red-500/40">
                            Search
                        </button>
                    </form>
                </div>

                @if ($tierList->isNotEmpty() || $hasFilter)
                    <div class="mt-3 flex flex-wrap items-center gap-2 border-t border-zinc-100 pt-3 dark:border-zinc-800">

                        @if ($tierList->isNotEmpty())
                            <span class="mr-1 text-xs font-medium text-zinc-500">
                                Tier
                            </span>

                            <a href="{{ $with(['tier_id' => null]) }}"
                               class="rounded-full border px-3 py-1 text-xs font-semibold transition {{ blank($tierValue) ? 'border-zinc-900 bg-zinc-900 text-white dark:border-white dark:bg-white dark:text-zinc-900' : 'border-zinc-200 text-zinc-600 hover:border-zinc-400 dark:border-zinc-700 dark:text-zinc-300' }}">
                                All
                            </a>

                            @foreach ($tierList as $tier)
                                @php
                                    $tid = $tier->getKey();
                                    $on = (string) $tierValue === (string) $tid;
                                    $count = $tierCounts[$tid] ?? null;
                                @endphp

                                <a href="{{ $with(['tier_id' => $tid]) }}"
                                   style="{{ TierVisual::style($tier) }}"
                                   @if ($on) aria-current="true" @endif
                                   class="inline-flex items-center gap-1.5 rounded-full border px-3 py-1 text-xs font-semibold transition {{ $on ? 'tier-on' : 'border-zinc-200 text-zinc-700 hover:border-zinc-400 dark:border-zinc-700 dark:text-zinc-300' }}">
                                    <span class="tier-dot h-2 w-2 rounded-full"></span>

                                    {{ $tier->tier_name }}

                                    @if ($count !== null)
                                        <span class="opacity-75">
                                            {{ number_format($count) }}
                                        </span>
                                    @endif
                                </a>
                            @endforeach
                        @endif

                        @if ($hasFilter)
                            <div class="ml-auto flex items-center gap-3 text-xs text-zinc-500">
                                <span>
                                    {{ number_format($total) }}
                                    {{ Str::plural('result', $total) }}
                                </span>

                                <a href="{{ $indexUrl }}"
                                   class="font-semibold text-red-600 hover:text-red-700">
                                    Clear all
                                </a>
                            </div>
                        @endif
                    </div>
                @endif
            </div>

            @if ($members->isEmpty())
                <div class="rounded-2xl border border-dashed border-zinc-300 bg-white px-6 py-16 text-center dark:border-zinc-700 dark:bg-zinc-900">
                    <p class="text-lg font-semibold text-zinc-900 dark:text-white">
                        No members match your filters
                    </p>

                    <p class="mt-1 text-sm text-zinc-500">
                        Try a different search or clear the filters to see everyone.
                    </p>

                    <a href="{{ $indexUrl }}"
                       class="mt-5 inline-flex items-center justify-center rounded-xl bg-red-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-red-500">
                        Show all members
                    </a>
                </div>
            @else
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">
                    @foreach ($members as $member)
                        @php
                            $isAdmin = $member->role === 'admin';
                            $isMe = (int) auth()->id() === (int) $member->getKey();
                            $ordersCnt = (int) ($member->orders_count ?? 0);
                            $tier = $member->tier;
                            $tierName = $tier?->tier_name ?? 'No tier';
                            $initial = Str::upper(Str::substr($member->name ?: '?', 0, 1));

                            $editUrl = Route::has('admin.members.edit')
                                ? route('admin.members.edit', $member)
                                : '#';

                            $deleteUrl = Route::has('admin.members.destroy')
                                ? route('admin.members.destroy', $member)
                                : null;

                            $canDelete = $deleteUrl
                                && !$isMe
                                && !$isAdmin
                                && $ordersCnt === 0;
                        @endphp

                        <article style="{{ TierVisual::style($tier) }}"
                                 class="relative flex flex-col overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">

                            <div class="tier-bar h-1.5 w-full"></div>

                            <div class="flex flex-1 flex-col p-5">
                                <div class="flex items-start gap-4">
                                    <div class="tier-avatar grid h-14 w-14 shrink-0 place-items-center rounded-2xl text-xl font-bold">
                                        {{ $initial }}
                                    </div>

                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-start justify-between gap-2">
                                            <p class="truncate text-base font-semibold text-zinc-900 dark:text-white"
                                               title="{{ $member->name }}">
                                                {{ $member->name }}
                                            </p>

                                            <span class="shrink-0 text-[11px] text-zinc-400">
                                                #{{ $member->getKey() }}
                                            </span>
                                        </div>

                                        <p class="truncate text-sm text-zinc-500">
                                            {{ $member->email }}
                                        </p>

                                        @if (filled($member->phone))
                                            <p class="text-sm text-zinc-400">
                                                {{ $member->phone }}
                                            </p>
                                        @endif
                                    </div>
                                </div>

                                <div class="mt-4 flex flex-wrap items-center gap-1.5">
                                    @if ($isAdmin)
                                        <span class="rounded-full bg-red-100 px-2.5 py-1 text-[11px] font-semibold text-red-700 dark:bg-red-950/60 dark:text-red-300">
                                            Admin
                                        </span>
                                    @else
                                        <span class="rounded-full bg-sky-100 px-2.5 py-1 text-[11px] font-semibold text-sky-700 dark:bg-sky-950/60 dark:text-sky-300">
                                            Member
                                        </span>
                                    @endif

                                    <span class="tier-badge inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-[11px] font-semibold">
                                        <span class="tier-dot h-1.5 w-1.5 rounded-full"></span>
                                        {{ $tierName }}
                                    </span>

                                    @if ($isMe)
                                        <span class="rounded-full bg-emerald-100 px-2.5 py-1 text-[11px] font-semibold text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300">
                                            You
                                        </span>
                                    @endif
                                </div>

                                <div class="tier-stat mt-4 grid grid-cols-2 divide-x divide-zinc-200/80 rounded-xl py-3 dark:divide-zinc-700/60">
                                    <div class="px-4">
                                        <p class="text-xs text-zinc-500">Points</p>
                                        <p class="font-display mt-0.5 text-2xl font-bold leading-tight text-zinc-900 dark:text-white">
                                            {{ number_format((int) $member->points) }}
                                        </p>
                                    </div>

                                    <div class="px-4">
                                        <p class="text-xs text-zinc-500">Orders</p>
                                        <p class="font-display mt-0.5 text-2xl font-bold leading-tight text-zinc-900 dark:text-white">
                                            {{ number_format($ordersCnt) }}
                                        </p>
                                    </div>
                                </div>

                                <div class="mt-5 flex items-center gap-2">
                                    <a href="{{ $editUrl }}"
                                       class="inline-flex h-10 flex-1 items-center justify-center rounded-xl bg-red-600 px-4 text-sm font-semibold text-white transition hover:bg-red-500 focus:outline-none focus:ring-2 focus:ring-red-500/40">
                                        Edit
                                    </a>

                                    @if ($canDelete)
                                        <form method="POST"
                                              action="{{ $deleteUrl }}"
                                              data-confirm="Delete {{ $member->name }}? This cannot be undone."
                                              data-confirm-ok="Delete">
                                            @csrf
                                            @method('DELETE')

                                            <button type="submit"
                                                    class="h-10 rounded-xl border border-zinc-300 px-3 text-sm font-semibold text-red-600 transition hover:border-red-600 hover:bg-red-600 hover:text-white dark:border-zinc-700">
                                                Delete
                                            </button>
                                        </form>
                                    @else
                                        <span class="px-2 text-xs text-zinc-400">
                                            {{ $isMe ? 'Your account' : ($isAdmin ? 'Admin account' : 'Has orders') }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif

            @if (method_exists($members, 'links'))
                <div class="pt-2">
                    {{ $members->withQueryString()->links() }}
                </div>
            @endif
        </div>
    </div>
</x-layouts::app>