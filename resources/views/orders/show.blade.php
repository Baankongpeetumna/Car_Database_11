@extends('layouts.public')

@section('content')
    @include('commerce.messages')

    <a href="{{ route('orders.index') }}"
       class="mb-4 inline-block text-blue-600">
        ← My Orders
    </a>

    @include('commerce.order-details')

    {{-- รีวิวรถในออเดอร์นี้ (เฉพาะหน้าลูกค้า ไม่ได้ใส่ใน order-details เพราะหน้า admin ใช้ร่วม) --}}
    @if ($order->status === \App\Models\Review::ELIGIBLE_ORDER_STATUS)
        <section class="rounded-xl border border-zinc-200 p-5 dark:border-zinc-700">
            <h2 class="mb-3 text-lg font-semibold">Review your cars</h2>

            <ul class="space-y-3">
                @foreach ($order->cars as $car)
                    <li class="flex flex-wrap items-center justify-between gap-3">
                        <span>
                            {{ $car->brand?->brand_name }}
                            {{ $car->model_name }}
                            ({{ $car->model_year }})
                        </span>

                        @if (($reviewsLeft[$car->car_id] ?? 0) > 0)
                            <a href="{{ route('products.show', $car) }}#write-review"
                               class="rounded-lg bg-blue-600 px-4 py-2 text-sm text-white">
                                ★ Write a review
                            </a>
                        @else
                            <span class="text-sm text-green-600">
                                ✓ Reviewed ·
                                <a href="{{ route('products.show', $car) }}#reviews" class="text-blue-600">
                                    View
                                </a>
                            </span>
                        @endif
                    </li>
                @endforeach
            </ul>
        </section>
    @elseif (in_array($order->status, ['pending', 'processing'], true))
        <p class="rounded-xl border border-zinc-200 p-4 text-sm text-zinc-500 dark:border-zinc-700">
            You can review these cars once the order is completed.
        </p>
    @endif
@endsection
