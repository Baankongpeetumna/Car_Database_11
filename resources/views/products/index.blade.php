@extends('layouts.public')

@section('content')
    @php
        $field = 'w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800';
    @endphp

    {{-- ข้อความแจ้งสำเร็จ --}}
    @if (session('success'))
        <div role="status"
             class="mb-4 rounded-lg bg-green-100 p-3 text-sm text-green-800">
            {{ session('success') }}
        </div>
    @endif

    {{-- ข้อความแจ้งข้อผิดพลาด เช่น จำนวนเกินสต็อก --}}
    @if ($errors->any())
        <div role="alert"
             class="mb-4 rounded-lg bg-red-100 p-3 text-sm text-red-800">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    {{-- เมนูสำหรับสมาชิก --}}
    @auth
        @if (auth()->user()->isMember())
            <div class="mb-6 flex flex-wrap gap-3">
                <a href="{{ route('cart.index') }}"
                   class="rounded-lg border border-zinc-300 px-4 py-2 text-sm dark:border-zinc-600">
                    View My Cart
                </a>

                <a href="{{ route('orders.index') }}"
                   class="rounded-lg border border-zinc-300 px-4 py-2 text-sm dark:border-zinc-600">
                    Order History
                </a>
            </div>
        @endif
    @endauth

    <div class="mb-6">
        <h1 class="text-2xl font-semibold">Cars</h1>

        <p class="mt-1 text-sm text-zinc-500">
            {{ $cars->total() }} {{ Str::plural('car', $cars->total()) }} found
        </p>
    </div>

    {{-- ค้นหาและกรองรถ --}}
    <form method="GET"
          action="{{ route('products.index') }}"
          class="mb-8 grid gap-3 rounded-xl border border-zinc-200 p-4 dark:border-zinc-700 sm:grid-cols-2 lg:grid-cols-4">

        <input
            type="text"
            name="q"
            value="{{ request('q') }}"
            placeholder="Search model or brand"
            aria-label="Search model or brand"
            class="{{ $field }} lg:col-span-2"
        >

        <select name="brand"
                aria-label="Brand"
                class="{{ $field }}">
            <option value="">All brands</option>

            @foreach ($brands as $brand)
                <option
                    value="{{ $brand->brand_id }}"
                    @selected((string) request('brand') === (string) $brand->brand_id)
                >
                    {{ $brand->brand_name }}
                </option>
            @endforeach
        </select>

        <select name="category"
                aria-label="Category"
                class="{{ $field }}">
            <option value="">All categories</option>

            @foreach ($categories as $category)
                <option
                    value="{{ $category->category_id }}"
                    @selected((string) request('category') === (string) $category->category_id)
                >
                    {{ $category->category_name }}
                </option>
            @endforeach
        </select>

        <input
            type="number"
            name="min_price"
            min="0"
            step="0.01"
            value="{{ request('min_price') }}"
            placeholder="Min price"
            aria-label="Min price"
            class="{{ $field }}"
        >

        <input
            type="number"
            name="max_price"
            min="0"
            step="0.01"
            value="{{ request('max_price') }}"
            placeholder="Max price"
            aria-label="Max price"
            class="{{ $field }}"
        >

        <select name="sort"
                aria-label="Sort cars"
                class="{{ $field }}">
            <option value="">Newest</option>

            <option value="price_asc"
                    @selected(request('sort') === 'price_asc')>
                Price: Low → High
            </option>

            <option value="price_desc"
                    @selected(request('sort') === 'price_desc')>
                Price: High → Low
            </option>
        </select>

        <div class="flex gap-2">
            <button type="submit"
                    class="rounded-lg bg-zinc-900 px-4 py-2 text-sm text-white dark:bg-white dark:text-zinc-900">
                Search
            </button>

            <a href="{{ route('products.index') }}"
               class="rounded-lg border border-zinc-300 px-4 py-2 text-sm dark:border-zinc-600">
                Reset
            </a>
        </div>
    </form>

    {{-- รายการรถ --}}
    <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
        @forelse ($cars as $car)
            <div class="flex flex-col overflow-hidden rounded-xl border border-zinc-200 dark:border-zinc-700">

                {{-- กดรูปหรือชื่อรุ่นเพื่อดูรายละเอียดรถ --}}
                <a href="{{ route('products.show', $car) }}" tabindex="-1" aria-hidden="true">
                    @if ($car->image_url)
                        <img
                            src="{{ $car->image_url }}"
                            alt="{{ $car->model_name }}"
                            loading="lazy"
                            class="aspect-video w-full object-cover"
                        >
                    @else
                        <div class="flex aspect-video w-full items-center justify-center bg-zinc-100 text-sm text-zinc-400 dark:bg-zinc-800">
                            No image
                        </div>
                    @endif
                </a>

                <div class="flex flex-1 flex-col gap-2 p-4">
                    <div class="text-xs text-zinc-500">
                        {{ $car->brand?->brand_name ?? '-' }}
                        ·
                        {{ $car->category?->category_name ?? '-' }}
                    </div>

                    <h2 class="font-medium">
                        <a href="{{ route('products.show', $car) }}" class="hover:underline">
                            {{ $car->model_name }}
                            ({{ $car->model_year }})
                        </a>
                    </h2>

                    <div class="text-sm text-zinc-500">
                        {{ $car->color }}
                        · {{ $car->fuel_type }}
                        · {{ $car->transmission }}
                    </div>

                    <div class="text-sm text-zinc-500">
                        {{ $car->car_condition }}
                        · {{ number_format($car->mileage_km) }} km
                    </div>

                    <div class="mt-auto pt-2">
                        <div class="text-lg font-semibold">
                            ฿{{ number_format($car->price, 2) }}
                        </div>

                        @if ($car->stock_qty > 0)
                            <div class="text-sm text-green-600">
                                In stock ({{ $car->stock_qty }} left)
                            </div>
                        @else
                            <div class="text-sm text-red-600">
                                Out of stock
                            </div>
                        @endif

                        @auth
                            @if (auth()->user()->isMember())
                                @if ($car->stock_qty > 0)
                                    {{-- เลือกจำนวนและเพิ่มลงตะกร้า --}}
                                    <form method="POST"
                                          action="{{ route('cart.store', $car) }}"
                                          class="mt-3">
                                        @csrf

                                        <label
                                            for="add-quantity-{{ $car->car_id }}"
                                            class="mb-1 block text-sm"
                                        >
                                            Quantity
                                        </label>

                                        <input
                                            id="add-quantity-{{ $car->car_id }}"
                                            type="number"
                                            name="quantity"
                                            min="1"
                                            max="{{ $car->stock_qty }}"
                                            step="1"
                                            value="1"
                                            required
                                            class="mb-2 w-24 rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800"
                                        >

                                        <button
                                            type="submit"
                                            class="w-full rounded-lg bg-zinc-900 px-4 py-2 text-sm text-white dark:bg-white dark:text-zinc-900"
                                        >
                                            Add to Cart
                                        </button>
                                    </form>
                                @else
                                    <button
                                        type="button"
                                        disabled
                                        class="mt-3 w-full rounded-lg bg-zinc-200 px-4 py-2 text-sm text-zinc-500 dark:bg-zinc-700"
                                    >
                                        Out of stock
                                    </button>
                                @endif
                            @endif
                        @else
                            <a href="{{ route('login') }}"
                               class="mt-3 block rounded-lg bg-zinc-900 px-4 py-2 text-center text-sm text-white dark:bg-white dark:text-zinc-900">
                                Log in to buy
                            </a>
                        @endauth
                    </div>
                </div>
            </div>
        @empty
            <p class="col-span-full text-center text-zinc-500">
                No cars match your filters.
            </p>
        @endforelse
    </div>

    <div class="mt-8">
        {{ $cars->links() }}
    </div>
@endsection