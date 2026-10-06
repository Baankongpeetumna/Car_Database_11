@php
    $labels = [
        'pending' => 'รอดำเนินการ',
        'processing' => 'กำลังดำเนินการ',
        'completed' => 'สำเร็จ',
        'cancelled' => 'ยกเลิก',
    ];

    $paymentLabels = [
        'bank_transfer' => 'โอนเงิน',
        'cash' => 'ชำระเงินที่ร้าน',
    ];
@endphp

<div class="mb-6 rounded-xl border border-zinc-200 p-5 dark:border-zinc-700">
    <h1 class="text-2xl font-semibold">
        คำสั่งซื้อ #{{ $order->order_id }}
    </h1>

    <p class="mt-2">
        วันที่ {{ $order->order_date?->format('d/m/Y H:i') }}
    </p>

    <p class="mt-2">
        สถานะ:
        <strong>{{ $labels[$order->status] ?? $order->status }}</strong>
    </p>

    <p class="mt-2">
        วิธีชำระเงิน:
        {{ $paymentLabels[$order->payment_method] ?? $order->payment_method }}
    </p>

    <p class="mt-2">ที่อยู่จัดส่ง:</p>

    <p class="whitespace-pre-wrap text-zinc-500">{{ $order->shipping_address }}</p>
</div>

<div class="overflow-x-auto rounded-xl border border-zinc-200 dark:border-zinc-700">
    <table class="w-full text-left text-sm">
        <thead>
            <tr class="border-b border-zinc-200 dark:border-zinc-700">
                <th class="p-3">รถ</th>
                <th class="p-3">ราคาตอนซื้อ</th>
                <th class="p-3">จำนวน</th>
                <th class="p-3">รวม</th>
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
        ยอดก่อนส่วนลด:
        ฿{{ \App\Support\Money::display(
            \App\Support\Money::cents($order->subtotal)
        ) }}
    </p>

    <p>
        ส่วนลด:
        ฿{{ \App\Support\Money::display(
            \App\Support\Money::cents($order->discount_amount)
        ) }}
    </p>

    <p class="text-xl font-semibold">
        ยอดสุทธิ:
        ฿{{ \App\Support\Money::display(
            \App\Support\Money::cents($order->total_amount)
        ) }}
    </p>

    <p>
        คะแนนที่ได้รับแล้ว:
        {{ number_format($order->points_earned) }} คะแนน
    </p>

    @if (in_array($order->status, ['pending', 'processing'], true))
        <p class="text-sm text-zinc-500">
            เมื่อคำสั่งซื้อสำเร็จ จะได้รับ
            {{ number_format(
                \App\Support\Money::points(
                    \App\Support\Money::cents($order->total_amount)
                )
            ) }}
            คะแนน
        </p>
    @endif
</div>