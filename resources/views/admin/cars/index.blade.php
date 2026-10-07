<x-layouts::app :title="'Manage Cars'">
    @include('commerce.messages')

    @php
        $field = 'rounded-lg border border-zinc-300 bg-white p-2 dark:border-zinc-600 dark:bg-zinc-800';
    @endphp

    <h1 class="mb-4 text-2xl font-semibold">
        Admin · Manage Cars
    </h1>

    <a href="{{ route('admin.dashboard') }}" class="text-blue-600">
        ← Admin Dashboard
    </a>

    <div class="my-5 flex flex-wrap items-start justify-between gap-3">
        {{-- ค้นหาและกรองรถ --}}
        <form method="GET"
              action="{{ route('admin.cars.index') }}"
              class="flex flex-wrap gap-3">
            <input
                type="text"
                name="q"
                value="{{ request('q') }}"
                placeholder="Search model or brand"
                aria-label="Search model or brand"
                class="{{ $field }}"
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

            <select name="stock" aria-label="Stock" class="{{ $field }}">
                <option value="">All stock</option>
                <option value="in" @selected(request('stock') === 'in')>In stock</option>
                <option value="out" @selected(request('stock') === 'out')>Out of stock</option>
            </select>

            <button type="submit"
                    class="rounded-lg bg-blue-600 px-4 py-2 text-white">
                Filter
            </button>

            <a href="{{ route('admin.cars.index') }}"
               class="rounded-lg border border-zinc-300 px-4 py-2 dark:border-zinc-600">
                Reset
            </a>
        </form>

        <a href="{{ route('admin.cars.create') }}"
           class="inline-block rounded-lg bg-blue-600 px-4 py-2 text-white">
            + Add Car
        </a>
    </div>

    <p class="mb-2 text-sm text-zinc-500">
        {{ $cars->total() }} {{ Str::plural('car', $cars->total()) }} found
    </p>

    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead>
                <tr>
                    <th class="p-3">Image</th>
                    <th class="p-3">ID</th>
                    <th class="p-3">Model</th>
                    <th class="p-3">Brand</th>
                    <th class="p-3">Category</th>
                    <th class="p-3">Price</th>
                    <th class="p-3">Stock</th>
                    <th class="p-3">Actions</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($cars as $car)
                    <tr class="border-t border-zinc-200 dark:border-zinc-700">
                        <td class="p-3">
                            @if ($car->image_url)
                                <img src="{{ $car->image_url }}"
                                     alt="{{ $car->model_name }}"
                                     loading="lazy"
                                     class="h-12 w-20 rounded object-cover">
                            @else
                                <div class="flex h-12 w-20 items-center justify-center rounded bg-zinc-100 text-xs text-zinc-400 dark:bg-zinc-800">
                                    No image
                                </div>
                            @endif
                        </td>

                        <td class="p-3">
                            #{{ $car->car_id }}
                        </td>

                        <td class="p-3">
                            {{ $car->model_name }}
                            <br>
                            <span class="text-zinc-500">
                                {{ $car->model_year }} · {{ $car->color }} · {{ $car->car_condition }}
                            </span>
                        </td>

                        <td class="p-3">
                            {{ $car->brand?->brand_name ?? '-' }}
                        </td>

                        <td class="p-3">
                            {{ $car->category?->category_name ?? '-' }}
                        </td>

                        <td class="p-3">
                            ฿{{ number_format($car->price, 2) }}
                        </td>

                        <td class="p-3">
                            @if ($car->stock_qty > 0)
                                {{ number_format($car->stock_qty) }}
                            @else
                                <span class="font-semibold text-red-600">0 (Out of stock)</span>
                            @endif

                            {{-- เติม stock เร็วๆ โดยไม่ต้องเข้าหน้า Edit --}}
                            <form method="POST"
                                  action="{{ route('admin.cars.stock', $car) }}"
                                  class="mt-2 flex items-center gap-1">
                                @csrf
                                @method('PATCH')

                                <label for="add-stock-{{ $car->car_id }}" class="sr-only">
                                    Stock to add for {{ $car->model_name }}
                                </label>

                                <input id="add-stock-{{ $car->car_id }}"
                                       type="number" name="amount" min="1" max="100000" step="1" value="1" required
                                       class="w-16 rounded border border-zinc-300 bg-white px-2 py-1 dark:border-zinc-600 dark:bg-zinc-800">

                                <button type="submit"
                                        class="rounded bg-green-600 px-2 py-1 text-xs text-white">
                                    + Add
                                </button>
                            </form>
                        </td>

                        <td class="p-3">
                            <div class="flex items-center gap-4">
                                <a href="{{ route('products.show', $car) }}"
                                   class="text-zinc-600 dark:text-zinc-300">
                                    View
                                </a>

                                <a href="{{ route('admin.cars.edit', $car) }}"
                                   class="text-blue-600">
                                    Edit
                                </a>

                                <form method="POST"
                                      action="{{ route('admin.cars.destroy', $car) }}"
                                      data-confirm="Delete car {{ $car->model_name }}?"
                                      onsubmit="return confirm(this.dataset.confirm)">
                                    @csrf
                                    @method('DELETE')

                                    <button type="submit" class="text-red-600">
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8"
                            class="p-6 text-center text-zinc-500">
                            No cars found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">
        {{ $cars->links() }}
    </div>
</x-layouts::app>
