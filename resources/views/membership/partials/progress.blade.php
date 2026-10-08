{{-- บัตรสมาชิกและความคืบหน้าไประดับถัดไป ($progress จาก MembershipController) --}}
@php
    $pct = fn ($value) => rtrim(rtrim(number_format((float) $value, 2), '0'), '.');
    $current = $progress['current'];
    $next = $progress['next'];
@endphp

<section class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-race to-race-dark p-7 text-white shadow-[0_20px_40px_-20px_rgb(229_50_45/0.7)]">
    <div class="speed-lines absolute inset-0 opacity-40"></div>
    <div class="relative flex flex-wrap items-start justify-between gap-6">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-white/80">Your tier</p>
            <p class="mt-2 flex items-center gap-2 font-display text-5xl font-extrabold uppercase italic">
                <x-store.icon name="crown" class="size-9 text-sun" /> {{ $current?->tier_name ?? '-' }}
            </p>
            @if ($current)
                <p class="mt-2 text-sm text-white/85">{{ $pct($current->discount_percent) }}% discount on every order</p>
            @endif
        </div>
        <div class="text-right">
            <p class="text-xs text-white/75">Your points</p>
            <p class="font-display text-5xl font-extrabold">{{ number_format($progress['points']) }}</p>
        </div>
    </div>

    <div class="relative mt-7">
        @if ($next)
            <div class="h-3 overflow-hidden rounded-full bg-white/25" role="progressbar"
                 aria-valuenow="{{ $progress['percent'] }}" aria-valuemin="0" aria-valuemax="100"
                 aria-label="Progress to {{ $next->tier_name }}">
                <div class="h-full rounded-full bg-sun" style="width: {{ $progress['percent'] }}%"></div>
            </div>
            <p class="mt-2 text-sm text-white/90">
                <strong>{{ number_format($progress['pointsNeeded']) }}</strong>
                more {{ Str::plural('point', $progress['pointsNeeded']) }}
                (about ฿{{ number_format($progress['bahtNeeded']) }} in completed orders)
                to reach <strong>{{ $next->tier_name }}</strong>
                and get {{ $pct($next->discount_percent) }}% off.
            </p>
        @else
            <p class="flex items-center gap-2 rounded-xl bg-white/15 px-4 py-3 text-sm font-semibold">
                <x-store.icon name="crown" class="size-4 text-sun" /> You are at the highest tier.
            </p>
        @endif
        <p class="mt-3 text-xs text-white/70">Points are added when an admin marks your order as completed.</p>
    </div>
</section>
