@extends('layouts.public')

@php
    use App\Support\CarVisual;
    use App\Support\Money;

    $tier = auth()->user()->tier;
    $discountCents = $tier ? Money::discount($subtotalCents, (string) $tier->discount_percent) : 0;
    $totalCents = $subtotalCents - $discountCents;
    $percent = $tier ? rtrim(rtrim(number_format((float) $tier->discount_percent, 2), '0'), '.') : '0';
@endphp

@section('content')
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="font-display text-4xl font-extrabold uppercase italic leading-none text-ink">My Cart</h1>
            <p class="mt-2 text-sm text-zinc-500">{{ $items->count() }} {{ Str::plural('item', $items->count()) }} · {{ $totalQuantity }} {{ Str::plural('car', $totalQuantity) }} in total</p>
        </div>
        @include('partials.store.checkout-steps', ['current' => 1])
    </div>

    <div class="mt-6">
        <x-store.flash />
    </div>

    @if ($items->isEmpty())
        <div class="flex flex-col items-center rounded-3xl border border-dashed border-line bg-white px-6 py-16 text-center">
            <x-store.car-art color="#C5CAD1" type="hatchback" class="w-60 opacity-80" />
            <p class="mt-4 font-display text-2xl font-semibold text-ink">Your cart is empty.</p>
            <p class="mt-1 text-sm text-zinc-500">Find a car you like and press "Add to Cart" on its details page.</p>
            <a href="{{ route('products.index') }}" class="mt-6 inline-flex items-center gap-2 rounded-xl bg-race px-6 py-3 text-sm font-semibold text-white hover:bg-race-dark">
                Browse cars <x-store.icon name="arrow-right" class="size-4" />
            </a>
        </div>
    @else
        <div class="grid gap-6 lg:grid-cols-[1fr_380px] lg:items-start">
            {{-- รายการในตะกร้า --}}
            <div class="space-y-4">
                @foreach ($items as $item)
                    @php
                        $car = $item['car'];
                        $overStock = $item['quantity'] > (int) $car->stock_qty;
                    @endphp
                    <article @class(['flex flex-col gap-4 rounded-2xl border bg-white p-4 sm:flex-row', 'border-race' => $overStock, 'border-line' => ! $overStock])>
                        <a href="{{ route('products.show', $car) }}" class="relative block shrink-0 overflow-hidden rounded-xl sm:w-48">
                            <x-store.car-photo :car="$car" class="aspect-[4/3]" />
                            <x-store.year-plate :year="$car->model_year" class="absolute left-2 top-2" />
                        </a>

                        <div class="flex min-w-0 flex-1 flex-col">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="text-[11px] font-semibold uppercase tracking-wider text-zinc-500">{{ $car->brand?->brand_name }} · {{ $car->category?->category_name }}</p>
                                    <h2 class="font-display text-lg font-semibold uppercase leading-tight text-ink">
                                        <a href="{{ route('products.show', $car) }}" class="hover:text-race">{{ $car->model_name }}</a>
                                    </h2>
                                    <p class="mt-1 text-xs text-zinc-500">{{ number_format($car->mileage_km) }} km · {{ $car->fuel_type }} · {{ $car->transmission }} · <x-store.condition-badge :condition="$car->car_condition" class="!px-2 !py-0.5 align-middle" /></p>
                                </div>
                                <form method="POST" action="{{ route('cart.destroy', $car) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="rounded-lg p-2 text-zinc-400 hover:bg-race-soft hover:text-race" aria-label="Remove {{ $car->model_name }} from cart">
                                        <x-store.icon name="trash" class="size-4" />
                                    </button>
                                </form>
                            </div>

                            @if ($overStock)
                                <p class="mt-3 flex items-center gap-2 rounded-xl bg-race-soft px-3 py-2 text-xs font-medium text-race-dark">
                                    <x-store.icon name="alert" class="size-4" />
                                    Only {{ $car->stock_qty }} left in stock. Please reduce the quantity or remove this item.
                                </p>
                            @endif

                            <div class="mt-auto flex flex-wrap items-end justify-between gap-3 pt-4">
                                <form method="POST" action="{{ route('cart.update', $car) }}" class="flex items-center gap-2" data-autosubmit>
                                    @csrf
                                    @method('PATCH')
                                    <label for="quantity-{{ $car->car_id }}" class="text-xs text-zinc-500">Quantity</label>
                                    <div class="flex items-center rounded-xl border border-line" data-stepper>
                                        <button type="button" class="p-2 text-zinc-500 hover:text-ink" data-step="-1" aria-label="Decrease quantity"><x-store.icon name="minus" class="size-4" /></button>
                                        <input id="quantity-{{ $car->car_id }}" type="number" name="quantity" min="1" max="{{ max(1, (int) $car->stock_qty) }}" value="{{ $item['quantity'] }}" required class="w-10 border-0 bg-transparent text-center font-display font-semibold text-ink focus:outline-none [appearance:textfield] [&::-webkit-inner-spin-button]:appearance-none">
                                        <button type="button" class="p-2 text-zinc-500 hover:text-ink" data-step="1" aria-label="Increase quantity"><x-store.icon name="plus" class="size-4" /></button>
                                    </div>
                                    <button type="submit" class="rounded-lg px-2 py-1 text-xs font-semibold text-race hover:underline" data-update-btn>Update</button>
                                    <span class="text-xs text-zinc-400">({{ $car->stock_qty }} in stock)</span>
                                </form>
                                <div class="text-right">
                                    <p class="text-xs text-zinc-500">฿{{ Money::display($item['unit_price_cents']) }} each</p>
                                    <p class="font-display text-xl font-bold text-ink">฿{{ Money::display($item['line_total_cents']) }}</p>
                                </div>
                            </div>
                        </div>
                    </article>
                @endforeach

                <a href="{{ route('products.index') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-race hover:underline">
                    <x-store.icon name="arrow-left" class="size-4" /> Continue shopping
                </a>
            </div>

            {{-- สรุปยอด --}}
            <aside class="rounded-3xl border border-line bg-white p-6 lg:sticky lg:top-24">
                <p class="font-display text-xl font-semibold text-ink">Order summary</p>
                <dl class="mt-5 space-y-3 text-sm">
                    <div class="flex justify-between"><dt class="text-zinc-500">Subtotal ({{ $totalQuantity }} {{ Str::plural('car', $totalQuantity) }})</dt><dd class="font-semibold text-ink">฿{{ Money::display($subtotalCents) }}</dd></div>
                    <div class="flex justify-between">
                        <dt class="text-zinc-500">{{ $tier?->tier_name }} member discount ({{ $percent }}%)</dt>
                        <dd class="font-semibold {{ $discountCents > 0 ? 'text-emerald-600' : 'text-zinc-400' }}">−฿{{ Money::display($discountCents) }}</dd>
                    </div>
                    <div class="flex items-end justify-between border-t border-line pt-4">
                        <dt class="font-semibold text-ink">Total</dt>
                        <dd class="font-display text-3xl font-extrabold text-race">฿{{ Money::display($totalCents) }}</dd>
                    </div>
                </dl>

                <p class="mt-4 flex items-center gap-2 rounded-xl bg-sun-soft px-3 py-2.5 text-xs text-ink">
                    <x-store.icon name="coins" class="size-4" />
                    Earn about <strong>{{ number_format(Money::points($totalCents)) }} points</strong> when the order is completed
                </p>

                @if ($canCheckout)
                    <a href="{{ route('checkout.create') }}" class="mt-5 flex w-full items-center justify-center gap-2 rounded-xl bg-race px-4 py-3 text-sm font-semibold text-white hover:bg-race-dark">
                        Proceed to Checkout <x-store.icon name="arrow-right" class="size-4" />
                    </a>
                @else
                    <button type="button" disabled class="mt-5 w-full rounded-xl bg-paper px-4 py-3 text-sm font-semibold text-zinc-400">Proceed to Checkout</button>
                    <p class="mt-2 text-center text-xs text-race">Please adjust quantities to match the available stock before continuing.</p>
                @endif

                <p class="mt-4 text-xs leading-relaxed text-zinc-500">Items in the cart are not reserved. Price, stock and discount will be checked again at checkout.</p>
            </aside>
        </div>
    @endif
@endsection

@push('scripts')
    <script>
        // ปุ่ม +/- เปลี่ยนจำนวนแล้วบันทึกทันที (ซ่อนปุ่ม "อัปเดต" เมื่อมี JS)
        document.querySelectorAll('[data-autosubmit]').forEach((form) => {
            const input = form.querySelector('input[name=quantity]');
            form.querySelector('[data-update-btn]').classList.add('sr-only');
            form.querySelectorAll('[data-step]').forEach((btn) => btn.addEventListener('click', () => {
                const max = Number(input.max) || 1;
                const next = Math.min(max, Math.max(1, (Number(input.value) || 1) + Number(btn.dataset.step)));
                if (next !== Number(input.value)) { input.value = next; form.submit(); }
            }));
            input.addEventListener('change', () => form.submit());
        });
    </script>
@endpush
