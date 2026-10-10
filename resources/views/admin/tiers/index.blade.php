@php
    use Illuminate\Support\Str;

    $list = collect($tiers)->values();
    $count = $list->count();

    $percent = fn ($value) => rtrim(
        rtrim(number_format((float) $value, 2, '.', ''), '0'),
        '.'
    ) ?: '0';

    $totalMembers = (int) $list->sum('members_count');
    $topDiscount = $count ? (float) $list->max('discount_percent') : 0;

    $stepRem = $count > 1 ? min(1.75, 7 / ($count - 1)) : 0;
@endphp

<x-layouts::app :title="'Manage Membership Tiers'">
    @include('partials.tier-colors')

    <div class="min-h-full bg-zinc-50 dark:bg-zinc-950">
        <div class="mx-auto max-w-[1600px] space-y-6 p-2 sm:p-4">

            @include('commerce.messages')

            {{-- Header --}}
            <header class="px-1">
                <nav class="flex items-center gap-2 text-xs text-zinc-500"
                     aria-label="Breadcrumb">
                    <a href="{{ route('admin.dashboard') }}"
                       class="transition hover:text-red-600">
                        Admin
                    </a>
                    <span>/</span>
                    <span class="text-zinc-900 dark:text-white">
                        Membership Tiers
                    </span>
                </nav>

                <div class="mt-2 flex flex-wrap items-end justify-between gap-4">
                    <h1 class="font-display mt-1 text-4xl font-black uppercase italic leading-none tracking-tight text-zinc-900 sm:text-5xl dark:text-white">
                        Membership Tiers
                    </h1>

                    <a href="{{ route('admin.tiers.create') }}"
                       class="inline-flex h-10 items-center justify-center gap-2 rounded-xl bg-red-600 px-5 text-sm font-semibold text-white shadow-lg shadow-red-600/20 transition hover:bg-red-500 focus:outline-none focus:ring-2 focus:ring-red-500/40">
                        <span class="text-lg leading-none">+</span>
                        Add tier
                    </a>
                </div>

                <p class="mt-3 max-w-2xl text-sm text-zinc-500">
                    Members move up a tier when a completed order brings their
                    points to the tier's minimum (1 point per ฿1,000).
                    Tiers are sorted by minimum points.
                </p>

                @if ($count)
                    <div class="mt-4 flex flex-wrap gap-2 text-xs">
                        <span class="rounded-full border border-zinc-200 bg-white px-3 py-1 text-zinc-600 dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-300">
                            <span class="font-semibold text-zinc-900 dark:text-white">
                                {{ $count }}
                            </span>
                            {{ Str::plural('tier', $count) }}
                        </span>

                        <span class="rounded-full border border-zinc-200 bg-white px-3 py-1 text-zinc-600 dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-300">
                            <span class="font-semibold text-zinc-900 dark:text-white">
                                {{ number_format($totalMembers) }}
                            </span>
                            {{ Str::plural('member', $totalMembers) }} enrolled
                        </span>

                        <span class="rounded-full border border-zinc-200 bg-white px-3 py-1 text-zinc-600 dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-300">
                            Up to
                            <span class="font-semibold text-emerald-600 dark:text-emerald-400">
                                {{ $percent($topDiscount) }}%
                            </span>
                            discount
                        </span>
                    </div>
                @endif
            </header>

            @if ($count)
                @if ($count > 7)
                    <p class="hidden px-1 text-xs text-zinc-500 lg:block">
                        Scroll sideways to see all {{ $count }} tiers →
                    </p>
                @elseif ($count > 5)
                    <p class="hidden px-1 text-xs text-zinc-500 lg:block 2xl:hidden">
                        Scroll sideways to see all {{ $count }} tiers →
                    </p>
                @endif

                {{-- Tier staircase --}}
                <div class="grid gap-4 sm:grid-cols-2 lg:snap-x lg:snap-proximity lg:grid-flow-col lg:grid-cols-none lg:auto-cols-[minmax(11.5rem,1fr)] lg:overflow-x-auto lg:px-1 lg:pb-5 lg:pt-2">

                    @foreach ($list as $tier)
                        @php
                            $isBase = (int) $tier->min_points === 0;
                            $next = $list[$loop->index + 1] ?? null;
                            $lift = ($count - 1 - $loop->index) * $stepRem;

                            $range = $next
                                ? number_format($tier->min_points)
                                    .' – '
                                    .number_format(max(
                                        $next->min_points - 1,
                                        $tier->min_points
                                    ))
                                : number_format($tier->min_points).'+';
                        @endphp

                        <article
                            class="relative flex flex-col overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm transition duration-200 hover:-translate-y-1 hover:shadow-lg dark:border-zinc-800 dark:bg-zinc-900 lg:mt-[var(--lift)] lg:snap-start"
                            style="--lift: {{ $lift }}rem; {{ \App\Support\TierVisual::style($tier) }}"
                        >
                            {{-- แถบสีตาม Tier --}}
                            <div class="tier-bar h-1.5"></div>

                            {{-- ชื่อและส่วนลด --}}
                            <div class="tier-tint relative px-5 pb-5 pt-5">
                                <span
                                    aria-hidden="true"
                                    class="pointer-events-none absolute -right-1 top-1 font-display text-7xl font-black italic leading-none text-zinc-900/[0.05] dark:text-white/[0.06]"
                                >
                                    {{ str_pad(
                                        (string) $loop->iteration,
                                        2,
                                        '0',
                                        STR_PAD_LEFT
                                    ) }}
                                </span>

                                <div class="relative flex flex-wrap items-center gap-2">
                                    <span class="tier-badge inline-flex items-center gap-2 rounded-full px-3 py-1 text-sm font-semibold">
                                        <span class="tier-dot h-2 w-2 shrink-0 rounded-full"></span>
                                        {{ $tier->tier_name }}
                                    </span>

                                    <span class="text-xs text-zinc-500">
                                        #{{ $tier->tier_id }}
                                    </span>
                                </div>

                                <p class="tier-text relative mt-6 font-display text-6xl font-black italic leading-none tabular-nums">
                                    {{ $percent($tier->discount_percent) }}<span class="text-3xl">%</span>
                                </p>

                                <p class="relative mt-1.5 text-sm text-zinc-500">
                                    member discount
                                </p>

                                @if ($isBase)
                                    <span class="relative mt-3 inline-block rounded-full border border-zinc-200 px-2 py-0.5 text-xs text-zinc-500 dark:border-zinc-700">
                                        Default for new members
                                    </span>
                                @endif
                            </div>

                            {{-- ข้อมูล Tier --}}
                            <dl class="space-y-3 px-5 pb-5 text-sm">
                                <div class="flex items-baseline justify-between gap-3 border-t border-zinc-100 pt-3 dark:border-zinc-800">
                                    <dt class="text-zinc-500">Points</dt>
                                    <dd class="font-semibold tabular-nums text-zinc-900 dark:text-white">
                                        {{ $range }}
                                    </dd>
                                </div>

                                <div class="flex items-baseline justify-between gap-3">
                                    <dt class="text-zinc-500">Members</dt>
                                    <dd class="font-semibold tabular-nums text-zinc-900 dark:text-white">
                                        {{ number_format($tier->members_count) }}
                                    </dd>
                                </div>

                                <div class="flex items-center justify-between gap-3">
                                    <dt class="text-zinc-500">Color</dt>
                                    <dd class="flex items-center gap-2">
                                        <span
                                            class="tier-dot h-4 w-4 rounded border border-zinc-300 dark:border-zinc-600"
                                            aria-hidden="true"
                                        ></span>
                                        <span class="font-mono text-xs text-zinc-600 dark:text-zinc-300">
                                            {{ $tier->color_hex }}
                                        </span>
                                    </dd>
                                </div>
                            </dl>

                            {{-- Actions --}}
                            <div class="mt-auto flex items-center gap-2 border-t border-zinc-100 p-4 dark:border-zinc-800">
                                <a href="{{ route('admin.tiers.edit', $tier) }}"
                                   class="inline-flex h-10 flex-1 items-center justify-center gap-2 rounded-xl bg-red-600 text-sm font-semibold text-white transition hover:bg-red-500 focus:outline-none focus:ring-2 focus:ring-red-500/40">
                                    <svg class="h-4 w-4"
                                         viewBox="0 0 24 24"
                                         fill="none"
                                         stroke="currentColor"
                                         stroke-width="2"
                                         stroke-linecap="round"
                                         stroke-linejoin="round"
                                         aria-hidden="true">
                                        <path d="M12 20h9"/>
                                        <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4Z"/>
                                    </svg>
                                    Edit
                                </a>

                                @if ($isBase)
                                    <span
                                        title="Default tier can't be deleted"
                                        aria-label="Default tier can't be deleted"
                                        class="grid h-10 w-10 cursor-not-allowed place-items-center rounded-xl border border-dashed border-zinc-300 text-zinc-300 dark:border-zinc-700 dark:text-zinc-600"
                                    >
                                        <svg class="h-4 w-4"
                                             viewBox="0 0 24 24"
                                             fill="none"
                                             stroke="currentColor"
                                             stroke-width="2"
                                             stroke-linecap="round"
                                             stroke-linejoin="round"
                                             aria-hidden="true">
                                            <rect width="18" height="11" x="3" y="11" rx="2"/>
                                            <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                                        </svg>
                                    </span>
                                @else
                                    <form
                                        method="POST"
                                        action="{{ route('admin.tiers.destroy', $tier) }}"
                                        data-confirm="Delete tier {{ $tier->tier_name }}?"

                                    >
                                        @csrf
                                        @method('DELETE')

                                        <button
                                            type="submit"
                                            title="Delete {{ $tier->tier_name }}"
                                            aria-label="Delete {{ $tier->tier_name }}"
                                            class="grid h-10 w-10 place-items-center rounded-xl border border-zinc-300 bg-white text-zinc-500 transition hover:border-red-600 hover:bg-red-600 hover:text-white focus:outline-none focus:ring-2 focus:ring-red-500/40 dark:border-zinc-700 dark:bg-zinc-800"
                                        >
                                            <svg class="h-4 w-4"
                                                 viewBox="0 0 24 24"
                                                 fill="none"
                                                 stroke="currentColor"
                                                 stroke-width="2"
                                                 stroke-linecap="round"
                                                 stroke-linejoin="round"
                                                 aria-hidden="true">
                                                <path d="M3 6h18"/>
                                                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"/>
                                                <path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                                            </svg>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>
            @else
                <div class="rounded-2xl border border-dashed border-zinc-300 bg-white px-6 py-16 text-center dark:border-zinc-700 dark:bg-zinc-900">
                    <p class="font-display text-3xl font-black italic text-zinc-900 dark:text-white">
                        No tiers found
                    </p>

                    <p class="mt-2 text-sm text-zinc-500">
                        Add a tier to start rewarding members.
                    </p>

                    <a href="{{ route('admin.tiers.create') }}"
                       class="mt-6 inline-flex items-center justify-center rounded-xl bg-red-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-red-500">
                        + Add tier
                    </a>
                </div>
            @endif
        </div>
    </div>
</x-layouts::app>