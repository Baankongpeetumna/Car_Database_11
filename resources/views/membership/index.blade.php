@extends('layouts.public')

@section('content')
    <h1 class="mb-2 text-2xl font-semibold">Membership</h1>

    <p class="mb-6 text-zinc-500">
        Earn 1 point for every ฿{{ number_format($bahtPerPoint) }} of completed orders.
        Reach a higher tier to get a bigger discount on every order.
    </p>

    {{-- ความคืบหน้าของสมาชิกที่ login อยู่ --}}
    @if ($progress)
        <section class="mb-8 rounded-xl border border-zinc-200 p-5 dark:border-zinc-700">
            <div class="flex flex-wrap items-baseline justify-between gap-2">
                <p>
                    Your tier:
                    <strong class="text-lg">{{ $progress['current']?->tier_name ?? '-' }}</strong>
                    @if ($progress['current'])
                        <span class="text-zinc-500">({{ $progress['current']->discount_percent }}% discount)</span>
                    @endif
                </p>

                <p>
                    Your points:
                    <strong>{{ number_format($progress['points']) }}</strong>
                </p>
            </div>

            @if ($progress['next'])
                <div class="mt-4 h-3 w-full overflow-hidden rounded-full bg-zinc-200 dark:bg-zinc-700"
                     role="progressbar"
                     aria-valuenow="{{ $progress['percent'] }}" aria-valuemin="0" aria-valuemax="100"
                     aria-label="Progress to {{ $progress['next']->tier_name }}">
                    <div class="h-full rounded-full bg-amber-500" style="width: {{ $progress['percent'] }}%"></div>
                </div>

                <p class="mt-2 text-sm">
                    <strong>{{ number_format($progress['pointsNeeded']) }}</strong>
                    more {{ Str::plural('point', $progress['pointsNeeded']) }}
                    (about ฿{{ number_format($progress['bahtNeeded']) }} in completed orders)
                    to reach <strong>{{ $progress['next']->tier_name }}</strong>
                    and get {{ $progress['next']->discount_percent }}% off.
                </p>
            @else
                <p class="mt-3 text-sm text-green-600">
                    🎉 You are at the highest tier.
                </p>
            @endif

            <p class="mt-2 text-xs text-zinc-500">
                Points are added when an admin marks your order as completed.
            </p>
        </section>
    @elseif (! auth()->check())
        <p class="mb-8 text-sm">
            <a href="{{ route('register') }}" class="text-blue-600">Create an account</a>
            or
            <a href="{{ route('login') }}" class="text-blue-600">log in</a>
            to start earning points.
        </p>
    @endif

    {{-- ตารางระดับทั้งหมด (ดึงจาก MEMBERSHIP_TIER ที่ admin จัดการ) --}}
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        @foreach ($tiers as $tier)
            @php
                $isCurrent = $progress && $progress['current']?->tier_id === $tier->tier_id;
            @endphp

            <div @class([
                'rounded-xl border p-5',
                'border-amber-500 ring-2 ring-amber-500' => $isCurrent,
                'border-zinc-200 dark:border-zinc-700' => ! $isCurrent,
            ])>
                <div class="flex items-center justify-between gap-2">
                    <h2 class="text-lg font-semibold">{{ $tier->tier_name }}</h2>

                    @if ($isCurrent)
                        <span class="rounded-full bg-amber-100 px-2 py-0.5 text-xs font-semibold text-amber-800 dark:bg-amber-900/40 dark:text-amber-300">
                            Your tier
                        </span>
                    @endif
                </div>

                <p class="mt-3 text-3xl font-semibold">
                    {{ rtrim(rtrim($tier->discount_percent, '0'), '.') }}%
                    <span class="text-base font-normal text-zinc-500">off</span>
                </p>

                <p class="mt-2 text-sm text-zinc-500">
                    @if ($tier->min_points === 0)
                        Starting tier for all new members
                    @else
                        From {{ number_format($tier->min_points) }} points
                        (≈ ฿{{ number_format($tier->min_points * $bahtPerPoint) }})
                    @endif
                </p>
            </div>
        @endforeach
    </div>
@endsection
