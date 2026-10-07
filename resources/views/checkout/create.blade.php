@extends('layouts.public')

@section('content')
    @include('commerce.messages')

    <h1 class="mb-4 text-2xl font-semibold">
        Checkout
    </h1>

    <a href="{{ route('cart.index') }}" class="text-blue-600">
        ← Back to Cart
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
                    {{ $item['car']->model_name }}:
                    only {{ $item['car']->stock_qty }} left.
                    Please update your cart.
                </p>
            @endif
        @endforeach

        <hr class="border-zinc-300 dark:border-zinc-700">

        <p>
            Membership tier:
            {{ $quote['tier']->tier_name }}
            ({{ $quote['tier']->discount_percent }}% discount)
        </p>

        <p>
            Subtotal:
            ฿{{ \App\Support\Money::display($quote['subtotal']) }}
        </p>

        <p>
            Discount:
            ฿{{ \App\Support\Money::display($quote['discount']) }}
        </p>

        <p class="text-xl font-semibold">
            Total:
            ฿{{ \App\Support\Money::display($quote['total']) }}
        </p>

        <p class="text-sm text-zinc-500">
            You will earn
            {{ number_format(\App\Support\Money::points($quote['total'])) }}
            points when this order is completed
            (1 point per ฿1,000 of the total, rounded down).
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
                Shipping address
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
                Payment method
            </label>

            <select
                id="payment_method"
                name="payment_method"
                required
                class="w-full rounded-lg border border-zinc-300 bg-white p-3 dark:border-zinc-600 dark:bg-zinc-800"
            >
                <option value="bank_transfer"
                        @selected(old('payment_method') === 'bank_transfer')>
                    Bank transfer
                </option>

                <option value="cash"
                        @selected(old('payment_method') === 'cash')>
                    Pay at the store
                </option>
            </select>
        </div>

        <p class="text-sm text-zinc-500">
            After placing the order, please contact the store to arrange payment and delivery.
            An admin will verify it before marking the order as completed.
        </p>

        <button type="submit"
                class="rounded-lg bg-blue-600 px-5 py-3 text-white">
            Place Order
        </button>
    </form>
@endsection