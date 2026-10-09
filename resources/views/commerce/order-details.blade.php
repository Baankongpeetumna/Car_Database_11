@php
    use App\Support\Money;

    $paymentLabels = [
        'bank_transfer' => 'Bank transfer',
        'cash'          => 'Pay at the store',
    ];

    $subtotalCents = Money::cents($order->subtotal);
    $discountCents = Money::cents($order->discount_amount);
    $totalCents    = Money::cents($order->total_amount);
    $pendingPoints = Money::points($totalCents);

    $isOpenOrder = in_array($order->status, ['pending', 'processing'], true);
    $itemCount   = $order->cars->sum(fn ($c) => (int) $c->pivot->quantity);
@endphp

<div class="space-y-5">

    {{-- Order info --}}
    <div class="overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
        <div class="grid divide-y divide-zinc-200 dark:divide-zinc-800 sm:grid-cols-3 sm:divide-x sm:divide-y-0">
            <div class="flex items-center gap-3 p-5">
                <span class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-red-600 text-white">
                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M7 3h6l3 3v11H4V3h3zM7 9h6M7 13h4"/></svg>
                </span>
                <div class="min-w-0">
                    <p class="text-xs text-zinc-500">Order number</p>
                    <p class="font-display text-2xl font-black italic leading-tight text-zinc-900 dark:text-white">#{{ $order->order_id }}</p>
                </div>
            </div>

            <div class="flex items-center gap-3 p-5">
                <span class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-sky-100 text-sky-600 dark:bg-sky-950/60 dark:text-sky-300">
                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="4" width="14" height="13" rx="2"/><path d="M3 8h14M7 2v3M13 2v3"/></svg>
                </span>
                <div class="min-w-0">
                    <p class="text-xs text-zinc-500">Order date</p>
                    <p class="font-semibold text-zinc-900 dark:text-white">{{ $order->order_date?->format('d M Y') }}</p>
                    <p class="text-xs text-zinc-500">{{ $order->order_date?->format('H:i') }}</p>
                </div>
            </div>

            <div class="flex items-center gap-3 p-5">
                <span class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-emerald-100 text-emerald-600 dark:bg-emerald-950/60 dark:text-emerald-300">
                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="2.5" y="5" width="15" height="10" rx="2"/><path d="M2.5 9h15M6 13h3"/></svg>
                </span>
                <div class="min-w-0">
                    <p class="text-xs text-zinc-500">Payment method</p>
                    <p class="font-semibold text-zinc-900 dark:text-white">{{ $paymentLabels[$order->payment_method] ?? $order->payment_method }}</p>
                </div>
            </div>
        </div>

        <div class="flex items-start gap-3 border-t border-zinc-200 bg-zinc-50 p-5 dark:border-zinc-800 dark:bg-zinc-950/40">
            <svg class="mt-0.5 h-5 w-5 shrink-0 text-red-600" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M10 18s6-5.2 6-10a6 6 0 10-12 0c0 4.800 6 10 6 10z"/><circle cx="10" cy="8" r="2"/></svg>
            <div class="min-w-0">
                <p class="text-xs text-zinc-500">Shipping address</p>
                <p class="mt-0.5 whitespace-pre-wrap break-words font-medium text-zinc-900 dark:text-white">{{ $order->shipping_address ?: '-' }}</p>
            </div>
        </div>
    </div>

    {{-- Cars --}}
    <div class="rounded-2xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
        <div class="flex items-center justify-between px-5 pt-5">
            <h2 class="font-display text-xl font-black italic text-zinc-900 dark:text-white">Cars in this order</h2>
            <span class="rounded-full bg-zinc-100 px-3 py-1 text-xs font-semibold text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">
                {{ $itemCount }} {{ \Illuminate\Support\Str::plural('car', $itemCount) }}
            </span>
        </div>

        <ul class="mt-4 divide-y divide-zinc-200 dark:divide-zinc-800">
            @foreach ($order->cars as $car)
                @php
                    $qty       = (int) $car->pivot->quantity;
                    $unitCents = Money::cents((string) $car->pivot->unit_price);
                    $brand     = $car->brand?->brand_name;
                @endphp
                <li class="flex flex-wrap items-center gap-4 px-5 py-4">
                    <div class="grid h-14 w-14 shrink-0 place-items-center rounded-xl bg-zinc-900 font-display text-xl font-black italic text-white dark:bg-white dark:text-zinc-900">
                        {{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($brand ?? $car->model_name, 0, 1)) }}
                    </div>

                    <div class="min-w-0 flex-1">
                        <p class="truncate text-base font-bold text-zinc-900 dark:text-white">
                            {{ $brand }} {{ $car->model_name }}
                        </p>
                        <div class="mt-1 flex flex-wrap items-center gap-2 text-sm text-zinc-500">
                            <span class="rounded-md bg-zinc-100 px-2 py-0.5 text-xs font-semibold text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">{{ $car->model_year }}</span>
                            <span>฿{{ Money::display($unitCents) }} × {{ $qty }}</span>
                        </div>
                    </div>

                    <p class="ml-auto text-lg font-bold tabular-nums text-zinc-900 dark:text-white">
                        ฿{{ Money::display($unitCents * $qty) }}
                    </p>
                </li>
            @endforeach
        </ul>
    </div>

    {{-- Summary --}}
    <div class="overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
        <div class="space-y-3 p-5">
            <div class="flex items-center justify-between text-sm">
                <span class="text-zinc-500">Subtotal</span>
                <span class="font-semibold tabular-nums text-zinc-900 dark:text-white">฿{{ Money::display($subtotalCents) }}</span>
            </div>

            <div class="flex items-center justify-between text-sm">
                <span class="text-zinc-500">Member discount</span>
                @if ($discountCents > 0)
                    <span class="font-semibold tabular-nums text-emerald-600 dark:text-emerald-400">−฿{{ Money::display($discountCents) }}</span>
                @else
                    <span class="tabular-nums text-zinc-400">฿0.00</span>
                @endif
            </div>

            <div class="flex items-end justify-between border-t border-dashed border-zinc-300 pt-4 dark:border-zinc-700">
                <span class="text-sm font-semibold text-zinc-900 dark:text-white">Total</span>
                <span class="font-display text-4xl font-black italic leading-none tabular-nums text-red-600">
                    ฿{{ Money::display($totalCents) }}
                </span>
            </div>
        </div>

        @if ($order->status === 'completed')
            <div class="flex items-center gap-3 bg-emerald-50 px-5 py-4 dark:bg-emerald-950/30">
                <span class="grid h-9 w-9 shrink-0 place-items-center rounded-full bg-emerald-600 text-white">
                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 10.5l4 4 8-9"/></svg>
                </span>
                <p class="text-sm text-emerald-800 dark:text-emerald-300">
                    Member earned <strong class="text-base">{{ number_format($order->points_earned) }} points</strong> from this order.
                </p>
            </div>
        @elseif ($isOpenOrder)
            <div class="flex items-center gap-3 bg-amber-50 px-5 py-4 dark:bg-amber-950/30">
                <span class="grid h-9 w-9 shrink-0 place-items-center rounded-full bg-amber-500 text-white">
                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M10 1.5l2.6 5.4 5.9.8-4.3 4.1 1 5.9L10 14.9l-5.2 2.8 1-5.9L1.5 7.700l5.9-.8L10 1.500z"/></svg>
                </span>
                <p class="text-sm text-amber-900 dark:text-amber-200">
                    Completing this order gives the member <strong class="text-base">{{ number_format($pendingPoints) }} points</strong>.
                </p>
            </div>
        @endif
    </div>
</div>