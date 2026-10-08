{{-- ตารางระดับทั้งหมด (MEMBERSHIP_TIER ที่ admin จัดการ) --}}
@php
    $pct = fn ($value) => rtrim(rtrim(number_format((float) $value, 2), '0'), '.');
@endphp

<section class="mt-6">
    <p class="font-display text-lg font-semibold text-ink">All tiers</p>
    <div class="mt-4 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ($tiers as $tier)
            @php
                $isCurrent = $progress && $progress['current']?->tier_id === $tier->tier_id;
                $top = $loop->last && $tiers->count() > 1;
            @endphp
            <div @class([
                'rounded-2xl border-2 bg-white p-5',
                'border-race' => $isCurrent,
                'border-sun bg-sun-soft' => ! $isCurrent && $top,
                'border-line' => ! $isCurrent && ! $top,
            ])>
                <div class="flex items-center justify-between gap-2">
                    <h2 class="font-display text-lg font-bold text-ink">{{ $tier->tier_name }}</h2>
                    @if ($isCurrent)
                        <span class="rounded-full bg-race px-2 py-0.5 text-[11px] font-semibold text-white">Current</span>
                    @elseif ($top)
                        <x-store.icon name="crown" class="size-5 text-ink" />
                    @endif
                </div>
                <p class="mt-3 font-display text-4xl font-extrabold text-race">{{ $pct($tier->discount_percent) }}%<span class="ml-1 text-base font-semibold text-zinc-500">off</span></p>
                <p class="mt-3 border-t border-line pt-3 text-sm text-zinc-500">
                    @if ((int) $tier->min_points === 0)
                        Starting tier for all new members
                    @else
                        From {{ number_format($tier->min_points) }} points
                        <span class="block text-xs">(≈ ฿{{ number_format($tier->min_points * $bahtPerPoint) }} in completed orders)</span>
                    @endif
                </p>
            </div>
        @endforeach
    </div>
</section>
