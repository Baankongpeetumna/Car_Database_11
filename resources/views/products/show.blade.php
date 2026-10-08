@extends('layouts.public')

@php
    use App\Support\CarVisual;
    use App\Support\Money;

    $user = auth()->user();
    $soldOut = (int) $car->stock_qty < 1;
    $isNew = CarVisual::isNew($car->car_condition);

    // ประมาณการส่วนลดและแต้มตามระดับสมาชิก (ราคาจริงคำนวณอีกครั้งตอน checkout)
    $tier = $user?->isMember() ? $user->tier : null;
    $priceCents = Money::cents((string) $car->price);
    $discountCents = $tier ? Money::discount($priceCents, (string) $tier->discount_percent) : 0;
    $pointsPreview = Money::points($priceCents - $discountCents);
    $percent = $tier ? rtrim(rtrim(number_format((float) $tier->discount_percent, 2), '0'), '.') : null;

    $specs = [
        ['calendar', 'Model year', $car->model_year],
        ['palette', 'Color', $car->color],
        ['fuel', 'Fuel type', $car->fuel_type],
        ['gear', 'Transmission', $car->transmission],
        ['engine', 'Engine', (int) $car->engine_cc > 0 ? number_format($car->engine_cc).' cc' : 'Electric'],
        ['gauge', 'Mileage', number_format($car->mileage_km).' km'],
        ['shield-check', 'Condition', CarVisual::conditionLabel($car->car_condition)],
        ['tag', 'Brand', trim(($car->brand?->brand_name ?? '-').($car->brand?->country ? ' ('.$car->brand->country.')' : ''))],
        ['car', 'Category', $car->category?->category_name ?? '-'],
    ];
@endphp

@section('content')
    <nav class="mb-5 flex flex-wrap items-center gap-1 text-xs text-zinc-500" aria-label="Breadcrumb">
        <a href="{{ route('home') }}" class="hover:text-ink">Home</a> <span>/</span>
        <a href="{{ route('products.index', ['condition' => $isNew ? 'new' : 'used']) }}" class="hover:text-ink">{{ $isNew ? 'New cars' : 'Used cars' }}</a> <span>/</span>
        @if ($car->brand)
            <a href="{{ route('products.index', ['brand' => $car->brand_id]) }}" class="hover:text-ink">{{ $car->brand->brand_name }}</a> <span>/</span>
        @endif
        <span class="text-ink">{{ $car->model_name }}</span>
    </nav>

    <x-store.flash />

    <div class="grid gap-8 lg:grid-cols-[1.5fr_1fr] lg:items-start">
        {{-- รูปรถ (CAR.image_url เก็บได้ 1 รูป) --}}
        <div class="relative overflow-hidden rounded-3xl border border-line bg-white">
            <x-store.car-photo :car="$car" size="hero" class="aspect-[4/3] {{ $soldOut ? 'grayscale' : '' }}" />
            <div class="absolute left-4 top-4 flex items-center gap-2">
                <x-store.year-plate :year="$car->model_year" class="!text-sm" />
                <x-store.condition-badge :condition="$car->car_condition" class="!text-xs" />
            </div>
            @if ($soldOut)
                <div class="absolute inset-0 flex items-center justify-center bg-white/55">
                    <span class="-rotate-6 rounded-xl border-2 border-ink bg-white px-6 py-2 font-display text-2xl font-bold text-ink">Sold out</span>
                </div>
            @endif
        </div>

        {{-- กล่องซื้อ --}}
        <aside class="rounded-3xl border border-line bg-white p-6 lg:sticky lg:top-24">
            <div class="flex flex-wrap gap-2">
                @if ($car->brand)
                    <a href="{{ route('products.index', ['brand' => $car->brand_id]) }}" class="rounded-full bg-paper px-3 py-1 text-xs font-semibold text-ink hover:bg-line">{{ $car->brand->brand_name }}</a>
                @endif
                @if ($car->category)
                    <a href="{{ route('products.index', ['category' => $car->category_id]) }}" class="rounded-full bg-paper px-3 py-1 text-xs font-semibold text-ink hover:bg-line">{{ $car->category->category_name }}</a>
                @endif
            </div>

            <h1 class="mt-3 font-display text-3xl font-extrabold uppercase italic leading-tight text-ink">{{ $car->model_name }}</h1>
            <p class="mt-1 text-sm text-zinc-500">{{ $car->model_year }} · {{ $car->color }} · {{ number_format($car->mileage_km) }} km</p>

            {{-- คะแนนเฉลี่ย (นับเฉพาะรีวิวที่มี rating) --}}
            <a href="#reviews" class="mt-3 inline-flex items-center gap-2 text-sm">
                @if ($averageRating !== null)
                    <x-store.stars :rating="(int) round($averageRating)" />
                    <span class="font-semibold text-ink">{{ number_format($averageRating, 1) }} / 5</span>
                    <span class="text-zinc-500">({{ $ratedCount }} {{ Str::plural('rating', $ratedCount) }})</span>
                @else
                    <span class="text-zinc-500">No ratings yet</span>
                @endif
            </a>

            <p class="mt-5 font-display text-4xl font-extrabold text-race">฿{{ number_format((float) $car->price, 2) }}</p>
            <p class="mt-1 text-sm {{ $soldOut ? 'text-race' : 'text-zinc-600' }}">
                @if ($soldOut)
                    Out of stock
                @else
                    <span class="mr-1 inline-block size-2 rounded-full bg-emerald-500 align-middle"></span> In stock ({{ $car->stock_qty }} left)
                @endif
            </p>

            @if ($tier)
                <div class="mt-5 rounded-2xl border border-sun bg-sun-soft p-4 text-sm">
                    <p class="flex items-center gap-2 font-semibold text-ink">
                        <x-store.icon name="crown" class="size-4" />
                        {{ $tier->tier_name }} member{{ $discountCents > 0 ? ' · '.$percent.'% off' : '' }}
                    </p>
                    <p class="mt-1 text-zinc-700">
                        @if ($discountCents > 0)
                            About <span class="font-semibold text-ink">฿{{ Money::display($priceCents - $discountCents) }}</span> after your discount ·
                        @endif
                        earn about <span class="font-semibold text-ink">{{ number_format($pointsPreview) }} {{ Str::plural('point', $pointsPreview) }}</span> when the order is completed.
                    </p>
                </div>
            @endif

            <div class="mt-6 border-t border-line pt-6">
                @if ($user?->isMember())
                    @if ($soldOut)
                        <button type="button" disabled class="w-full rounded-xl bg-paper px-4 py-3 text-sm font-semibold text-zinc-400">Out of stock</button>
                    @else
                        <form method="POST" action="{{ route('cart.store', $car) }}" class="space-y-3">
                            @csrf
                            <div class="flex items-center justify-between">
                                <label for="quantity" class="text-sm font-medium text-ink">Quantity</label>
                                <div class="flex items-center rounded-xl border border-line" data-stepper>
                                    <button type="button" class="p-2.5 text-zinc-500 hover:text-ink" data-step="-1" aria-label="Decrease quantity"><x-store.icon name="minus" class="size-4" /></button>
                                    <input id="quantity" type="number" name="quantity" value="1" min="1" max="{{ $car->stock_qty }}" step="1" required class="w-12 border-0 bg-transparent text-center font-display font-semibold text-ink focus:outline-none [appearance:textfield] [&::-webkit-inner-spin-button]:appearance-none">
                                    <button type="button" class="p-2.5 text-zinc-500 hover:text-ink" data-step="1" aria-label="Increase quantity"><x-store.icon name="plus" class="size-4" /></button>
                                </div>
                            </div>
                            <button type="submit" class="flex w-full items-center justify-center gap-2 rounded-xl bg-race px-4 py-3 text-sm font-semibold text-white hover:bg-race-dark">
                                <x-store.icon name="cart" class="size-4" /> Add to Cart
                            </button>
                        </form>
                    @endif
                @elseif ($user?->isAdmin())
                    <p class="rounded-xl bg-paper px-4 py-3 text-sm text-zinc-600">Admin accounts cannot place orders.</p>
                    <a href="{{ route('admin.cars.edit', $car) }}" class="mt-3 flex items-center justify-center gap-2 rounded-xl bg-ink px-4 py-3 text-sm font-semibold text-white hover:bg-ink-soft">
                        <x-store.icon name="pencil" class="size-4" /> Edit in Admin Panel
                    </a>
                @else
                    <a href="{{ route('login') }}" class="flex w-full items-center justify-center gap-2 rounded-xl bg-race px-4 py-3 text-sm font-semibold text-white hover:bg-race-dark">
                        Log in to buy
                    </a>
                    <p class="mt-3 text-center text-xs text-zinc-500">No account yet? <a href="{{ route('register') }}" class="font-semibold text-race hover:underline">Sign up free</a> to get member discounts and points.</p>
                @endif
            </div>

            <ul class="mt-6 space-y-2 text-xs text-zinc-500">
                <li class="flex items-center gap-2"><x-store.icon name="shield-check" class="size-4 text-emerald-600" /> {{ $isNew ? 'Brand-new, never registered' : 'Inspected before delivery' }}</li>
                <li class="flex items-center gap-2"><x-store.icon name="receipt" class="size-4 text-emerald-600" /> Pay by bank transfer or at the store</li>
            </ul>
        </aside>
    </div>

    {{-- สเปก --}}
    <section class="mt-12">
        <x-store.section-title kicker="Spec sheet" title="Specifications" />
        <dl class="mt-6 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($specs as [$icon, $label, $value])
                <div class="flex items-center gap-4 rounded-2xl border border-line bg-white p-4">
                    <span class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-paper text-race">
                        <x-store.icon :name="$icon" class="size-5" />
                    </span>
                    <div>
                        <dt class="text-xs text-zinc-500">{{ $label }}</dt>
                        <dd class="font-display text-base font-semibold text-ink">{{ $value }}</dd>
                    </div>
                </div>
            @endforeach
        </dl>
    </section>

    @if (filled($car->description))
        <section class="mt-12">
            <x-store.section-title kicker="About this car" title="Description" />
            <div class="mt-6 rounded-2xl border border-line bg-white p-6 text-sm leading-relaxed text-zinc-700">
                <p class="whitespace-pre-line">{{ $car->description }}</p>
            </div>
        </section>
    @endif

    {{-- รีวิว: เขียนได้เฉพาะสมาชิกที่มีออเดอร์ completed ของรถคันนี้ (Review::remainingFor) --}}
    <section id="reviews" class="mt-12 scroll-mt-24">
        <x-store.section-title kicker="Reviews" :title="'Reviews ('.$reviews->count().')'">
            @if ($averageRating !== null)
                <x-slot:action>
                    <div class="flex items-center gap-3 rounded-2xl border border-line bg-white px-4 py-3">
                        <span class="font-display text-3xl font-extrabold text-ink">{{ number_format($averageRating, 1) }}</span>
                        <div>
                            <x-store.stars :rating="(int) round($averageRating)" />
                            <p class="text-xs text-zinc-500">
                                <span class="font-semibold text-ink">{{ number_format($averageRating, 1) }} out of 5</span>
                                · from {{ $ratedCount }} {{ Str::plural('rating', $ratedCount) }}
                            </p>
                        </div>
                    </div>
                </x-slot:action>
            @endif
        </x-store.section-title>

        <div class="mt-6 grid gap-6 lg:grid-cols-[1.5fr_1fr] lg:items-start">
            <div class="space-y-3">
                @forelse ($reviews as $review)
                    <article class="rounded-2xl border border-line bg-white p-5">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex items-center gap-3">
                                <span class="flex size-10 items-center justify-center rounded-full bg-ink font-display text-sm font-semibold text-white">{{ $review->member?->initials() ?? '?' }}</span>
                                <div>
                                    {{-- แสดงชื่อ + อักษรแรกของนามสกุล ไม่โชว์ชื่อเต็ม --}}
                                    <p class="text-sm font-semibold text-ink">
                                        {{ $review->member?->first_name }}
                                        {{ Str::upper(Str::substr($review->member?->last_name ?? '', 0, 1)) }}.
                                    </p>
                                    <div class="flex items-center gap-2 text-xs text-zinc-500">
                                        <x-store.stars :rating="$review->rating" size="size-3.5" />
                                        <span>{{ $review->created_at?->format('d/m/Y') }}</span>
                                    </div>
                                </div>
                            </div>

                            {{-- เจ้าของรีวิวแก้ไข/ลบได้ --}}
                            @if ($user && (int) $review->member_id === (int) $user->getKey())
                                <div class="flex items-center gap-1">
                                    <a href="{{ route('reviews.edit', $review) }}" class="rounded-lg p-1.5 text-zinc-400 hover:bg-paper hover:text-ink" aria-label="Edit your review"><x-store.icon name="pencil" class="size-4" /></a>
                                    <form method="POST" action="{{ route('reviews.destroy', $review) }}" data-confirm="Delete your review?" onsubmit="return confirm(this.dataset.confirm)">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="rounded-lg p-1.5 text-zinc-400 hover:bg-race-soft hover:text-race" aria-label="Delete your review"><x-store.icon name="trash" class="size-4" /></button>
                                    </form>
                                </div>
                            @endif
                        </div>
                        <p class="mt-3 whitespace-pre-line text-sm leading-relaxed text-zinc-700">{{ $review->comment }}</p>
                    </article>
                @empty
                    <div class="rounded-2xl border border-dashed border-line bg-white p-8 text-center text-sm text-zinc-500">
                        <x-store.icon name="message" class="mx-auto size-8 text-zinc-300" />
                        <p class="mt-2">No reviews yet.</p>
                    </div>
                @endforelse
            </div>

            <div class="rounded-2xl border border-line bg-white p-5">
                @if ($user?->isMember())
                    @if ($reviewsLeft > 0)
                        <form id="write-review" method="POST" action="{{ route('reviews.store', $car) }}" class="scroll-mt-24 space-y-4">
                            @csrf
                            <p class="font-display text-lg font-semibold text-ink">Write a review</p>
                            @error('review')
                                <p class="text-xs text-race">{{ $message }}</p>
                            @enderror
                            @include('reviews._form-fields', ['review' => null])
                            <button type="submit" class="w-full rounded-xl bg-ink px-4 py-2.5 text-sm font-semibold text-white hover:bg-ink-soft">Post Review</button>
                        </form>
                    @else
                        <p class="font-display text-lg font-semibold text-ink">Reviews from buyers</p>
                        <p class="mt-2 text-sm text-zinc-500">You can review this car after an order containing it is completed (one review per completed order).</p>
                    @endif
                @elseif ($user)
                    <p class="font-display text-lg font-semibold text-ink">Reviews from buyers</p>
                    <p class="mt-2 text-sm text-zinc-500">Only member accounts can review cars.</p>
                @else
                    <p class="font-display text-lg font-semibold text-ink">Reviews from buyers</p>
                    <p class="mt-2 text-sm text-zinc-500"><a href="{{ route('login') }}" class="font-semibold text-race hover:underline">Log in</a> to review a car you have bought.</p>
                @endif
            </div>
        </div>
    </section>

    @if ($similar->isNotEmpty())
        <section class="mt-12">
            <x-store.section-title kicker="You may also like" title="Similar cars" />
            <div class="mt-6 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($similar as $item)
                    <x-store.car-card :car="$item" />
                @endforeach
            </div>
        </section>
    @endif
@endsection

@push('scripts')
    <script>
        // ปุ่ม + / - ของจำนวน (ไม่เกินสต็อก)
        document.querySelectorAll('[data-stepper]').forEach((box) => {
            const input = box.querySelector('input');
            box.querySelectorAll('[data-step]').forEach((btn) => btn.addEventListener('click', () => {
                const max = Number(input.max) || 1;
                input.value = Math.min(max, Math.max(1, (Number(input.value) || 1) + Number(btn.dataset.step)));
            }));
        });
    </script>
@endpush
