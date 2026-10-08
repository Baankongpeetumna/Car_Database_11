@extends('layouts.public')

@php
    use App\Support\CarVisual;

    $pageTitle = match ($condition) {
        'new' => ['New cars', 'Brand-new cars, ready for delivery.', 'New cars'],
        'used' => ['Used cars', 'Quality-checked used cars with clear mileage and condition.', 'Used cars'],
        default => ['All cars', 'Every new and used car in our showroom.', 'All cars'],
    };

    // สร้างลิงก์ที่ตัดค่าตัวกรองหนึ่งค่าออก (ใช้กับชิปตัวกรอง)
    $without = function (string $key, $value = null) {
        $query = request()->except(['page']);
        if ($value === null || ! is_array($query[$key] ?? null)) {
            unset($query[$key]);
        } else {
            $query[$key] = array_values(array_filter($query[$key], fn ($v) => (string) $v !== (string) $value));
        }

        return route('products.index', $query);
    };

    $chips = [];
    if (filled(request('q'))) $chips[] = ['Search: '.request('q'), $without('q')];
    foreach ($brands->whereIn('brand_id', $selected['brand']) as $b) $chips[] = [$b->brand_name, $without('brand', $b->brand_id)];
    foreach ($categories->whereIn('category_id', $selected['category']) as $c) $chips[] = [$c->category_name, $without('category', $c->category_id)];
    foreach ($selected['fuel'] as $f) $chips[] = [$f, $without('fuel', $f)];
    foreach ($selected['transmission'] as $t) $chips[] = [$t, $without('transmission', $t)];
    if (is_numeric(request('year_from'))) $chips[] = ['From '.request('year_from'), $without('year_from')];
    if (is_numeric(request('year_to'))) $chips[] = ['To '.request('year_to'), $without('year_to')];
    if (is_numeric(request('min_price'))) $chips[] = ['Min '.CarVisual::baht(request('min_price')), $without('min_price')];
    if (is_numeric(request('max_price'))) $chips[] = ['Max '.CarVisual::baht(request('max_price')), $without('max_price')];
    if (is_numeric(request('max_mileage'))) $chips[] = ['Under '.number_format(request('max_mileage')).' km', $without('max_mileage')];
    if (request()->boolean('in_stock')) $chips[] = ['In stock only', $without('in_stock')];

    $tabs = [
        [null, 'All', $conditionCounts['all']],
        ['new', 'New', $conditionCounts['new']],
        ['used', 'Used', $conditionCounts['used']],
    ];
@endphp

@section('bleed')
    {{-- หัวหน้า --}}
    <section class="border-b border-line bg-white">
        <div class="mx-auto max-w-7xl px-4 pb-6 pt-8 sm:px-6 lg:px-8">
            <nav class="text-xs text-zinc-500" aria-label="breadcrumb">
                <a href="{{ route('home') }}" class="hover:text-ink">Home</a> <span class="mx-1">/</span> <span class="text-ink">{{ $pageTitle[2] }}</span>
            </nav>
            <div class="mt-3 flex flex-wrap items-end justify-between gap-4">
                <div>
                    <h1 class="font-display text-4xl font-extrabold uppercase italic leading-none tracking-tight text-ink sm:text-5xl">{{ $pageTitle[0] }}</h1>
                    <p class="mt-2 text-sm text-zinc-500">{{ $pageTitle[1] }}</p>
                </div>
                <p class="flex items-center gap-2 font-display text-sm font-semibold text-ink">
                    <span class="size-2 rounded-full bg-race"></span> {{ number_format($cars->total()) }} {{ Str::plural('car', $cars->total()) }} found
                </p>
            </div>

            {{-- แท็บ รถใหม่ / มือสอง --}}
            <div class="mt-6 flex flex-wrap gap-2">
                @foreach ($tabs as [$value, $label, $count])
                    @php
                        $query = request()->except(['page', 'condition']);
                        if ($value) $query['condition'] = $value;
                        $active = $condition === $value;
                    @endphp
                    <a href="{{ route('products.index', $query) }}" @class([
                        'inline-flex items-center gap-2 rounded-full border px-4 py-2 text-sm font-semibold transition',
                        'border-ink bg-ink text-white' => $active,
                        'border-line bg-white text-ink hover:border-ink' => ! $active,
                    ])>
                        {{ $label }}
                        <span @class(['rounded-full px-2 py-0.5 text-[11px]', 'bg-white/15' => $active, 'bg-paper text-zinc-500' => ! $active])>{{ $count }}</span>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    <div id="search" class="mx-auto grid max-w-7xl gap-8 px-4 py-8 sm:px-6 lg:grid-cols-[272px_1fr] lg:px-8">
        {{-- ===== ตัวกรอง ===== --}}
        <aside>
            <details id="filter-panel" class="group rounded-2xl border border-line bg-white lg:sticky lg:top-24">
                <summary class="flex cursor-pointer list-none items-center justify-between px-5 py-4 lg:cursor-default [&::-webkit-details-marker]:hidden">
                    <span class="flex items-center gap-2 font-display text-lg font-semibold text-ink">
                        <x-store.icon name="sliders" class="size-5 text-race" /> Filters
                        @if (count($chips))
                            <span class="rounded-full bg-race px-2 text-xs text-white">{{ count($chips) }}</span>
                        @endif
                    </span>
                    <x-store.icon name="chevron-down" class="size-5 text-zinc-400 transition group-open:rotate-180 lg:hidden" />
                </summary>

                <form id="filters" method="GET" action="{{ route('products.index') }}" class="space-y-1 border-t border-line px-5 pb-5">
                    @if ($condition)
                        <input type="hidden" name="condition" value="{{ $condition }}">
                    @endif
                    <input type="hidden" name="sort" value="{{ $sort }}">

                    @php
                        $groups = [
                            ['brand', 'Brand', $brands->map(fn ($b) => [$b->brand_id, $b->brand_name, $b->cars_count])],
                            ['category', 'Category', $categories->map(fn ($c) => [$c->category_id, $c->category_name, $c->cars_count])],
                            ['fuel', 'Fuel type', $fuelOptions->map(fn ($f) => [$f, $f, null])],
                            ['transmission', 'Transmission', $transmissionOptions->map(fn ($t) => [$t, $t, null])],
                        ];
                    @endphp

                    @foreach ($groups as [$key, $label, $options])
                        <fieldset class="border-b border-line py-4">
                            <legend class="mb-2 text-sm font-semibold text-ink">{{ $label }}</legend>
                            <div class="space-y-1.5">
                                @foreach ($options as [$value, $name, $count])
                                    <label class="flex cursor-pointer items-center gap-2.5 rounded-lg px-1 py-0.5 text-sm text-zinc-700 hover:bg-paper">
                                        <input type="checkbox" name="{{ $key }}[]" value="{{ $value }}" @checked(in_array((string) $value, array_map('strval', $selected[$key]), true)) class="size-4 rounded border-zinc-300 accent-race">
                                        <span class="flex-1">{{ $name }}</span>
                                        @if ($count !== null)
                                            <span class="text-xs text-zinc-400">{{ $count }}</span>
                                        @endif
                                    </label>
                                @endforeach
                            </div>
                        </fieldset>
                    @endforeach

                    <fieldset class="border-b border-line py-4">
                        <legend class="mb-2 text-sm font-semibold text-ink">Model year</legend>
                        <div class="grid grid-cols-2 gap-2">
                            @foreach (['year_from' => 'From', 'year_to' => 'To'] as $name => $placeholder)
                                <select name="{{ $name }}" class="field !px-3" aria-label="Model year {{ $placeholder }}">
                                    <option value="">{{ $placeholder }}</option>
                                    @for ($y = $yearRange[1]; $y >= $yearRange[0] && $y > 0; $y--)
                                        <option value="{{ $y }}" @selected((string) request($name) === (string) $y)>{{ $y }}</option>
                                    @endfor
                                </select>
                            @endforeach
                        </div>
                    </fieldset>

                    <fieldset class="border-b border-line py-4">
                        <legend class="mb-2 text-sm font-semibold text-ink">Price (฿)</legend>
                        <div class="grid grid-cols-2 gap-2">
                            <input type="number" name="min_price" min="0" step="10000" value="{{ request('min_price') }}" placeholder="Min" class="field !px-3" aria-label="Min price">
                            <input type="number" name="max_price" min="0" step="10000" value="{{ request('max_price') }}" placeholder="Max" class="field !px-3" aria-label="Max price">
                        </div>
                    </fieldset>

                    <fieldset class="border-b border-line py-4">
                        <legend class="mb-2 text-sm font-semibold text-ink">Mileage</legend>
                        <select name="max_mileage" class="field" aria-label="Max mileage">
                            <option value="">Any</option>
                            @foreach ([10000, 30000, 50000, 100000] as $km)
                                <option value="{{ $km }}" @selected((string) request('max_mileage') === (string) $km)>Under {{ number_format($km) }} km</option>
                            @endforeach
                        </select>
                    </fieldset>

                    <label class="flex cursor-pointer items-center gap-2.5 py-4 text-sm text-zinc-700">
                        <input type="checkbox" name="in_stock" value="1" @checked(request()->boolean('in_stock')) class="size-4 accent-race">
                        In stock only
                    </label>

                    <div class="grid gap-2 pt-1">
                        <button type="submit" class="rounded-xl bg-race px-4 py-2.5 text-sm font-semibold text-white hover:bg-race-dark">Apply filters</button>
                        <a href="{{ route('products.index', array_filter(['condition' => $condition])) }}" class="rounded-xl px-4 py-2 text-center text-sm font-medium text-zinc-500 hover:text-race">Reset all filters</a>
                    </div>
                </form>
            </details>
        </aside>

        {{-- ===== รายการรถ ===== --}}
        <div class="min-w-0">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                <label class="relative flex-1">
                    <span class="sr-only">Search model or brand</span>
                    <x-store.icon name="search" class="pointer-events-none absolute left-3.5 top-1/2 size-4 -translate-y-1/2 text-zinc-400" />
                    <input form="filters" type="search" name="q" value="{{ request('q') }}" placeholder="Search model or brand, e.g. Civic, Toyota" class="field !pl-10">
                </label>
                <label class="flex items-center gap-2 text-sm text-zinc-500">
                    <span class="whitespace-nowrap">Sort by</span>
                    <select id="sort-select" class="field !w-auto !py-2 font-medium" aria-label="Sort cars">
                        @foreach ($sorts as $key => [$label])
                            <option value="{{ $key }}" @selected($sort === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </label>
            </div>

            @if (count($chips))
                <div class="mt-4 flex flex-wrap items-center gap-2">
                    @foreach ($chips as [$label, $url])
                        <a href="{{ $url }}" class="inline-flex items-center gap-1.5 rounded-full bg-race-soft px-3 py-1.5 text-xs font-semibold text-race-dark hover:bg-race hover:text-white" aria-label="Remove filter {{ $label }}">
                            {{ $label }} <x-store.icon name="x" class="size-3.5" />
                        </a>
                    @endforeach
                </div>
            @endif

            <div class="mt-6 grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
                @forelse ($cars as $car)
                    <x-store.car-card :car="$car" />
                @empty
                    <div class="col-span-full flex flex-col items-center rounded-2xl border border-dashed border-line bg-white px-6 py-16 text-center">
                        <x-store.car-art color="#C5CAD1" type="sedan" class="w-56 opacity-70" />
                        <p class="mt-4 font-display text-xl font-semibold text-ink">No cars match your filters</p>
                        <p class="mt-1 text-sm text-zinc-500">Try removing a filter or reset them all.</p>
                        <a href="{{ route('products.index') }}" class="mt-5 rounded-xl bg-race px-5 py-2.5 text-sm font-semibold text-white hover:bg-race-dark">Reset filters</a>
                    </div>
                @endforelse
            </div>

            <div class="mt-8">
                {{ $cars->links('partials.store.pagination') }}
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // จอใหญ่: เปิดแผงตัวกรองไว้เสมอ / มือถือ: พับไว้
        (() => {
            const panel = document.getElementById('filter-panel');
            const sync = () => { if (window.matchMedia('(min-width: 1024px)').matches) panel.open = true; };
            sync();
            window.addEventListener('resize', sync);
            panel.querySelector('summary').addEventListener('click', (e) => {
                if (window.matchMedia('(min-width: 1024px)').matches) e.preventDefault();
            });

            // เปลี่ยนการเรียงลำดับแล้วโหลดใหม่ทันที
            document.getElementById('sort-select').addEventListener('change', (e) => {
                const form = document.getElementById('filters');
                form.querySelector('input[name=sort]').value = e.target.value;
                form.submit();
            });
        })();
    </script>
@endpush
