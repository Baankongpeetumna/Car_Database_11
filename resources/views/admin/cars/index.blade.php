@php
    use Illuminate\Support\Str;

    $lowStockThreshold = 3;
    $meterMax          = 10;   // stock level that fills the meter

    $q            = request('q');
    $brandValue   = request('brand');
    $catValue     = request('category');
    $stockFilter  = request('stock');
    $isLowFilter  = $stockFilter === 'low';
    $total        = $cars->total();
    $hasFilter    = filled($q) || filled($brandValue) || filled($catValue) || filled($stockFilter);
    $indexUrl     = route('admin.cars.index');
    $with         = fn (array $changes) => request()->fullUrlWithQuery($changes + ['page' => null]);

    // Optional counts from the controller. Hidden when not provided.
    // ['all' => 17, 'in' => 14, 'low' => 2, 'out' => 1]
    $stockCounts  = $stockCounts ?? null;

    // Stock filter pills: each has its own color when active
    $stockTabs = [
        ['value' => '',    'label' => 'All stock',    'key' => 'all', 'on' => 'bg-zinc-900 text-white dark:bg-white dark:text-zinc-900', 'dot' => null],
        ['value' => 'in',  'label' => 'In stock',     'key' => 'in',  'on' => 'bg-emerald-600 text-white',                              'dot' => 'bg-emerald-500'],
        ['value' => 'low', 'label' => 'Low stock',    'key' => 'low', 'on' => 'bg-amber-500 text-white',                                'dot' => 'bg-amber-500'],
        ['value' => 'out', 'label' => 'Out of stock', 'key' => 'out', 'on' => 'bg-red-600 text-white',                                  'dot' => 'bg-red-500'],
    ];

    $select = 'h-10 rounded-xl border border-zinc-200 bg-zinc-50 px-3 text-sm text-zinc-900
               focus:border-red-500 focus:outline-none focus:ring-2 focus:ring-red-500/20
               dark:border-zinc-700 dark:bg-zinc-800 dark:text-white';
@endphp

<x-layouts::app :title="'Manage Cars'">
    <div class="min-h-full bg-zinc-50 dark:bg-zinc-950">
        <div class="mx-auto max-w-7xl space-y-5 p-2 sm:p-4">

            @include('commerce.messages')

            {{-- Header: no card, sits directly on the page --}}
            <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <div class="flex items-center gap-2 text-sm text-zinc-400">
                        <a href="{{ route('admin.dashboard') }}" class="hover:text-red-600">Admin</a>
                        <span>/</span>
                        <span class="text-zinc-600 dark:text-zinc-300">Cars</span>
                    </div>

                    <h1 class="font-display mt-1 text-4xl font-black uppercase italic leading-none tracking-tight
                               text-zinc-900 sm:text-5xl dark:text-white">
                        Manage Cars
                    </h1>
                    <div class="mt-2 h-1 w-14 -skew-x-12 bg-red-600"></div>

                    <p class="mt-2 text-sm text-zinc-500">Add, edit and restock the cars in your showroom</p>

                    {{-- Quick stats --}}
                    <div class="mt-3 flex flex-wrap items-center gap-2">
                        <span class="rounded-full border border-zinc-200 bg-white px-3 py-1 text-xs text-zinc-500 dark:border-zinc-800 dark:bg-zinc-900">
                            <span class="font-semibold text-zinc-900 dark:text-white">{{ number_format($stockCounts['all'] ?? $total) }}</span> cars
                        </span>

                        @if (($stockCounts['low'] ?? 0) > 0)
                            <a href="{{ $with(['stock' => 'low']) }}"
                               class="inline-flex items-center gap-1.5 rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-800
                                      transition hover:bg-amber-200 dark:bg-amber-950/60 dark:text-amber-300">
                                <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>{{ $stockCounts['low'] }} low stock
                            </a>
                        @endif

                        @if (($stockCounts['out'] ?? 0) > 0)
                            <a href="{{ $with(['stock' => 'out']) }}"
                               class="inline-flex items-center gap-1.5 rounded-full bg-red-100 px-3 py-1 text-xs font-semibold text-red-700
                                      transition hover:bg-red-200 dark:bg-red-950/60 dark:text-red-300">
                                <span class="h-1.5 w-1.5 rounded-full bg-red-500"></span>{{ $stockCounts['out'] }} out of stock
                            </a>
                        @endif
                    </div>
                </div>

                <a href="{{ route('admin.cars.create') }}"
                   class="inline-flex items-center justify-center gap-2 rounded-xl bg-red-600 px-5 py-2.5 text-sm font-semibold text-white
                          shadow-md shadow-red-600/30 transition hover:bg-red-500 focus:outline-none focus:ring-2 focus:ring-red-500/40">
                    <span class="text-lg leading-none">+</span>
                    Add new car
                </a>
            </div>


            {{-- One toolbar: search + brand + category on top, stock pills below --}}
            <div class="rounded-2xl border border-zinc-200 bg-white p-3 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">

                <form method="GET" action="{{ $indexUrl }}" class="flex flex-col gap-3 lg:flex-row lg:items-center">
                    @if ($stockFilter) <input type="hidden" name="stock" value="{{ $stockFilter }}"> @endif

                    <div class="relative flex-1">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                             stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"
                             class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-zinc-400">
                            <circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>
                        </svg>
                        <input type="text" name="q" value="{{ $q }}" placeholder="Search model or brand" aria-label="Search model or brand"
                               class="h-10 w-full rounded-xl border border-zinc-200 bg-zinc-50 pl-10 pr-3 text-sm text-zinc-900
                                      placeholder:text-zinc-400 focus:border-red-500 focus:outline-none focus:ring-2 focus:ring-red-500/20
                                      dark:border-zinc-700 dark:bg-zinc-800 dark:text-white">
                    </div>

                    <select name="brand" aria-label="Brand" class="{{ $select }}" onchange="this.form.submit()">
                        <option value="">All brands</option>
                        @foreach ($brands as $brand)
                            <option value="{{ $brand->brand_id }}" @selected((string) $brandValue === (string) $brand->brand_id)>
                                {{ $brand->brand_name }}
                            </option>
                        @endforeach
                    </select>

                    <select name="category" aria-label="Category" class="{{ $select }}" onchange="this.form.submit()">
                        <option value="">All categories</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->category_id }}" @selected((string) $catValue === (string) $category->category_id)>
                                {{ $category->category_name }}
                            </option>
                        @endforeach
                    </select>

                    <button type="submit"
                            class="h-10 rounded-xl bg-red-600 px-5 text-sm font-semibold text-white transition hover:bg-red-500
                                   focus:outline-none focus:ring-2 focus:ring-red-500/40">
                        Search
                    </button>
                </form>

                <div class="mt-3 flex flex-wrap items-center gap-2 border-t border-zinc-100 pt-3 dark:border-zinc-800">
                    <span class="mr-1 text-xs font-medium text-zinc-500">Stock</span>

                    @foreach ($stockTabs as $tab)
                        @php
                            $on  = (string) $stockFilter === $tab['value'];
                            $cnt = $stockCounts[$tab['key']] ?? null;
                        @endphp
                        <a href="{{ $with(['stock' => $tab['value'] ?: null]) }}"
                           class="inline-flex items-center gap-1.5 rounded-full border px-3 py-1 text-xs font-semibold transition
                                  {{ $on
                                        ? $tab['on'].' border-transparent shadow-md'
                                        : 'border-zinc-200 text-zinc-700 hover:border-zinc-400 dark:border-zinc-700 dark:text-zinc-300' }}">
                            @if ($tab['dot'])
                                <span class="h-2 w-2 rounded-full {{ $on ? 'bg-white' : $tab['dot'] }}"></span>
                            @endif
                            {{ $tab['label'] }}
                            @if ($cnt !== null)
                                <span class="{{ $on ? 'opacity-80' : 'text-zinc-400' }}">{{ $cnt }}</span>
                            @endif
                        </a>
                    @endforeach

                    @if ($hasFilter)
                        <div class="ml-auto flex items-center gap-3 text-xs text-zinc-500">
                            <span>
                                {{ number_format($total) }} {{ Str::plural('result', $total) }}
                                @if ($isLowFilter) · fewer than {{ $lowStockThreshold }} left, lowest first @endif
                            </span>
                            <a href="{{ $indexUrl }}" class="font-semibold text-red-600 hover:text-red-700">Clear all</a>
                        </div>
                    @endif
                </div>
            </div>


            {{-- Cars table --}}
            <div class="overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[960px] text-left text-sm">
                        <thead>
                            <tr class="border-b border-zinc-200 text-xs font-medium text-zinc-500 dark:border-zinc-800">
                                <th class="px-5 py-3.5">Image</th>
                                <th class="px-5 py-3.5">ID</th>
                                <th class="px-5 py-3.5">Model</th>
                                <th class="px-5 py-3.5">Brand</th>
                                <th class="px-5 py-3.5">Category</th>
                                <th class="px-5 py-3.5">Price</th>
                                <th class="px-5 py-3.5">In stock</th>
                                <th class="px-5 py-3.5">
                                    Restock
                                    <span class="block text-[11px] font-normal text-zinc-400">add units to stock</span>
                                </th>
                                <th class="px-5 py-3.5">Actions</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                            @forelse ($cars as $car)
                                @php
                                    // Same image logic as the storefront product pages
                                    $imgSrc = \App\Support\CarVisual::imageSrc($car);

                                    $qty = (int) $car->stock_qty;
                                    if ($qty <= 1) {
                                        $qtyColor = 'text-red-600 dark:text-red-400';
                                        $barColor = 'bg-red-500';
                                    } elseif ($qty < $lowStockThreshold) {
                                        $qtyColor = 'text-amber-600 dark:text-amber-400';
                                        $barColor = 'bg-amber-500';
                                    } else {
                                        $qtyColor = 'text-zinc-900 dark:text-white';
                                        $barColor = 'bg-emerald-500';
                                    }
                                    $pct = max(0, min(100, (int) round($qty / $meterMax * 100)));
                                @endphp

                                <tr class="transition hover:bg-zinc-50 dark:hover:bg-zinc-800/40">
                                    <td class="px-5 py-4">
                                        @if ($imgSrc)
                                            <img src="{{ $imgSrc }}" alt="{{ $car->model_name }}" loading="lazy"
                                                 class="h-14 w-24 rounded-xl bg-white object-contain p-1 ring-1 ring-zinc-200 dark:ring-zinc-700">
                                        @else
                                            <div class="flex h-14 w-24 items-center justify-center rounded-xl bg-zinc-100 text-xs text-zinc-400 dark:bg-zinc-800">
                                                No image
                                            </div>
                                        @endif
                                    </td>

                                    <td class="px-5 py-4 text-zinc-400">#{{ $car->car_id }}</td>

                                    <td class="px-5 py-4">
                                        <p class="font-semibold text-zinc-900 dark:text-white">{{ $car->model_name }}</p>
                                        <p class="mt-1 flex flex-wrap items-center gap-1.5 text-xs text-zinc-500">
                                            <span>{{ $car->model_year }}</span>
                                            <span class="text-zinc-300 dark:text-zinc-600">·</span>
                                            <span>{{ $car->color }}</span>
                                            <span class="rounded-full px-2 py-0.5 text-[10px] font-semibold
                                                         {{ Str::lower((string) $car->car_condition) === 'new'
                                                                ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300'
                                                                : 'bg-zinc-100 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400' }}">
                                                {{ $car->car_condition }}
                                            </span>
                                        </p>
                                    </td>

                                    <td class="px-5 py-4 text-zinc-700 dark:text-zinc-300">{{ $car->brand?->brand_name ?? '-' }}</td>

                                    <td class="px-5 py-4">
                                        @if ($car->category?->category_name)
                                            <span class="rounded-full bg-sky-100 px-2.5 py-1 text-xs font-semibold text-sky-700 dark:bg-sky-950/60 dark:text-sky-300">
                                                {{ $car->category->category_name }}
                                            </span>
                                        @else
                                            <span class="text-zinc-400">-</span>
                                        @endif
                                    </td>

                                    <td class="px-5 py-4 font-semibold tabular-nums text-zinc-900 dark:text-white">
                                        ฿{{ number_format($car->price, 2) }}
                                    </td>

                                    {{-- Current stock (read-only) with a small meter --}}
                                    <td class="px-5 py-4">
                                        <div class="w-28">
                                            <div class="flex items-baseline justify-between gap-2">
                                                <span class="text-2xl font-bold leading-none tabular-nums {{ $qtyColor }}">{{ number_format($qty) }}</span>

                                                @if ($qty <= 0)
                                                    <span class="rounded-full bg-red-100 px-2 py-0.5 text-[10px] font-semibold text-red-700 dark:bg-red-950/60 dark:text-red-300">Out of stock</span>
                                                @elseif ($qty === 1)
                                                    <span class="rounded-full bg-red-100 px-2 py-0.5 text-[10px] font-semibold text-red-700 dark:bg-red-950/60 dark:text-red-300">Only 1 left</span>
                                                @elseif ($qty < $lowStockThreshold)
                                                    <span class="rounded-full bg-amber-100 px-2 py-0.5 text-[10px] font-semibold text-amber-800 dark:bg-amber-950/60 dark:text-amber-300">Low stock</span>
                                                @else
                                                    <span class="text-[11px] text-zinc-500">in stock</span>
                                                @endif
                                            </div>

                                            <div class="mt-2 h-1.5 overflow-hidden rounded-full bg-zinc-100 dark:bg-zinc-800">
                                                <div class="h-full rounded-full {{ $barColor }}" style="width: {{ $pct }}%"></div>
                                            </div>
                                        </div>
                                    </td>

                                    {{-- Restock --}}
                                    <td class="px-5 py-4">
                                        <form method="POST" action="{{ route('admin.cars.stock', $car) }}"
                                              class="inline-flex items-stretch overflow-hidden rounded-xl border border-zinc-200 bg-zinc-50
                                                     focus-within:border-red-500 focus-within:ring-2 focus-within:ring-red-500/20
                                                     dark:border-zinc-700 dark:bg-zinc-800">
                                            @csrf
                                            @method('PATCH')

                                            <label for="add-stock-{{ $car->car_id }}" class="sr-only">
                                                Units to add to stock for {{ $car->model_name }}
                                            </label>

                                            <span class="flex items-center px-2.5 text-sm font-semibold text-zinc-400">+</span>

                                            <input id="add-stock-{{ $car->car_id }}" type="number" name="amount" min="1" max="100000" step="1" value="1" required
                                                   class="w-12 border-0 bg-transparent px-1 py-2 text-center text-sm text-zinc-900
                                                          focus:outline-none focus:ring-0 dark:text-white">

                                            <button type="submit"
                                                    class="bg-red-600 px-3.5 text-xs font-semibold text-white transition hover:bg-red-500">
                                                Add
                                            </button>
                                        </form>
                                    </td>

                                    {{-- Actions --}}
                                    <td class="px-5 py-4">
                                        <div class="flex items-center gap-2">
                                            <a href="{{ route('products.show', $car) }}" title="View" aria-label="View {{ $car->model_name }}"
                                               class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-sky-200 bg-sky-50 text-sky-600 transition
                                                      hover:bg-sky-500 hover:text-white
                                                      dark:border-sky-900/50 dark:bg-sky-950/30 dark:text-sky-400 dark:hover:bg-sky-500 dark:hover:text-white">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5" aria-hidden="true">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                </svg>
                                            </a>

                                            <a href="{{ route('admin.cars.edit', $car) }}" title="Edit" aria-label="Edit {{ $car->model_name }}"
                                               class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-amber-200 bg-amber-50 text-amber-600 transition
                                                      hover:bg-amber-500 hover:text-white
                                                      dark:border-amber-900/50 dark:bg-amber-950/30 dark:text-amber-400 dark:hover:bg-amber-500 dark:hover:text-white">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5" aria-hidden="true">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                                                </svg>
                                            </a>

                                            <form method="POST" action="{{ route('admin.cars.destroy', $car) }}"
                                                  data-confirm="Delete car {{ $car->model_name }}?"
>
                                                @csrf
                                                @method('DELETE')

                                                <button type="submit" title="Delete" aria-label="Delete {{ $car->model_name }}"
                                                        class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-red-200 bg-red-50 text-red-600 transition
                                                               hover:bg-red-600 hover:text-white
                                                               dark:border-red-900/50 dark:bg-red-950/30 dark:text-red-400 dark:hover:bg-red-600 dark:hover:text-white">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5" aria-hidden="true">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                                    </svg>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="px-6 py-16 text-center">
                                        <p class="text-lg font-semibold text-zinc-900 dark:text-white">
                                            {{ $isLowFilter ? 'No cars are running low on stock 🎉' : 'No cars match these filters' }}
                                        </p>
                                        <p class="mt-1 text-sm text-zinc-500">
                                            {{ $isLowFilter ? 'Everything in the showroom is well stocked.' : 'Try a different search, or clear the filters to see everything.' }}
                                        </p>
                                        <a href="{{ $indexUrl }}"
                                           class="mt-5 inline-flex items-center justify-center rounded-xl bg-red-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-red-500">
                                            Show all cars
                                        </a>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if ($cars->hasPages())
                <div class="pt-1">
                    {{ $cars->withQueryString()->links() }}
                </div>
            @endif

        </div>
    </div>
</x-layouts::app>