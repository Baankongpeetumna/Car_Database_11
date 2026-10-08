@extends('layouts.public')

@section('bleed')
    @php
        use App\Support\CarVisual;
    @endphp

    {{-- ===== HERO ===== --}}
    <section class="relative overflow-hidden bg-white">
        <div class="checker-fade pointer-events-none absolute inset-y-0 right-0 w-1/2"></div>
        <div class="pointer-events-none absolute -right-24 top-10 size-[420px] rounded-full bg-race-soft"></div>

        <div class="relative mx-auto grid max-w-7xl items-center gap-10 px-4 pb-10 pt-12 sm:px-6 lg:grid-cols-[1.05fr_1fr] lg:px-8 lg:pt-16">
            <div>
                <p class="inline-flex items-center gap-2 rounded-full bg-sun px-3 py-1 text-xs font-semibold text-ink">
                    <x-store.icon name="flag" class="size-3.5" /> Brand-new cars · Quality-checked used cars
                </p>
                <h1 class="mt-5 font-display text-5xl font-extrabold uppercase italic leading-[0.92] tracking-tight text-ink sm:text-6xl lg:text-7xl">
                    Find your<br>next <span class="text-race">ride</span>
                </h1>
                <p class="mt-5 max-w-md text-base text-zinc-600">
                    {{ number_format($totalCars) }} cars ready to drive away. Honest prices, member discounts by tier, and points on every purchase.
                </p>
                <div class="mt-6 flex flex-wrap gap-3">
                    <a href="{{ route('products.index', ['condition' => 'new']) }}" class="inline-flex items-center gap-2 rounded-xl bg-race px-5 py-3 text-sm font-semibold text-white hover:bg-race-dark">
                        Shop new cars <x-store.icon name="arrow-right" class="size-4" />
                    </a>
                    <a href="{{ route('products.index', ['condition' => 'used']) }}" class="inline-flex items-center gap-2 rounded-xl border-2 border-ink px-5 py-2.5 text-sm font-semibold text-ink hover:bg-ink hover:text-white">
                        Shop used cars
                    </a>
                </div>
            </div>

            <div class="relative">
                <div class="relative">
                    <div class="absolute bottom-6 left-0 right-0 h-3 -skew-x-12 bg-race/90"></div>
                    <div class="absolute bottom-0 left-10 right-16 h-2 -skew-x-12 bg-ink"></div>
                    <x-store.car-art
                        :color="$heroCar ? CarVisual::colorHex($heroCar->color) : '#D7322E'"
                        :type="$heroCar ? CarVisual::bodyType($heroCar->category?->category_name) : 'coupe'"
                        class="relative -rotate-2 drop-shadow-xl"
                    />
                </div>
                @if ($heroCar)
                    <a href="{{ Route::has('products.show') ? route('products.show', $heroCar) : route('products.index') }}" class="relative mt-4 block w-fit rounded-2xl border border-line bg-white/95 px-4 py-3 shadow-lg transition hover:border-race sm:absolute sm:right-6 sm:top-0 sm:mt-0">
                        <p class="text-[11px] font-semibold uppercase tracking-wider text-zinc-500">Just arrived</p>
                        <p class="font-display text-base font-semibold uppercase text-ink">{{ $heroCar->brand?->brand_name }} {{ $heroCar->model_name }}</p>
                        <p class="font-display text-lg font-bold text-race">{{ CarVisual::baht($heroCar->price) }}</p>
                    </a>
                @endif
            </div>
        </div>

        {{-- กล่องค้นหา --}}
        <div class="relative mx-auto max-w-7xl px-4 pb-12 sm:px-6 lg:px-8">
            <form method="GET" action="{{ route('products.index') }}" class="grid gap-3 rounded-2xl border border-line bg-white p-3 shadow-[0_18px_40px_-20px_rgb(27_31_36/0.35)] md:grid-cols-[repeat(3,1fr)_auto]" role="search" aria-label="Search cars">
                <label class="flex items-center gap-3 rounded-xl bg-paper px-4 py-2.5">
                    <x-store.icon name="tag" class="size-5 text-race" />
                    <span class="grid flex-1">
                        <span class="text-[11px] font-semibold text-zinc-500">Brand</span>
                        <select name="brand" class="-ml-1 bg-transparent text-sm font-medium text-ink focus:outline-none">
                            <option value="">All brands</option>
                            @foreach ($brands as $brand)
                                <option value="{{ $brand->brand_id }}">{{ $brand->brand_name }}</option>
                            @endforeach
                        </select>
                    </span>
                </label>
                <label class="flex items-center gap-3 rounded-xl bg-paper px-4 py-2.5">
                    <x-store.icon name="car" class="size-5 text-race" />
                    <span class="grid flex-1">
                        <span class="text-[11px] font-semibold text-zinc-500">Category</span>
                        <select name="category" class="-ml-1 bg-transparent text-sm font-medium text-ink focus:outline-none">
                            <option value="">All categories</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->category_id }}">{{ $category->category_name }}</option>
                            @endforeach
                        </select>
                    </span>
                </label>
                <label class="flex items-center gap-3 rounded-xl bg-paper px-4 py-2.5">
                    <x-store.icon name="coins" class="size-5 text-race" />
                    <span class="grid flex-1">
                        <span class="text-[11px] font-semibold text-zinc-500">Budget</span>
                        <select name="max_price" class="-ml-1 bg-transparent text-sm font-medium text-ink focus:outline-none">
                            <option value="">Any price</option>
                            @foreach ($priceSteps as $step)
                                <option value="{{ $step }}">Up to {{ CarVisual::baht($step) }}</option>
                            @endforeach
                        </select>
                    </span>
                </label>
                <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-xl bg-race px-8 py-3 text-sm font-semibold text-white hover:bg-race-dark">
                    <x-store.icon name="search" class="size-4" /> Search cars
                </button>
            </form>
        </div>
    </section>

    <div class="road-divider"></div>

    {{-- ===== เลือกตามยี่ห้อ ===== --}}
    <section class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8">
        <x-store.section-title kicker="Your favorite, ready to go" title="Pick your brand" subtitle="Pick a brand to see every car we have from it.">
            <x-slot:action>
                <a href="{{ route('products.index') }}" class="inline-flex items-center gap-1 text-sm font-semibold text-race hover:underline">View all cars <x-store.icon name="arrow-right" class="size-4" /></a>
            </x-slot:action>
        </x-store.section-title>

        <div class="mt-8 grid grid-cols-2 gap-3 sm:grid-cols-[repeat(auto-fit,minmax(140px,1fr))]">
            @foreach ($brands->take(8) as $brand)
                <a href="{{ route('products.index', ['brand' => $brand->brand_id]) }}" class="group rounded-2xl border border-line bg-white px-3 py-5 text-center transition hover:-translate-y-0.5 hover:border-race">
                    <p class="font-display text-base font-bold uppercase tracking-tight text-ink group-hover:text-race">{{ $brand->brand_name }}</p>
                    <p class="mt-1 text-xs text-zinc-500">{{ number_format($brand->cars_count) }} {{ Str::plural('car', $brand->cars_count) }}</p>
                </a>
            @endforeach
        </div>
    </section>

    {{-- ===== เลือกตามประเภท ===== --}}
    <section class="bg-white">
        <div class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8">
            <x-store.section-title kicker="A ride for every side of you" title="What's your drive?" subtitle="Browse by the body type that fits your life." />

            <div class="mt-8 grid grid-cols-2 gap-3 sm:grid-cols-[repeat(auto-fit,minmax(130px,1fr))]">
                @foreach ($categories as $category)
                    <a href="{{ route('products.index', ['category' => $category->category_id]) }}" class="group flex flex-col items-center rounded-2xl border border-line bg-paper px-3 pb-4 pt-3 transition hover:border-race hover:bg-white">
                        <x-store.car-art color="#1B1F24" :type="CarVisual::bodyType($category->category_name)" class="w-24 opacity-80 transition group-hover:opacity-100" />
                        <p class="mt-1 font-display text-sm font-semibold text-ink group-hover:text-race">{{ $category->category_name }}</p>
                        <p class="text-[11px] text-zinc-500">{{ number_format($category->cars_count) }} {{ Str::plural('car', $category->cars_count) }}</p>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ===== รถมาใหม่ ===== --}}
    <section class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8">
        <x-store.section-title kicker="Just in · Don't miss out" title="Fresh on the grid" subtitle="The latest arrivals, ready to view and order today.">
            <x-slot:action>
                <a href="{{ route('products.index') }}" class="inline-flex items-center gap-1 text-sm font-semibold text-race hover:underline">View all <x-store.icon name="arrow-right" class="size-4" /></a>
            </x-slot:action>
        </x-store.section-title>

        <div class="mt-8 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
            @forelse ($latestCars as $car)
                <x-store.car-card :car="$car" />
            @empty
                <p class="col-span-full rounded-2xl border border-dashed border-line bg-white p-10 text-center text-sm text-zinc-500">No cars available yet.</p>
            @endforelse
        </div>
    </section>

    {{-- ===== ทำไมต้อง VELOCE ===== --}}
    <section class="bg-white">
        <div class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8">
            <x-store.section-title kicker="The VELOCE way" title="Good rides. Zero guesswork." subtitle="Buy with confidence at every step." />

            <div class="mt-8 grid gap-4 md:grid-cols-3">
                @foreach ([
                    ['icon' => 'shield-check', 'title' => 'Checked before sale', 'text' => 'Every used car is inspected, with its condition and mileage shown on the details page.'],
                    ['icon' => 'receipt', 'title' => 'Honest pricing', 'text' => 'The price you see is the price you pay. Member discounts are applied automatically at checkout.'],
                    ['icon' => 'coins', 'title' => 'Earn as you drive', 'text' => 'Get 1 point for every ฿1,000 spent. More points, higher tier, bigger discount.'],
                ] as $item)
                    <div class="flex gap-4 rounded-2xl border border-line bg-paper p-5">
                        <span class="flex size-12 shrink-0 items-center justify-center rounded-xl bg-sun text-ink">
                            <x-store.icon :name="$item['icon']" class="size-6" />
                        </span>
                        <div>
                            <p class="font-display text-lg font-semibold text-ink">{{ $item['title'] }}</p>
                            <p class="mt-1 text-sm leading-relaxed text-zinc-600">{{ $item['text'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ===== VELOCE Club (ระดับสมาชิกจากตาราง MEMBERSHIP_TIER) ===== --}}
    <section id="club" class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8">
        <div class="grid gap-8 lg:grid-cols-[1fr_2fr] lg:items-center">
            <div>
                <p class="font-display text-xs font-semibold uppercase italic tracking-[0.18em] text-race">VELOCE Club</p>
                <h2 class="mt-1 font-display text-4xl font-extrabold uppercase italic leading-none text-ink">Join the<br>fast lane.</h2>
                <p class="mt-3 text-sm text-zinc-600">Join free and start at {{ $tiers->first()?->tier_name ?? 'Basic' }}. Every completed order earns points, and your tier upgrades automatically.</p>
                <a href="{{ route('membership.index') }}" class="mt-5 mr-2 inline-flex items-center gap-2 rounded-xl border-2 border-ink px-5 py-2.5 text-sm font-semibold text-ink hover:bg-ink hover:text-white">Membership details</a>
                @guest
                    <a href="{{ route('register') }}" class="mt-5 inline-flex items-center gap-2 rounded-xl bg-race px-5 py-3 text-sm font-semibold text-white hover:bg-race-dark">
                        Join now <x-store.icon name="arrow-right" class="size-4" />
                    </a>
                @endguest
            </div>

            <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                @foreach ($tiers as $tier)
                    @php $top = $loop->last && $tiers->count() > 1; @endphp
                    <div @class(['rounded-2xl border p-5', 'border-sun bg-sun-soft' => $top, 'border-line bg-white' => ! $top])>
                        <div class="flex items-center justify-between">
                            <p class="font-display text-lg font-bold text-ink">{{ $tier->tier_name }}</p>
                            <x-store.icon name="{{ $top ? 'crown' : 'badge-percent' }}" class="size-5 {{ $top ? 'text-ink' : 'text-zinc-400' }}" />
                        </div>
                        <p class="mt-3 font-display text-4xl font-extrabold text-race">{{ rtrim(rtrim(number_format((float) $tier->discount_percent, 2), '0'), '.') }}%</p>
                        <p class="text-xs text-zinc-500">off every order</p>
                        <div class="mt-4 border-t border-line pt-3 text-sm">
                            <span class="text-zinc-500">Min. points</span>
                            <span class="float-right font-semibold text-ink">{{ number_format($tier->min_points) }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endsection
