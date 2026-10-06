@extends('layouts.public')

@section('content')
    @include('commerce.messages')

    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-semibold">ตะกร้าของฉัน</h1>

            <p class="mt-1 text-sm text-zinc-500">
                {{ $items->count() }} รายการ
                รวม {{ $totalQuantity }} คัน
            </p>
        </div>

        <a href="{{ route('products.index') }}"
           class="rounded-lg border px-4 py-2">
            เลือกรถเพิ่มเติม
        </a>
    </div>

    @forelse ($items as $item)
        @php
            $car = $item['car'];
        @endphp

        <div class="mb-4 flex flex-col gap-4 rounded-xl border border-zinc-200 p-4 dark:border-zinc-700 sm:flex-row">
            <div class="flex-1">
                <h2 class="font-semibold">
                    {{ $car->brand?->brand_name }}
                    {{ $car->model_name }}
                    ({{ $car->model_year }})
                </h2>

                <p class="mt-2">
                    ราคาต่อคัน
                    ฿{{ \App\Support\Money::display($item['unit_price_cents']) }}
                </p>

                <p class="mt-1 text-sm text-zinc-500">
                    สต็อกปัจจุบัน {{ $car->stock_qty }} คัน
                </p>

                @if ($item['quantity'] > $car->stock_qty)
                    <p class="mt-2 text-red-600">
                        จำนวนเกินสต็อกปัจจุบัน
                        กรุณาลดจำนวนหรือลบรายการ
                    </p>
                @endif
            </div>

            <div>
                <p class="font-semibold">
                    รวม
                    ฿{{ \App\Support\Money::display($item['line_total_cents']) }}
                </p>

                <form method="POST"
                      action="{{ route('cart.update', $car) }}"
                      class="mt-3 flex items-center gap-2">
                    @csrf
                    @method('PATCH')

                    <label for="quantity-{{ $car->car_id }}"
                           class="sr-only">
                        จำนวน {{ $car->model_name }}
                    </label>

                    <input
                        id="quantity-{{ $car->car_id }}"
                        type="number"
                        name="quantity"
                        min="1"
                        max="{{ max(1, (int) $car->stock_qty) }}"
                        value="{{ $item['quantity'] }}"
                        required
                        class="w-24 rounded-lg border border-zinc-300 bg-white p-2 dark:border-zinc-600 dark:bg-zinc-800"
                    >

                    <button
                        type="submit"
                        @disabled($car->stock_qty < 1)
                        class="rounded-lg bg-blue-600 px-4 py-2 text-white disabled:opacity-50"
                    >
                        อัปเดต
                    </button>
                </form>

                <form method="POST"
                      action="{{ route('cart.destroy', $car) }}"
                      class="mt-3">
                    @csrf
                    @method('DELETE')

                    <button type="submit" class="text-red-600">
                        ลบรายการ
                    </button>
                </form>
            </div>
        </div>
    @empty
        <p class="rounded-xl border p-8 text-center text-zinc-500">
            ยังไม่มีรถในตะกร้า
        </p>
    @endforelse

    @if ($items->isNotEmpty())
        <div class="mt-6 rounded-xl border border-zinc-200 p-5 dark:border-zinc-700">
            <p class="text-xl font-semibold">
                ยอดก่อนส่วนลด
                ฿{{ \App\Support\Money::display($subtotalCents) }}
            </p>

            <p class="my-3 text-sm text-zinc-500">
                ตะกร้ายังไม่จองสต็อก
                ตรวจราคา สต็อก และส่วนลดอีกครั้งตอนยืนยันซื้อ
            </p>

            @if ($canCheckout)
                <a href="{{ route('checkout.create') }}"
                   class="inline-block rounded-lg bg-blue-600 px-5 py-2 text-white">
                    ไปยืนยันคำสั่งซื้อ
                </a>
            @else
                <p class="text-red-600">
                    กรุณาแก้จำนวนให้ตรงกับสต็อกก่อนดำเนินการต่อ
                </p>
            @endif
        </div>
    @endif
@endsection