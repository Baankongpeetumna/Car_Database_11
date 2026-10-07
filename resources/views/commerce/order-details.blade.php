@php
    $labels = [
        'pending' => 'Pending',
        'processing' => 'Processing',
        'completed' => 'Completed',
        'cancelled' => 'Cancelled',
    ];

    $paymentLabels = [
        'bank_transfer' => 'Bank transfer',
        'cash' => 'Pay at the store',
    ];
@endphp

<div class="mb-6 rounded-xl border border-zinc-200 p-5 dark:border-zinc-700">
    <h1 class="text-2xl font-semibold">
        Order #{{ $order->order_id }}
    </h1>

    <p class="mt-2">
        Date: {{ $order->order_date?->format('d/m/Y H:i') }}
    </p>

    <p class="mt-2">
        Status:
        <strong>{{ $labels[$order->status] ?? $order->status }}</strong>
    </p>

    <p class="mt-2">
        Payment method:
        {{ $paymentLabels[$order->payment_method] ?? $order->payment_method }}
    </p>

    <p class="mt-2">Shipping address:</p>

    <p class="whitespace-pre-wrap text-zinc-500">{{ $order->shipping_address }}</p>
</div>

<div class="overflow-x-auto rounded-xl border border-zinc-200 dark:border-zinc-700">
    <table class="w-full text-left text-sm">
        <thead>
            <tr class="border-b border-zinc-200 dark:border-zinc-700">
                <th class="p-3">Car</th>
                <th class="p-3">Unit Price</th>
                <th class="p-3">Quantity</th>
                <th class="p-3">Total</th>
            </tr>
        </thead>

        <tbody>
            @foreach ($order->cars as $car)
                @php
                    $unitCents = \App\Support\Money::cents(
                        (string) $car->pivot->unit_price
                    );
                @endphp

                <tr class="border-b border-zinc-200 dark:border-zinc-700">
                    <td class="p-3">
                        {{ $car->brand?->brand_name }}
                        {{ $car->model_name }}
                        ({{ $car->model_year }})
                    </td>

                    <td class="p-3">
                        ฿{{ \App\Support\Money::display($unitCents) }}
                    </td>

                    <td class="p-3">
                        {{ $car->pivot->quantity }}
                    </td>

                    <td class="p-3">
                        ฿{{ \App\Support\Money::display(
                            $unitCents * (int) $car->pivot->quantity
                        ) }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div class="my-6 space-y-2 rounded-xl border border-zinc-200 p-5 dark:border-zinc-700">
    <p>
        Subtotal:
        ฿{{ \App\Support\Money::display(
            \App\Support\Money::cents($order->subtotal)
        ) }}
    </p>

    <p>
        Discount:
        ฿{{ \App\Support\Money::display(
            \App\Support\Money::cents($order->discount_amount)
        ) }}
    </p>

    <p class="text-xl font-semibold">
        Total:
        ฿{{ \App\Support\Money::display(
            \App\Support\Money::cents($order->total_amount)
        ) }}
    </p>

    <p>
        Points earned:
        {{ number_format($order->points_earned) }} points
    </p>

    @if (in_array($order->status, ['pending', 'processing'], true))
        <p class="text-sm text-zinc-500">
            When this order is completed, you will earn
            {{ number_format(
                \App\Support\Money::points(
                    \App\Support\Money::cents($order->total_amount)
                )
            ) }}
            points.
        </p>
    @endif
</div>