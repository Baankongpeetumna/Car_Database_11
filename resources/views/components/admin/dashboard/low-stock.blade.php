@props(['threshold' => 3, 'limit' => 3])

@php
    // Cars with fewer than $threshold units left (lowest stock first)
    $query = \App\Models\Car::where('stock_qty', '<', $threshold);
    $total = (clone $query)->count();
    $cars  = (clone $query)->with(['brand', 'category'])
        ->orderBy('stock_qty')->orderBy('car_id')->limit($limit)->get();
@endphp

<div {{ $attributes->merge(['class' => 'rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900']) }}>

    <div class="flex items-start justify-between px-5 py-5">
        <div>
            <x-admin.dashboard.section-title title="Low stock cars" />
            <p class="mt-2 text-sm text-zinc-500">
                Fewer than {{ $threshold }} left in stock
            </p>
        </div>

        @if ($total > 0)
            <a href="{{ route('admin.cars.index', ['stock' => 'low']) }}"
               class="pt-1 text-sm font-semibold text-red-600 hover:text-red-700">
                View all ({{ $total }}) →
            </a>
        @endif
    </div>

    @if ($cars->isEmpty())
        <p class="px-5 pb-10 pt-4 text-center text-sm text-zinc-500">
            All cars are well stocked.
        </p>
    @else
        <div class="grid grid-cols-1 gap-3 px-5 pb-5 sm:grid-cols-3">
            @foreach ($cars as $car)
                @php
                    // Same image logic as the storefront product pages
                    $imgSrc  = \App\Support\CarVisual::imageSrc($car);
                    $carName = trim(($car->brand?->brand_name ?? '') . ' ' . $car->model_name . ' ' . $car->model_year);
                    $editUrl = \Illuminate\Support\Facades\Route::has('admin.cars.edit')
                        ? route('admin.cars.edit', $car->getKey())
                        : route('admin.cars.index');
                @endphp

                <div class="flex min-w-0 flex-col overflow-hidden rounded-lg border border-zinc-200
                            bg-white transition hover:border-red-500
                            dark:border-zinc-700 dark:bg-zinc-800">

                    {{-- Photo (shows "No image" if missing or the file fails to load) --}}
                    <div class="relative aspect-[4/3] w-full bg-white">
                        <div class="absolute inset-0 flex items-center justify-center bg-zinc-100
                                    text-[11px] text-zinc-400 dark:bg-zinc-900">
                            No image
                        </div>

                        @if ($imgSrc)
                            <img src="{{ $imgSrc }}" alt="{{ $carName }}"
                                 loading="lazy"
                                 onerror="this.remove()"
                                 class="absolute inset-0 h-full w-full bg-white object-contain object-center">
                        @endif

                        @if ($car->stock_qty <= 0)
                            <span class="absolute right-2 top-2 rounded-full bg-red-100 px-2 py-0.5 text-[11px] font-medium text-red-700 shadow-sm">
                                Out of stock
                            </span>
                        @else
                            <span class="absolute right-2 top-2 rounded-full bg-amber-100 px-2 py-0.5 text-[11px] font-medium text-amber-800 shadow-sm">
                                {{ $car->stock_qty }} left
                            </span>
                        @endif
                    </div>

                    <div class="flex flex-1 flex-col p-3">
                        <p class="truncate text-xs font-semibold uppercase text-zinc-900 dark:text-white"
                           title="{{ $carName }}">
                            {{ $carName }}
                        </p>
                        <p class="mt-0.5 truncate text-[11px] text-zinc-500">
                            ฿{{ number_format($car->price, 0) }}
                            @if ($car->category?->category_name)
                                · {{ $car->category->category_name }}
                            @endif
                        </p>

                        <a href="{{ $editUrl }}"
                           class="mt-3 flex w-full items-center justify-center rounded-lg border
                                  border-red-200 bg-red-50 px-3 py-2 text-sm font-medium text-red-600
                                  transition hover:bg-red-600 hover:text-white
                                  dark:border-red-900/50 dark:bg-red-950/30 dark:text-red-400
                                  dark:hover:bg-red-600 dark:hover:text-white">
                            Edit
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    @if ($total > $cars->count())
        <div class="border-t border-zinc-200 px-5 py-3 text-xs text-zinc-500 dark:border-zinc-700">
            Showing {{ $cars->count() }} of {{ $total }} low stock cars
        </div>
    @endif
</div>
