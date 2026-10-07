@extends('layouts.public')

@section('content')
    @include('commerce.messages')

    @php
        $labels = [
            'pending' => 'Pending',
            'processing' => 'Processing',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
        ];
    @endphp

    <h1 class="mb-6 text-2xl font-semibold">
        My Orders
    </h1>

    @forelse ($orders as $order)
        <a href="{{ route('orders.show', $order) }}"
           class="mb-4 block rounded-xl border border-zinc-200 p-4 dark:border-zinc-700">
            <strong>
                #{{ $order->order_id }}
                · {{ $labels[$order->status] ?? $order->status }}
            </strong>

            @if (in_array($order->order_id, $reviewableOrderIds, true))
                <span class="ml-2 rounded-full bg-amber-100 px-2 py-0.5 text-xs font-semibold text-amber-800 dark:bg-amber-900/40 dark:text-amber-300">
                    ★ Review available
                </span>
            @endif

            <p class="mt-2">
                {{ $order->order_date?->format('d/m/Y H:i') }}
                · ฿{{ \App\Support\Money::display(
                    \App\Support\Money::cents($order->total_amount)
                ) }}
            </p>
        </a>
    @empty
        <p class="text-zinc-500">You have no orders yet.</p>
    @endforelse

    <div class="mt-6">
        {{ $orders->links() }}
    </div>
@endsection