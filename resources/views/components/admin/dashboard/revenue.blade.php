@php
    // Only orders with these statuses count as revenue
    $revenueStatuses = ['processing', 'completed'];
    $base = \App\Models\Order::whereIn('status', $revenueStatuses);
    $now  = now();

    $periods = [
        'Today'      => [$now->copy()->startOfDay(),   $now->copy()->endOfDay()],
        'This month' => [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()],
        'This year'  => [$now->copy()->startOfYear(),  $now->copy()->endOfYear()],
    ];

    $rows = [];
    foreach ($periods as $label => $range) {
        $q = (clone $base)->whereBetween('order_date', $range);
        $rows[$label] = [
            'label'  => $label,
            'amount' => (float) (clone $q)->sum('total_amount'),
            'orders' => (clone $q)->count(),
        ];
    }

    $today = $rows['Today'];
    $month = $rows['This month'];
    $year  = $rows['This year'];

    $pctOfYear = fn ($amount) => $year['amount'] > 0
        ? (int) min(100, round($amount / $year['amount'] * 100))
        : 0;

    $avgOrder = $year['orders'] > 0 ? $year['amount'] / $year['orders'] : 0;
@endphp

<div {{ $attributes->merge(['class' => 'flex flex-col rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900']) }}>

    <div class="flex items-start justify-between px-5 py-5">
        <div>
            <x-admin.dashboard.section-title title="Revenue" />
            <p class="mt-2 text-sm text-zinc-500">
                Preparing and ready-for-handover orders
            </p>
        </div>

        <div class="rounded-lg bg-emerald-50 p-2.5 text-emerald-600 dark:bg-emerald-950/40">
            <x-admin.dashboard.icon name="trend" :size="22" />
        </div>
    </div>

    <div class="flex flex-1 flex-col gap-3 px-5 pb-5">

        {{-- Hero: this year --}}
        <div class="relative overflow-hidden rounded-xl bg-gradient-to-br from-red-600 to-red-800 p-5 text-white shadow-sm">
            <div class="pointer-events-none absolute -right-8 -top-8 h-28 w-28 rounded-full bg-white/10"></div>
            <div class="pointer-events-none absolute -bottom-10 right-6 h-24 w-24 rounded-full bg-white/10"></div>

            <p class="relative text-xs font-medium uppercase tracking-wider text-red-100">This year</p>
            <p class="relative mt-1 font-display text-3xl font-bold leading-tight">
                ฿{{ number_format($year['amount'], 0) }}
            </p>
            <div class="relative mt-2 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-red-100">
                <span>{{ $year['orders'] }} {{ \Illuminate\Support\Str::plural('order', $year['orders']) }}</span>
                @if ($avgOrder > 0)
                    <span class="opacity-60">•</span>
                    <span>avg ฿{{ number_format($avgOrder, 0) }} / order</span>
                @endif
            </div>
        </div>

        {{-- Today + This month, with share of the year --}}
        <div class="grid grid-cols-2 gap-3">
            @foreach ([['row' => $today, 'color' => 'bg-sky-500'], ['row' => $month, 'color' => 'bg-amber-500']] as $tile)
                @php $r = $tile['row']; $pct = $pctOfYear($r['amount']); @endphp

                <div class="rounded-xl border border-zinc-200 p-3.5 dark:border-zinc-700">
                    <div class="flex items-center gap-1.5">
                        <span class="h-2 w-2 rounded-full {{ $tile['color'] }}"></span>
                        <p class="text-xs font-medium text-zinc-500">{{ $r['label'] }}</p>
                    </div>

                    <p class="mt-1.5 font-display text-lg font-bold leading-tight text-zinc-900 dark:text-white">
                        ฿{{ number_format($r['amount'], 0) }}
                    </p>
                    <p class="mt-0.5 text-[11px] text-zinc-500">
                        {{ $r['orders'] }} {{ \Illuminate\Support\Str::plural('order', $r['orders']) }}
                    </p>

                    <div class="mt-2.5 h-1.5 overflow-hidden rounded-full bg-zinc-100 dark:bg-zinc-800">
                        <div class="h-full rounded-full {{ $tile['color'] }}" style="width: {{ $pct }}%"></div>
                    </div>
                    <p class="mt-1 text-[10px] text-zinc-400">{{ $pct }}% of this year</p>
                </div>
            @endforeach
        </div>

        <p class="mt-auto pt-1 text-xs text-zinc-500">
            Pending and cancelled orders are not counted.
        </p>
    </div>
</div>
