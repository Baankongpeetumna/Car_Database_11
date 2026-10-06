@extends('layouts.public')

@section('content')
    @include('commerce.messages')

    @php
        $labels = [
            'pending' => 'รอดำเนินการ',
            'processing' => 'กำลังดำเนินการ',
            'completed' => 'สำเร็จ',
            'cancelled' => 'ยกเลิก',
        ];
    @endphp

    <h1 class="mb-6 text-2xl font-semibold">
        ประวัติคำสั่งซื้อของฉัน
    </h1>

    @forelse ($orders as $order)
        <a href="{{ route('orders.show', $order) }}"
           class="mb-4 block rounded-xl border border-zinc-200 p-4 dark:border-zinc-700">
            <strong>
                #{{ $order->order_id }}
                · {{ $labels[$order->status] ?? $order->status }}
            </strong>

            <p class="mt-2">
                {{ $order->order_date?->format('d/m/Y H:i') }}
                · ฿{{ \App\Support\Money::display(
                    \App\Support\Money::cents($order->total_amount)
                ) }}
            </p>
        </a>
    @empty
        <p class="text-zinc-500">ยังไม่มีคำสั่งซื้อ</p>
    @endforelse

    <div class="mt-6">
        {{ $orders->links() }}
    </div>
@endsection