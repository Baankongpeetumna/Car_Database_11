@props(['orders'])

<div class="overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">

    <div class="flex items-start justify-between px-5 py-5">
        <div>
            <x-admin.dashboard.section-title title="Recent orders" />
        </div>

        <a href="{{ route('admin.orders.index') }}"
           class="pt-1 text-sm font-semibold text-red-600 hover:text-red-700">
            View all orders →
        </a>
    </div>

    <div class="space-y-2.5 px-4 pb-4">
        @forelse ($orders as $order)

            @php
                $statusLabel = match ($order->status) {
                    'pending' => 'Pending',
                    'processing' => 'Preparing car',
                    'completed' => 'Ready for handover',
                    'cancelled' => 'Cancelled',
                    default => ucfirst($order->status),
                };

                // Same status colors as the Orders page
                $st = match ($order->status) {
                    'pending'    => ['bar' => 'bg-amber-500',   'avatar' => 'bg-amber-500',   'dot' => 'bg-amber-500',   'badge' => 'bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300'],
                    'processing' => ['bar' => 'bg-sky-500',     'avatar' => 'bg-sky-500',     'dot' => 'bg-sky-500',     'badge' => 'bg-sky-100 text-sky-700 dark:bg-sky-950/60 dark:text-sky-300'],
                    'completed'  => ['bar' => 'bg-emerald-500', 'avatar' => 'bg-emerald-500', 'dot' => 'bg-emerald-500', 'badge' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300'],
                    default      => ['bar' => 'bg-zinc-400',    'avatar' => 'bg-zinc-600',    'dot' => 'bg-zinc-400',    'badge' => 'bg-zinc-200 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400'],
                };

                $mName   = $order->member?->name ?? 'Deleted member';
                $initial = \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($mName, 0, 1));
            @endphp

            <a href="{{ route('admin.orders.show', $order) }}"
               class="group relative flex items-center gap-4 overflow-hidden rounded-xl border border-zinc-200 bg-zinc-50/60 py-3.5 pl-6 pr-4
                      transition hover:border-zinc-400 hover:bg-white
                      dark:border-zinc-800 dark:bg-zinc-950/40 dark:hover:border-zinc-600 dark:hover:bg-zinc-900">

                <span class="absolute inset-y-0 left-0 w-1.5 {{ $st['bar'] }}"></span>

                <span class="w-[4.5rem] shrink-0 rounded-lg bg-zinc-200/70 py-1.5 text-center text-xs font-bold text-zinc-800 dark:bg-zinc-800 dark:text-zinc-100">
                    VLC-{{ $order->order_id }}
                </span>

                <div class="flex min-w-0 flex-1 items-center gap-3">
                    <div class="grid h-10 w-10 shrink-0 place-items-center rounded-xl text-sm font-bold text-white {{ $st['avatar'] }}">
                        {{ $initial }}
                    </div>
                    <div class="min-w-0">
                        <p class="truncate text-sm font-semibold text-zinc-900 dark:text-white">{{ $mName }}</p>
                        <p class="truncate text-xs text-zinc-500">{{ $order->member?->email }}</p>
                    </div>
                </div>

                <div class="hidden w-28 shrink-0 md:block">
                    <p class="text-[10px] font-semibold uppercase tracking-wider text-zinc-400">Date</p>
                    <p class="text-sm font-semibold text-zinc-900 dark:text-white">{{ $order->order_date?->format('j M Y') ?? '-' }}</p>
                </div>

                <div class="hidden w-32 shrink-0 text-right sm:block">
                    <p class="text-[10px] font-semibold uppercase tracking-wider text-zinc-400">Total</p>
                    <p class="text-sm font-bold tabular-nums {{ $order->status === 'cancelled' ? 'text-zinc-400 line-through' : 'text-zinc-900 dark:text-white' }}">
                        ฿{{ number_format($order->total_amount, 0) }}
                    </p>
                </div>

                <span class="inline-flex w-44 shrink-0 items-center justify-center gap-1.5 rounded-full px-3 py-1.5 text-xs font-semibold {{ $st['badge'] }}">
                    <span class="h-1.5 w-1.5 rounded-full {{ $st['dot'] }}"></span>{{ $statusLabel }}
                </span>

                <span class="hidden w-4 shrink-0 text-center text-zinc-300 transition group-hover:translate-x-0.5 group-hover:text-red-600 sm:block" aria-hidden="true">→</span>
            </a>

        @empty

            <div class="px-5 py-10 text-center">
                <p class="text-base font-semibold text-zinc-900 dark:text-white">No orders yet</p>
                <p class="mt-1 text-sm text-zinc-500">Orders will show up here once members check out.</p>
            </div>

        @endforelse
    </div>
</div>