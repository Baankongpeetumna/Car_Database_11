{{-- รายละเอียดคำสั่งซื้อฝั่งสมาชิก (หน้า admin ใช้ commerce/order-details ของทีม admin แยกกัน) --}}
@php
    use App\Support\Money;

    $paymentLabels = [
        'bank_transfer' => 'Bank transfer',
        'cash' => 'Pay at the store',
    ];

    $steps = [
        'pending' => 'Order placed',
        'processing' => 'Preparing your car',
        'completed' => 'Delivered',
    ];
    $stepIndex = array_search($order->status, array_keys($steps), true);
    $cancelled = $order->status === 'cancelled';
    $expectedPoints = Money::points(Money::cents((string) $order->total_amount));
@endphp

<div class="space-y-6">
    {{-- ตัวติดตามสถานะ --}}
    <section class="rounded-3xl border border-line bg-white p-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-zinc-500">Order</p>
                <p class="font-display text-2xl font-extrabold text-ink">#{{ str_pad($order->order_id, 5, '0', STR_PAD_LEFT) }}</p>
            </div>
            <x-store.order-status :status="$order->status" class="!text-sm" />
        </div>

        @if ($cancelled)
            <p class="mt-5 flex items-center gap-2 rounded-xl bg-race-soft px-4 py-3 text-sm text-race-dark">
                <x-store.icon name="x" class="size-4" /> This order was cancelled and the cars were returned to stock.
            </p>
        @else
            <ol class="mt-6 grid grid-cols-3 gap-2">
                @foreach ($steps as $key => $label)
                    @php $done = $stepIndex !== false && $loop->index <= $stepIndex; @endphp
                    <li class="relative">
                        <div class="h-1.5 rounded-full {{ $done ? 'bg-race' : 'bg-line' }}"></div>
                        <p class="mt-2 flex items-center gap-1.5 text-xs font-semibold {{ $done ? 'text-ink' : 'text-zinc-400' }}">
                            @if ($done)
                                <x-store.icon name="check-circle" class="size-4 text-race" />
                            @endif
                            {{ $label }}
                        </p>
                    </li>
                @endforeach
            </ol>
        @endif

        <dl class="mt-6 grid gap-4 border-t border-line pt-5 text-sm sm:grid-cols-3">
            <div>
                <dt class="text-xs text-zinc-500">Order date</dt>
                <dd class="mt-0.5 font-semibold text-ink">{{ $order->order_date?->format('d/m/Y H:i') }}</dd>
            </div>
            <div>
                <dt class="text-xs text-zinc-500">Payment method</dt>
                <dd class="mt-0.5 font-semibold text-ink">{{ $paymentLabels[$order->payment_method] ?? $order->payment_method }}</dd>
            </div>
            <div>
                <dt class="text-xs text-zinc-500">Shipping address</dt>
                <dd class="mt-0.5 whitespace-pre-line text-ink">{{ $order->shipping_address }}</dd>
            </div>
        </dl>
    </section>

    {{-- รายการรถ (ORDER_ITEM เก็บราคา ณ ตอนซื้อ) --}}
    <section class="rounded-3xl border border-line bg-white p-6">
        <p class="font-display text-lg font-semibold text-ink">Cars in this order</p>
        <ul class="mt-3 divide-y divide-line">
            @foreach ($order->cars as $car)
                @php $unitCents = Money::cents((string) $car->pivot->unit_price); @endphp
                <li class="flex items-center gap-4 py-4">
                    <x-store.car-photo :car="$car" size="thumb" class="aspect-[4/3] w-24 shrink-0 rounded-xl" />
                    <div class="min-w-0 flex-1">
                        <p class="text-[11px] font-semibold uppercase tracking-wider text-zinc-500">{{ $car->brand?->brand_name }}</p>
                        <p class="font-display font-semibold uppercase text-ink">
                            @if (Route::has('products.show'))
                                <a href="{{ route('products.show', $car) }}" class="hover:text-race">{{ $car->model_name }}</a>
                            @else
                                {{ $car->model_name }}
                            @endif
                        </p>
                        <p class="text-xs text-zinc-500">{{ $car->model_year }} · ฿{{ Money::display($unitCents) }} × {{ $car->pivot->quantity }}</p>
                    </div>
                    <p class="font-display text-lg font-bold text-ink">฿{{ Money::display($unitCents * (int) $car->pivot->quantity) }}</p>
                </li>
            @endforeach
        </ul>

        <dl class="mt-2 space-y-2.5 border-t border-line pt-4 text-sm">
            <div class="flex justify-between"><dt class="text-zinc-500">Subtotal</dt><dd class="font-semibold text-ink">฿{{ Money::display(Money::cents((string) $order->subtotal)) }}</dd></div>
            <div class="flex justify-between"><dt class="text-zinc-500">Member discount</dt><dd class="font-semibold text-emerald-600">−฿{{ Money::display(Money::cents((string) $order->discount_amount)) }}</dd></div>
            <div class="flex items-end justify-between border-t border-line pt-3">
                <dt class="font-semibold text-ink">Total</dt>
                <dd class="font-display text-2xl font-extrabold text-race">฿{{ Money::display(Money::cents((string) $order->total_amount)) }}</dd>
            </div>
        </dl>

        <p class="mt-4 flex items-center gap-2 rounded-xl bg-sun-soft px-3 py-2.5 text-xs text-ink">
            <x-store.icon name="coins" class="size-4" />
            @if ($order->status === 'completed')
                Points earned: <strong>{{ number_format($order->points_earned) }} points</strong>
            @elseif ($cancelled)
                Cancelled orders do not earn points.
            @else
                When this order is completed, you will earn <strong>{{ number_format($expectedPoints) }} points</strong>.
            @endif
        </p>
    </section>
</div>
