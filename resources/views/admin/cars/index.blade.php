<x-layouts::app :title="'Manage Cars'">
    <div class="min-h-full bg-zinc-50 dark:bg-zinc-950">
        <div class="space-y-6 p-2 sm:p-4">

            @include('commerce.messages')

            @php
                $field = 'rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900
                          focus:border-red-500 focus:outline-none focus:ring-1 focus:ring-red-500
                          dark:border-zinc-700 dark:bg-zinc-800 dark:text-white';
                $lowStockThreshold = 3;

                $stockFilter = request('stock');
                $isLowFilter = $stockFilter === 'low';

                // Highlight the stock dropdown while a stock filter is active
                $stockField = $field . ($stockFilter
                    ? ' !border-red-500 !bg-red-50 font-semibold dark:!bg-red-950/30'
                    : '');

                $stockFilterLabels = [
                    'in'  => 'In stock only',
                    'low' => 'Low stock only',
                    'out' => 'Out of stock only',
                ];
            @endphp

            {{-- Header --}}
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
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

                    <flux:text class="mt-1 text-zinc-500">
                        Add, edit and restock the cars in your showroom
                    </flux:text>
                </div>

                <a href="{{ route('admin.cars.create') }}"
                   class="inline-flex items-center justify-center gap-2 rounded-lg
                          bg-red-600 px-5 py-2.5 text-sm font-medium text-white
                          shadow-sm transition hover:bg-red-700">
                    <span class="text-lg leading-none">+</span>
                    Add new car
                </a>
            </div>


            {{-- Active filter banner --}}
            @if ($isLowFilter)
                <div class="flex flex-col gap-3 rounded-xl border border-red-200 bg-red-50 px-5 py-4
                            sm:flex-row sm:items-center sm:justify-between
                            dark:border-red-900/50 dark:bg-red-950/20">
                    <div>
                        <p class="text-sm font-semibold text-red-800 dark:text-red-300">
                            Showing low stock cars only
                        </p>
                        <p class="mt-0.5 text-xs text-red-700/80 dark:text-red-300/70">
                            Fewer than {{ $lowStockThreshold }} left in stock
                            · {{ $cars->total() }} {{ \Illuminate\Support\Str::plural('car', $cars->total()) }}
                            · sorted lowest first
                        </p>
                    </div>

                    <div class="flex items-center gap-2">
                        <a href="{{ route('admin.cars.index') }}"
                           class="rounded-lg border border-red-300 px-4 py-2 text-sm font-medium text-red-800
                                  transition hover:bg-red-100
                                  dark:border-red-800 dark:text-red-300 dark:hover:bg-red-950/40">
                            Show all cars
                        </a>
                        <a href="{{ route('admin.dashboard') }}"
                           class="px-3 py-2 text-sm font-medium text-red-700 hover:underline dark:text-red-300">
                            Back to dashboard
                        </a>
                    </div>
                </div>
            @elseif ($stockFilter && isset($stockFilterLabels[$stockFilter]))
                <div class="flex flex-col gap-3 rounded-xl border border-zinc-200 bg-white px-5 py-4
                            sm:flex-row sm:items-center sm:justify-between
                            dark:border-zinc-800 dark:bg-zinc-900">
                    <p class="text-sm font-semibold text-zinc-800 dark:text-zinc-200">
                        Showing: {{ $stockFilterLabels[$stockFilter] }}
                        <span class="font-normal text-zinc-500">
                            · {{ $cars->total() }} {{ \Illuminate\Support\Str::plural('car', $cars->total()) }}
                        </span>
                    </p>

                    <a href="{{ route('admin.cars.index') }}"
                       class="text-sm font-medium text-red-600 hover:underline">
                        Clear filter
                    </a>
                </div>
            @endif


            {{-- Search and filters --}}
            <div class="rounded-xl border border-zinc-200 bg-white p-4 shadow-sm
                        dark:border-zinc-800 dark:bg-zinc-900">
                <form method="GET"
                      action="{{ route('admin.cars.index') }}"
                      class="flex flex-wrap items-center gap-3">

                    <input
                        type="text"
                        name="q"
                        value="{{ request('q') }}"
                        placeholder="Search model or brand"
                        aria-label="Search model or brand"
                        class="{{ $field }} min-w-[14rem] flex-1"
                    >

                    <select name="brand" aria-label="Brand" class="{{ $field }}">
                        <option value="">All brands</option>
                        @foreach ($brands as $brand)
                            <option value="{{ $brand->brand_id }}"
                                    @selected((string) request('brand') === (string) $brand->brand_id)>
                                {{ $brand->brand_name }}
                            </option>
                        @endforeach
                    </select>

                    <select name="category" aria-label="Category" class="{{ $field }}">
                        <option value="">All categories</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->category_id }}"
                                    @selected((string) request('category') === (string) $category->category_id)>
                                {{ $category->category_name }}
                            </option>
                        @endforeach
                    </select>

                    <select name="stock" aria-label="Stock" class="{{ $stockField }}">
                        <option value="">All stock</option>
                        <option value="in" @selected($stockFilter === 'in')>In stock</option>
                        <option value="low" @selected($stockFilter === 'low')>Low stock (&lt; {{ $lowStockThreshold }})</option>
                        <option value="out" @selected($stockFilter === 'out')>Out of stock</option>
                    </select>

                    <button type="submit"
                            class="rounded-lg bg-red-600 px-5 py-2 text-sm font-medium text-white
                                   shadow-sm transition hover:bg-red-700">
                        Filter
                    </button>

                    <a href="{{ route('admin.cars.index') }}"
                       class="rounded-lg border border-zinc-300 px-4 py-2 text-sm font-medium
                              text-zinc-700 transition hover:bg-zinc-50
                              dark:border-zinc-700 dark:text-zinc-300 dark:hover:bg-zinc-800">
                        Reset
                    </a>
                </form>
            </div>


            {{-- Cars table --}}
            <div class="rounded-xl border border-zinc-200 bg-white shadow-sm
                        dark:border-zinc-800 dark:bg-zinc-900">

                <div class="flex items-center justify-between px-5 py-5">
                    <h2 class="font-display text-xl font-semibold text-zinc-900 dark:text-white">
                        {{ $isLowFilter ? 'Low stock cars' : 'All cars' }}
                    </h2>

                    <p class="text-sm text-zinc-500">
                        {{ $cars->total() }} {{ \Illuminate\Support\Str::plural('car', $cars->total()) }} found
                    </p>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-zinc-100 text-xs text-zinc-500 dark:bg-zinc-700/50 dark:text-zinc-400">
                            <tr>
                                <th class="px-5 py-3 font-medium">Image</th>
                                <th class="px-5 py-3 font-medium">ID</th>
                                <th class="px-5 py-3 font-medium">Model</th>
                                <th class="px-5 py-3 font-medium">Brand</th>
                                <th class="px-5 py-3 font-medium">Category</th>
                                <th class="px-5 py-3 font-medium">Price</th>
                                <th class="px-5 py-3 font-medium">In stock</th>
                                <th class="px-5 py-3 font-medium">
                                    Restock
                                    <span class="block text-[11px] font-normal text-zinc-400">add units to stock</span>
                                </th>
                                <th class="px-5 py-3 text-left font-medium">Actions</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                            @forelse ($cars as $car)
                                @php
                                    // Same image logic as the storefront product pages
                                    $imgSrc = \App\Support\CarVisual::imageSrc($car);
                                @endphp

                                <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-700/30">
                                    <td class="px-5 py-4">
                                        @if ($imgSrc)
                                            <img src="{{ $imgSrc }}"
                                                 alt="{{ $car->model_name }}"
                                                 loading="lazy"
                                                 class="h-12 w-20 rounded-md bg-white object-contain">
                                        @else
                                            <div class="flex h-12 w-20 items-center justify-center rounded-md
                                                        bg-zinc-100 text-xs text-zinc-400 dark:bg-zinc-800">
                                                No image
                                            </div>
                                        @endif
                                    </td>

                                    <td class="px-5 py-4 text-zinc-500">
                                        #{{ $car->car_id }}
                                    </td>

                                    <td class="px-5 py-4">
                                        <p class="font-semibold text-zinc-900 dark:text-white">
                                            {{ $car->model_name }}
                                        </p>
                                        <p class="mt-0.5 text-xs text-zinc-500">
                                            {{ $car->model_year }} · {{ $car->color }} · {{ $car->car_condition }}
                                        </p>
                                    </td>

                                    <td class="px-5 py-4 text-zinc-600 dark:text-zinc-300">
                                        {{ $car->brand?->brand_name ?? '-' }}
                                    </td>

                                    <td class="px-5 py-4 text-zinc-600 dark:text-zinc-300">
                                        {{ $car->category?->category_name ?? '-' }}
                                    </td>

                                    <td class="px-5 py-4 font-semibold text-zinc-900 dark:text-white">
                                        ฿{{ number_format($car->price, 2) }}
                                    </td>

                                    {{-- Current stock (read-only) --}}
                                    <td class="px-5 py-4">
                                        @php
                                            $qty = (int) $car->stock_qty;
                                            $qtyColor = $qty <= 0 || $qty === 1
                                                ? 'text-red-600 dark:text-red-400'
                                                : ($qty < $lowStockThreshold
                                                    ? 'text-amber-600 dark:text-amber-400'
                                                    : 'text-zinc-900 dark:text-white');
                                        @endphp

                                        <div class="flex flex-col items-start gap-1.5">
                                            <span class="text-2xl font-bold leading-none {{ $qtyColor }}">
                                                {{ number_format($qty) }}
                                            </span>

                                            @if ($qty <= 0)
                                                <span class="rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-medium
                                                             text-red-700 dark:bg-red-950/40 dark:text-red-400">
                                                    Out of stock
                                                </span>
                                            @elseif ($qty === 1)
                                                <span class="rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-medium
                                                             text-red-700 dark:bg-red-950/40 dark:text-red-400">
                                                    Only 1 left
                                                </span>
                                            @elseif ($qty < $lowStockThreshold)
                                                <span class="rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-medium
                                                             text-amber-800 dark:bg-amber-950/40 dark:text-amber-300">
                                                    Low stock
                                                </span>
                                            @else
                                                <span class="text-xs text-zinc-500">in stock</span>
                                            @endif
                                        </div>
                                    </td>

                                    {{-- Restock: its own column, visually separate from the stock number --}}
                                    <td class="px-5 py-4">
                                        <form method="POST"
                                              action="{{ route('admin.cars.stock', $car) }}"
                                              class="inline-flex items-stretch overflow-hidden rounded-lg border
                                                     border-zinc-300 bg-white
                                                     focus-within:border-red-500 focus-within:ring-1 focus-within:ring-red-500
                                                     dark:border-zinc-700 dark:bg-zinc-800">
                                            @csrf
                                            @method('PATCH')

                                            <label for="add-stock-{{ $car->car_id }}" class="sr-only">
                                                Units to add to stock for {{ $car->model_name }}
                                            </label>

                                            <span class="flex items-center bg-zinc-100 px-2.5 text-sm font-semibold
                                                         text-zinc-500 dark:bg-zinc-700 dark:text-zinc-300">+</span>

                                            <input id="add-stock-{{ $car->car_id }}"
                                                   type="number" name="amount" min="1" max="100000" step="1" value="1" required
                                                   class="w-14 border-0 bg-transparent px-1 py-1.5 text-center text-sm
                                                          text-zinc-900 focus:outline-none focus:ring-0 dark:text-white">

                                            <button type="submit"
                                                    class="bg-zinc-900 px-3 text-xs font-semibold text-white transition
                                                           hover:bg-red-600 dark:bg-zinc-100 dark:text-zinc-900
                                                           dark:hover:bg-red-500 dark:hover:text-white">
                                                Add
                                            </button>
                                        </form>
                                    </td>

                                    {{-- Actions: icon buttons --}}
                                    <td class="px-5 py-4">
                                        <div class="flex items-center justify-start gap-2">
                                            <a href="{{ route('products.show', $car) }}"
                                               title="View" aria-label="View {{ $car->model_name }}"
                                               class="inline-flex h-9 w-9 items-center justify-center rounded-lg border
                                                      border-sky-200 bg-sky-50 text-sky-600 transition
                                                      hover:bg-sky-500 hover:text-white
                                                      dark:border-sky-900/50 dark:bg-sky-950/30 dark:text-sky-400
                                                      dark:hover:bg-sky-500 dark:hover:text-white">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                     stroke-width="1.5" stroke="currentColor" class="h-5 w-5" aria-hidden="true">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                          d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                          d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                </svg>
                                            </a>

                                            <a href="{{ route('admin.cars.edit', $car) }}"
                                               title="Edit" aria-label="Edit {{ $car->model_name }}"
                                               class="inline-flex h-9 w-9 items-center justify-center rounded-lg border
                                                      border-amber-200 bg-amber-50 text-amber-600 transition
                                                      hover:bg-amber-500 hover:text-white
                                                      dark:border-amber-900/50 dark:bg-amber-950/30 dark:text-amber-400
                                                      dark:hover:bg-amber-500 dark:hover:text-white">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                     stroke-width="1.5" stroke="currentColor" class="h-5 w-5" aria-hidden="true">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                          d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                                                </svg>
                                            </a>

                                            <form method="POST"
                                                  action="{{ route('admin.cars.destroy', $car) }}"
                                                  data-confirm="Delete car {{ $car->model_name }}?"
                                                  onsubmit="return confirm(this.dataset.confirm)">
                                                @csrf
                                                @method('DELETE')

                                                <button type="submit"
                                                        title="Delete" aria-label="Delete {{ $car->model_name }}"
                                                        class="inline-flex h-9 w-9 items-center justify-center rounded-lg border
                                                               border-red-200 bg-red-50 text-red-600 transition
                                                               hover:bg-red-600 hover:text-white
                                                               dark:border-red-900/50 dark:bg-red-950/30 dark:text-red-400
                                                               dark:hover:bg-red-600 dark:hover:text-white">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                         stroke-width="1.5" stroke="currentColor" class="h-5 w-5" aria-hidden="true">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                              d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                                    </svg>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="px-5 py-10 text-center text-zinc-500">
                                        @if ($isLowFilter)
                                            No cars are running low on stock. 🎉
                                            <a href="{{ route('admin.cars.index') }}"
                                               class="ml-1 font-medium text-red-600 hover:underline">
                                                Show all cars
                                            </a>
                                        @else
                                            No cars match these filters. Try resetting them or add a new car.
                                        @endif
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($cars->hasPages())
                    <div class="border-t border-zinc-200 px-5 py-4 dark:border-zinc-700">
                        {{ $cars->links() }}
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-layouts::app>