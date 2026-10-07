@extends('layouts.public')

@section('content')
    @include('commerce.messages')

    <a href="{{ route('products.index') }}"
       class="mb-4 inline-block text-blue-600">
        ← All Cars
    </a>

    <div class="grid gap-8 lg:grid-cols-2">
        {{-- รูปรถ --}}
        <div>
            @if ($car->image_url)
                <img src="{{ $car->image_url }}"
                     alt="{{ $car->model_name }}"
                     class="aspect-video w-full rounded-xl object-cover">
            @else
                <div class="flex aspect-video w-full items-center justify-center rounded-xl bg-zinc-100 text-zinc-400 dark:bg-zinc-800">
                    No image
                </div>
            @endif
        </div>

        {{-- ข้อมูลหลัก ราคา และปุ่มซื้อ --}}
        <div>
            <div class="text-sm text-zinc-500">
                {{ $car->brand?->brand_name ?? '-' }}
                ·
                {{ $car->category?->category_name ?? '-' }}
            </div>

            <h1 class="mt-1 text-3xl font-semibold">
                {{ $car->model_name }}
                <span class="text-zinc-500">({{ $car->model_year }})</span>
            </h1>

            <a href="#reviews" class="mt-2 inline-flex items-center gap-2 text-sm">
                @if ($averageRating !== null)
                    @include('reviews._stars', ['rating' => (int) round($averageRating)])
                    <span>{{ number_format($averageRating, 1) }} / 5</span>
                    <span class="text-zinc-500">({{ $ratedCount }} {{ Str::plural('rating', $ratedCount) }})</span>
                @else
                    <span class="text-zinc-500">No ratings yet</span>
                @endif
            </a>

            <div class="mt-4 text-3xl font-semibold">
                ฿{{ number_format($car->price, 2) }}
            </div>

            @if ($car->stock_qty > 0)
                <div class="mt-1 text-green-600">
                    In stock ({{ $car->stock_qty }} left)
                </div>
            @else
                <div class="mt-1 text-red-600">
                    Out of stock
                </div>
            @endif

            @auth
                @if (auth()->user()->isMember())
                    @if ($car->stock_qty > 0)
                        <form method="POST"
                              action="{{ route('cart.store', $car) }}"
                              class="mt-6 flex flex-wrap items-end gap-3">
                            @csrf

                            <div>
                                <label for="quantity" class="mb-1 block text-sm">
                                    Quantity
                                </label>

                                <input
                                    id="quantity"
                                    type="number"
                                    name="quantity"
                                    min="1"
                                    max="{{ $car->stock_qty }}"
                                    step="1"
                                    value="1"
                                    required
                                    class="w-24 rounded-lg border border-zinc-300 bg-white px-3 py-2 dark:border-zinc-600 dark:bg-zinc-800"
                                >
                            </div>

                            <button type="submit"
                                    class="rounded-lg bg-zinc-900 px-6 py-2 text-white dark:bg-white dark:text-zinc-900">
                                Add to Cart
                            </button>
                        </form>
                    @endif
                @endif

                @if (auth()->user()->isAdmin())
                    <a href="{{ route('admin.cars.edit', $car) }}"
                       class="mt-6 inline-block rounded-lg bg-blue-600 px-5 py-2 text-white">
                        Edit in Admin Panel
                    </a>
                @endif
            @else
                <a href="{{ route('login') }}"
                   class="mt-6 inline-block rounded-lg bg-zinc-900 px-6 py-2 text-white dark:bg-white dark:text-zinc-900">
                    Log in to buy
                </a>
            @endauth

            {{-- สเปกรถ --}}
            <dl class="mt-8 grid grid-cols-2 gap-x-6 gap-y-3 rounded-xl border border-zinc-200 p-5 text-sm dark:border-zinc-700">
                <div>
                    <dt class="text-zinc-500">Brand</dt>
                    <dd class="font-medium">{{ $car->brand?->brand_name ?? '-' }}</dd>
                </div>

                <div>
                    <dt class="text-zinc-500">Category</dt>
                    <dd class="font-medium">{{ $car->category?->category_name ?? '-' }}</dd>
                </div>

                <div>
                    <dt class="text-zinc-500">Model Year</dt>
                    <dd class="font-medium">{{ $car->model_year }}</dd>
                </div>

                <div>
                    <dt class="text-zinc-500">Condition</dt>
                    <dd class="font-medium">{{ $car->car_condition }}</dd>
                </div>

                <div>
                    <dt class="text-zinc-500">Color</dt>
                    <dd class="font-medium">{{ $car->color }}</dd>
                </div>

                <div>
                    <dt class="text-zinc-500">Fuel Type</dt>
                    <dd class="font-medium">{{ $car->fuel_type }}</dd>
                </div>

                <div>
                    <dt class="text-zinc-500">Transmission</dt>
                    <dd class="font-medium">{{ $car->transmission }}</dd>
                </div>

                <div>
                    <dt class="text-zinc-500">Engine</dt>
                    <dd class="font-medium">
                        {{ $car->engine_cc > 0 ? number_format($car->engine_cc).' cc' : 'Electric' }}
                    </dd>
                </div>

                <div>
                    <dt class="text-zinc-500">Mileage</dt>
                    <dd class="font-medium">{{ number_format($car->mileage_km) }} km</dd>
                </div>
            </dl>
        </div>
    </div>

    @if ($car->description)
        <section class="mt-10">
            <h2 class="mb-2 text-xl font-semibold">Description</h2>
            <p class="whitespace-pre-wrap text-zinc-600 dark:text-zinc-300">{{ $car->description }}</p>
        </section>
    @endif

    {{-- รีวิว --}}
    <section id="reviews" class="mt-10">
        <h2 class="mb-1 text-xl font-semibold">
            Reviews ({{ $reviews->count() }})
        </h2>

        @if ($averageRating !== null)
            <p class="mb-4 flex items-center gap-2">
                @include('reviews._stars', ['rating' => (int) round($averageRating)])
                <span class="font-semibold">{{ number_format($averageRating, 1) }} out of 5</span>
                <span class="text-sm text-zinc-500">
                    from {{ $ratedCount }} {{ Str::plural('rating', $ratedCount) }}
                </span>
            </p>
        @endif

        {{-- ฟอร์มเขียนรีวิว: เฉพาะสมาชิกที่มีออเดอร์ completed ของรถคันนี้ --}}
        @auth
            @if (auth()->user()->isMember())
                @if ($reviewsLeft > 0)
                    <form id="write-review"
                          method="POST"
                          action="{{ route('reviews.store', $car) }}"
                          class="my-6 max-w-2xl space-y-4 rounded-xl border border-zinc-200 p-5 dark:border-zinc-700">
                        @csrf

                        <h3 class="font-semibold">Write a review</h3>

                        @include('reviews._form-fields', ['review' => null])

                        <button type="submit"
                                class="rounded-lg bg-blue-600 px-5 py-2 text-white">
                            Post Review
                        </button>
                    </form>
                @else
                    <p class="my-4 text-sm text-zinc-500">
                        You can write a review after an order containing this car is completed
                        (one review per completed order).
                    </p>
                @endif
            @endif
        @else
            <p class="my-4 text-sm text-zinc-500">
                <a href="{{ route('login') }}" class="text-blue-600">Log in</a>
                to review a car you have bought.
            </p>
        @endauth

        @forelse ($reviews as $review)
            <div class="mb-3 rounded-xl border border-zinc-200 p-4 dark:border-zinc-700">
                <div class="flex flex-wrap items-center gap-2 text-sm text-zinc-500">
                    @include('reviews._stars', ['rating' => $review->rating])

                    <span>
                        {{-- แสดงชื่อ + อักษรแรกของนามสกุล ไม่โชว์ชื่อเต็ม --}}
                        {{ $review->member?->first_name }}
                        {{ Str::upper(Str::substr($review->member?->last_name ?? '', 0, 1)) }}.
                        · {{ $review->created_at?->format('d/m/Y') }}
                    </span>
                </div>

                <p class="mt-2 whitespace-pre-wrap">{{ $review->comment }}</p>

                {{-- เจ้าของรีวิวแก้ไข/ลบได้ --}}
                @auth
                    @if ((int) $review->member_id === (int) auth()->id())
                        <div class="mt-3 flex items-center gap-4 text-sm">
                            <a href="{{ route('reviews.edit', $review) }}" class="text-blue-600">
                                Edit
                            </a>

                            <form method="POST"
                                  action="{{ route('reviews.destroy', $review) }}"
                                  data-confirm="Delete your review?"
                                  onsubmit="return confirm(this.dataset.confirm)">
                                @csrf
                                @method('DELETE')

                                <button type="submit" class="text-red-600">
                                    Delete
                                </button>
                            </form>
                        </div>
                    @endif
                @endauth
            </div>
        @empty
            <p class="text-zinc-500">No reviews yet.</p>
        @endforelse
    </section>
@endsection
