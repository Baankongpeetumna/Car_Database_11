@extends('layouts.public')

@section('content')
    @if (session('order_placed'))
        {{-- หน้าสั่งซื้อสำเร็จ (CheckoutController ส่ง order_placed มาหลังกดยืนยัน) --}}
        <section class="relative overflow-hidden rounded-3xl bg-ink px-6 py-10 text-center text-white">
            <div class="speed-lines absolute inset-0 opacity-60"></div>
            <div class="relative">
                <div class="mx-auto flex justify-center">@include('partials.store.checkout-steps', ['current' => 3, 'dark' => true])</div>
                <span class="mx-auto mt-6 flex size-20 items-center justify-center rounded-full bg-race ring-8 ring-race/25">
                    <x-store.icon name="check" class="size-10 text-white" stroke="3" />
                </span>
                <h1 class="mt-5 font-display text-4xl font-extrabold uppercase italic sm:text-5xl">Order placed!</h1>
                <p class="mt-2 text-sm text-zinc-300">{{ session('success') ?? 'Thank you for choosing VELOCE.' }}</p>
                <div class="mt-6 flex flex-wrap justify-center gap-3">
                    <a href="{{ route('orders.index') }}" class="rounded-xl bg-race px-5 py-2.5 text-sm font-semibold text-white hover:bg-race-dark">View My Orders</a>
                    <a href="{{ route('products.index') }}" class="rounded-xl border border-white/30 px-5 py-2.5 text-sm font-semibold text-white hover:bg-white/10">Continue Shopping</a>
                </div>
            </div>
        </section>
        <div class="mt-6"></div>
    @else
        <a href="{{ route('orders.index') }}" class="inline-flex items-center gap-1 text-sm font-semibold text-zinc-500 hover:text-ink">
            <x-store.icon name="arrow-left" class="size-4" /> My Orders
        </a>
        <h1 class="mb-6 mt-2 font-display text-4xl font-extrabold uppercase italic leading-none text-ink">Order details</h1>
        <x-store.flash />
    @endif

    <div class="mx-auto max-w-4xl space-y-6">
        @include('orders.partials.details')

        {{-- รีวิวรถในออเดอร์นี้ ($reviewsLeft จาก OrderController@show) --}}
        @if ($order->status === \App\Models\Review::ELIGIBLE_ORDER_STATUS)
            <section class="rounded-3xl border border-line bg-white p-6">
                <p class="font-display text-lg font-semibold text-ink">Review your cars</p>
                <ul class="mt-3 divide-y divide-line">
                    @foreach ($order->cars as $car)
                        <li class="flex flex-wrap items-center justify-between gap-3 py-3">
                            <span class="text-sm font-medium text-ink">{{ $car->brand?->brand_name }} {{ $car->model_name }} ({{ $car->model_year }})</span>
                            @if (($reviewsLeft[$car->car_id] ?? 0) > 0)
                                <a href="{{ route('products.show', $car) }}#write-review" class="inline-flex items-center gap-1.5 rounded-xl bg-sun px-4 py-2 text-sm font-semibold text-ink hover:brightness-95">
                                    ★ Write a review
                                </a>
                            @else
                                <span class="flex items-center gap-1.5 text-sm text-emerald-700">
                                    <x-store.icon name="check-circle" class="size-4" /> Reviewed ·
                                    <a href="{{ route('products.show', $car) }}#reviews" class="font-semibold text-race hover:underline">View</a>
                                </span>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </section>
        @elseif (in_array($order->status, ['pending', 'processing'], true))
            <p class="flex items-center gap-2 rounded-2xl border border-line bg-white px-5 py-4 text-sm text-zinc-500">
                <x-store.icon name="info" class="size-4 text-zinc-400" /> You can review these cars once the order is completed.
            </p>
        @endif
    </div>
@endsection
