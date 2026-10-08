@props(['car'])

@php
    use App\Support\CarVisual;

    $href = Route::has('products.show') ? route('products.show', $car) : route('products.index');
    $soldOut = (int) $car->stock_qty < 1;
@endphp

<article {{ $attributes->merge(['class' => 'group relative flex flex-col overflow-hidden rounded-2xl border border-line bg-white shadow-[0_1px_2px_rgb(27_31_36/0.04)] transition hover:-translate-y-1 hover:border-race hover:shadow-[0_14px_30px_-12px_rgb(27_31_36/0.25)]']) }}>
    <a href="{{ $href }}" class="relative block">
        <x-store.car-photo :car="$car" class="aspect-[4/3] {{ $soldOut ? 'grayscale' : '' }}" />

        <div class="absolute left-3 top-3 flex items-center gap-2">
            <x-store.year-plate :year="$car->model_year" />
            <x-store.condition-badge :condition="$car->car_condition" />
        </div>

        @if ($soldOut)
            <div class="absolute inset-0 flex items-center justify-center bg-white/55">
                <span class="-rotate-6 rounded-lg border-2 border-ink bg-white px-4 py-1.5 font-display text-lg font-bold text-ink">Sold out</span>
            </div>
        @endif
    </a>

    <div class="flex flex-1 flex-col p-4">
        <p class="text-[11px] font-semibold uppercase tracking-wider text-zinc-500">
            {{ $car->brand?->brand_name ?? '-' }} · {{ $car->category?->category_name ?? '-' }}
        </p>
        <h3 class="mt-0.5 font-display text-lg font-semibold uppercase leading-tight text-ink">
            <a href="{{ $href }}" class="hover:text-race">{{ $car->model_name }}</a>
        </h3>

        <dl class="mt-3 flex flex-wrap items-center gap-x-4 gap-y-1.5 whitespace-nowrap border-y border-line py-2.5 text-xs text-zinc-600">
            <div class="flex items-center gap-1.5" title="Mileage">
                <x-store.icon name="gauge" class="size-4 text-zinc-400" />
                <dt class="sr-only">Mileage</dt><dd>{{ number_format($car->mileage_km) }} km</dd>
            </div>
            <div class="flex items-center gap-1.5" title="Fuel">
                <x-store.icon name="fuel" class="size-4 text-zinc-400" />
                <dt class="sr-only">Fuel</dt><dd>{{ $car->fuel_type }}</dd>
            </div>
            <div class="flex items-center gap-1.5" title="Transmission">
                <x-store.icon name="gear" class="size-4 text-zinc-400" />
                <dt class="sr-only">Transmission</dt><dd>{{ $car->transmission === 'Automatic' ? 'Auto' : $car->transmission }}</dd>
            </div>
        </dl>

        <div class="mt-3 flex items-end justify-between gap-2">
            <p class="font-display text-2xl font-bold leading-none text-race">{{ CarVisual::baht($car->price) }}</p>
            <p class="text-xs {{ $soldOut ? 'text-zinc-400' : 'text-zinc-500' }}">
                @if ($soldOut)
                    Out of stock
                @else
                    <span class="mr-1 inline-block size-1.5 rounded-full bg-sun align-middle"></span>{{ $car->stock_qty }} left
                @endif
            </p>
        </div>

        <a href="{{ $href }}" class="mt-4 inline-flex items-center justify-center gap-2 rounded-xl {{ $soldOut ? 'bg-paper text-zinc-500' : 'bg-race text-white hover:bg-race-dark' }} px-4 py-2.5 text-sm font-semibold transition">
            View details
            <x-store.icon name="arrow-right" class="size-4" />
        </a>
    </div>
</article>
