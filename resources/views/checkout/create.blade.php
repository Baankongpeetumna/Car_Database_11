@extends('layouts.public')

@section('content')
    @include('commerce.messages')

    <h1 class="mb-4 text-2xl font-semibold">
        ยืนยันคำสั่งซื้อ
    </h1>

    <a href="{{ route('cart.index') }}" class="text-blue-600">
        ← กลับไปแก้ตะกร้า
    </a>

    <div class="my-5 space-y-3 rounded-xl border border-zinc-200 p-5 dark:border-zinc-700">
        @foreach ($quote['items'] as $item)
            <div class="flex flex-wrap justify-between gap-2">
                <span>
                    {{ $item['car']->model_name }}
                    × {{ $item['quantity'] }}
                </span>

                <span>
                    ฿{{ \App\Support\Money::display($item['line_cents']) }}
                </span>
            </div>

            @if ($item['quantity'] > $item['car']->stock_qty)
                <p class="text-red-600">
                    {{ $item['car']->model_name }}
                    เหลือ {{ $item['car']->stock_qty }} คัน
                    กรุณาแก้ตะกร้า
                </p>
            @endif
        @endforeach

        <hr class="border-zinc-300 dark:border-zinc-700">

        <p>
            ระดับสมาชิก:
            {{ $quote['tier']->tier_name }}
            (ส่วนลด {{ $quote['tier']->discount_percent }}%)
        </p>

        <p>
            ยอดก่อนส่วนลด:
            ฿{{ \App\Support\Money::display($quote['subtotal']) }}
        </p>

        <p>
            ส่วนลด:
            ฿{{ \App\Support\Money::display($quote['discount']) }}
        </p>

        <p class="text-xl font-semibold">
            ยอดสุทธิ:
            ฿{{ \App\Support\Money::display($quote['total']) }}
        </p>

        <p class="text-sm text-zinc-500">
            ได้รับ
            {{ number_format(\App\Support\Money::points($quote['total'])) }}
            คะแนนเมื่อคำสั่งซื้อสำเร็จ
            (ยอดสุทธิทุก 1,000 บาท = 1 คะแนน เศษปัดทิ้ง)
        </p>
    </div>

    <form method="POST"
          action="{{ route('checkout.store') }}"
          class="space-y-4">
        @csrf

        <input type="hidden"
               name="checkout_token"
               value="{{ $checkoutToken }}">

        <div>
            <label for="shipping_address" class="mb-2 block">
                ที่อยู่จัดส่ง
            </label>

            <textarea
                id="shipping_address"
                name="shipping_address"
                rows="4"
                required
                maxlength="5000"
                class="w-full rounded-lg border border-zinc-300 bg-white p-3 dark:border-zinc-600 dark:bg-zinc-800"
            >{{ old('shipping_address', auth()->user()->address) }}</textarea>
        </div>

        <div>
            <label for="payment_method" class="mb-2 block">
                วิธีชำระเงิน
            </label>

            <select
                id="payment_method"
                name="payment_method"
                required
                class="w-full rounded-lg border border-zinc-300 bg-white p-3 dark:border-zinc-600 dark:bg-zinc-800"
            >
                <option value="bank_transfer"
                        @selected(old('payment_method') === 'bank_transfer')>
                    โอนเงิน
                </option>

                <option value="cash"
                        @selected(old('payment_method') === 'cash')>
                    ชำระเงินที่ร้าน
                </option>
            </select>
        </div>

        <p class="text-sm text-zinc-500">
            หลังยืนยัน กรุณาติดต่อร้านเพื่อชำระเงินและนัดหมายส่งมอบรถ
            ผู้ดูแลจะตรวจสอบก่อนปรับสถานะเป็นสำเร็จ
        </p>

        <button type="submit"
                class="rounded-lg bg-blue-600 px-5 py-3 text-white">
            ยืนยันคำสั่งซื้อ
        </button>
    </form>
@endsection