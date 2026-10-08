@extends('layouts.public')

@php
    use App\Support\Money;

    $tier = $quote['tier'];
    $percent = rtrim(rtrim(number_format((float) $tier->discount_percent, 2), '0'), '.');
    $hasStockError = collect($quote['items'])->contains(fn ($i) => $i['quantity'] > (int) $i['car']->stock_qty);
    $methods = [
        'bank_transfer' => ['receipt', 'Bank transfer', 'Transfer to the store account and send the slip to our staff.'],
        'cash' => ['coins', 'Pay at the store', 'Pay by cash or card at the showroom when you pick up the car.'],
    ];
    $chosen = old('payment_method', 'bank_transfer');
@endphp

@section('content')
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <a href="{{ route('cart.index') }}" class="inline-flex items-center gap-1 text-sm font-semibold text-zinc-500 hover:text-ink"><x-store.icon name="arrow-left" class="size-4" /> Back to Cart</a>
            <h1 class="mt-2 font-display text-4xl font-extrabold uppercase italic leading-none text-ink">Checkout</h1>
        </div>
        @include('partials.store.checkout-steps', ['current' => 2])
    </div>

    <div class="mt-6">
        <x-store.flash />
    </div>

    <form method="POST" action="{{ route('checkout.store') }}" class="grid gap-6 lg:grid-cols-[1fr_400px] lg:items-start">
        @csrf
        <input type="hidden" name="checkout_token" value="{{ $checkoutToken }}">

        <div class="space-y-6">
            {{-- ที่อยู่จัดส่ง --}}
            <section class="rounded-3xl border border-line bg-white p-6">
                <h2 class="flex items-center gap-2 font-display text-xl font-semibold text-ink">
                    <span class="flex size-8 items-center justify-center rounded-full bg-ink text-sm text-white">1</span> Shipping address
                </h2>
                <p class="mt-1 text-sm text-zinc-500">Filled from your profile. Changes here apply to this order only.</p>
                <label for="shipping_address" class="sr-only">Shipping address</label>
                <textarea id="shipping_address" name="shipping_address" rows="4" required maxlength="5000" class="field mt-4" placeholder="House number, street, district, province, postcode">{{ old('shipping_address', auth()->user()->address) }}</textarea>
                @error('shipping_address')
                    <p class="mt-1.5 text-xs text-race">{{ $message }}</p>
                @enderror
                <p class="mt-3 flex items-center gap-2 text-sm text-zinc-600">
                    <x-store.icon name="phone" class="size-4 text-zinc-400" />
                    Phone: {{ auth()->user()->phone ?: 'Not set' }}
                    <a href="{{ route('profile.edit') }}" class="text-xs font-semibold text-race hover:underline">Edit in profile</a>
                </p>
            </section>

            {{-- วิธีชำระเงิน (payment_method: bank_transfer / cash) --}}
            <section class="rounded-3xl border border-line bg-white p-6">
                <h2 class="flex items-center gap-2 font-display text-xl font-semibold text-ink">
                    <span class="flex size-8 items-center justify-center rounded-full bg-ink text-sm text-white">2</span> Payment method
                </h2>
                <div class="mt-4 grid gap-3 sm:grid-cols-2">
                    @foreach ($methods as $value => [$icon, $label, $hint])
                        <label class="relative flex cursor-pointer gap-3 rounded-2xl border-2 border-line p-4 transition has-[:checked]:border-race has-[:checked]:bg-race-soft/40">
                            <input type="radio" name="payment_method" value="{{ $value }}" @checked($chosen === $value) required class="peer sr-only">
                            <span class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-paper text-race"><x-store.icon :name="$icon" class="size-5" /></span>
                            <span>
                                <span class="block font-semibold text-ink">{{ $label }}</span>
                                <span class="mt-0.5 block text-xs text-zinc-500">{{ $hint }}</span>
                            </span>
                            <span class="absolute right-3 top-3 hidden size-5 items-center justify-center rounded-full bg-race text-white peer-checked:flex"><x-store.icon name="check" class="size-3" stroke="3" /></span>
                        </label>
                    @endforeach
                </div>
                @error('payment_method')
                    <p class="mt-1.5 text-xs text-race">{{ $message }}</p>
                @enderror
                <p class="mt-4 flex items-start gap-2 rounded-xl bg-paper px-3 py-2.5 text-xs text-zinc-600">
                    <x-store.icon name="info" class="mt-0.5 size-4 text-zinc-400" />
                    After placing the order, please contact the store to arrange payment and delivery. An admin will verify it before marking the order as completed. We never store your card or bank details.
                </p>
            </section>
        </div>

        {{-- สรุปยอด --}}
        <aside class="rounded-3xl border border-line bg-white p-6 lg:sticky lg:top-24">
            <p class="font-display text-xl font-semibold text-ink">Order summary</p>

            <ul class="mt-4 divide-y divide-line">
                @foreach ($quote['items'] as $item)
                    @php $car = $item['car']; $over = $item['quantity'] > (int) $car->stock_qty; @endphp
                    <li class="flex gap-3 py-3">
                        <x-store.car-photo :car="$car" size="thumb" class="aspect-[4/3] w-20 shrink-0 rounded-lg" />
                        <div class="min-w-0 flex-1 text-sm">
                            <p class="truncate font-semibold text-ink">{{ $car->brand?->brand_name }} {{ $car->model_name }}</p>
                            <p class="text-xs text-zinc-500">{{ $car->model_year }} · × {{ $item['quantity'] }}</p>
                            @if ($over)
                                <p class="mt-1 text-xs font-medium text-race">Only {{ $car->stock_qty }} left. Please update your cart.</p>
                            @endif
                        </div>
                        <p class="text-sm font-semibold text-ink">฿{{ Money::display($item['line_cents']) }}</p>
                    </li>
                @endforeach
            </ul>

            <dl class="mt-2 space-y-3 border-t border-line pt-4 text-sm">
                <div class="flex justify-between"><dt class="text-zinc-500">Subtotal</dt><dd class="font-semibold text-ink">฿{{ Money::display($quote['subtotal']) }}</dd></div>
                <div class="flex justify-between">
                    <dt class="text-zinc-500">{{ $tier->tier_name }} member discount ({{ $percent }}%)</dt>
                    <dd class="font-semibold {{ $quote['discount'] > 0 ? 'text-emerald-600' : 'text-zinc-400' }}">−฿{{ Money::display($quote['discount']) }}</dd>
                </div>
                <div class="flex items-end justify-between border-t border-line pt-4">
                    <dt class="font-semibold text-ink">Total</dt>
                    <dd class="font-display text-3xl font-extrabold text-race">฿{{ Money::display($quote['total']) }}</dd>
                </div>
            </dl>

            <p class="mt-4 flex items-center gap-2 rounded-xl bg-sun-soft px-3 py-2.5 text-xs text-ink">
                <x-store.icon name="coins" class="size-4" />
                Earn <strong>{{ number_format(Money::points($quote['total'])) }} points</strong> when this order is completed (1 point per ฿1,000, rounded down)
            </p>

            @if ($hasStockError)
                <a href="{{ route('cart.index') }}" class="mt-5 flex w-full items-center justify-center rounded-xl bg-ink px-4 py-3 text-sm font-semibold text-white">Back to Cart</a>
            @else
                <button type="submit" class="mt-5 flex w-full items-center justify-center gap-2 rounded-xl bg-race px-4 py-3 text-sm font-semibold text-white hover:bg-race-dark">
                    <x-store.icon name="check-circle" class="size-4" /> Place Order
                </button>
            @endif
            <p class="mt-3 text-center text-xs text-zinc-500">By placing the order you agree to the store terms of sale.</p>
        </aside>
    </form>
@endsection
