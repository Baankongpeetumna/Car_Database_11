@php
    // ---- Low stock: cars with fewer than N units left ----
    $lowStockThreshold = 3;
    $lowStockQuery = \App\Models\Car::where('stock_qty', '<', $lowStockThreshold);
    $lowStockTotal = (clone $lowStockQuery)->count();
    $lowStockCars = (clone $lowStockQuery)->orderBy('stock_qty')->orderBy('car_id')->limit(3)->get();

    // ---- Revenue: only orders with these statuses count as revenue ----
    $revenueStatuses = ['processing', 'completed'];
    $revenueBase = \App\Models\Order::whereIn('status', $revenueStatuses);
    $now = now();

    $periods = [
        'Today'      => [$now->copy()->startOfDay(),   $now->copy()->endOfDay()],
        'This month' => [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()],
        'This year'  => [$now->copy()->startOfYear(),  $now->copy()->endOfYear()],
    ];

    $revenueRows = [];
    foreach ($periods as $label => $range) {
        $q = (clone $revenueBase)->whereBetween('order_date', $range);
        $revenueRows[] = [
            'label'  => $label,
            'amount' => (float) (clone $q)->sum('total_amount'),
            'orders' => (clone $q)->count(),
        ];
    }
@endphp
<x-layouts::app :title="'Admin Dashboard'">
    <div class="min-h-full bg-zinc-50 dark:bg-zinc-950">
        <div class="space-y-6 p-2 sm:p-4">

            {{-- Header --}}
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <div class="flex items-center gap-2 text-sm text-zinc-400">
                        <span>Admin</span>
                        <span>/</span>
                        <span class="text-zinc-600 dark:text-zinc-300">Dashboard</span>
                    </div>

                    <flux:heading size="xl" level="1" class="font-display mt-2 font-semibold">
                        Dashboard
                    </flux:heading>

                    <flux:text class="mt-1 text-zinc-500">
                        Overview of cars, orders and members at VELOCE
                    </flux:text>
                </div>

                <a
                    href="{{ route('admin.cars.index') }}"
                    class="inline-flex items-center justify-center gap-2 rounded-lg
                           bg-red-600 px-5 py-2.5 text-sm font-medium text-white
                           shadow-sm transition hover:bg-red-700"
                >
                    <span class="text-lg leading-none">+</span>
                    Add new car
                </a>
            </div>


            {{-- Statistics --}}
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">

                {{-- Cars --}}
                <div class="rounded-xl border border-zinc-200 bg-white p-5 shadow-sm
                            dark:border-zinc-800 dark:bg-zinc-900">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="font-display text-sm font-semibold text-zinc-900 dark:text-white">Total cars</p>
                            <p class="mt-2 text-3xl font-semibold text-zinc-900 dark:text-white">{{ $carsCount }}</p>
                            <p class="mt-1 text-xs text-zinc-400">cars in the system</p>
                            <p class="mt-1 text-xs text-zinc-400">{{ $brandsCount }} brands · {{ $categoriesCount }} categories</p>
                        </div>

                        <div class="rounded-lg bg-red-50 p-2.5 text-red-600 dark:bg-red-950/40">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-car-front preview-icon"><path d="m21 8-2 2-1.5-3.7A2 2 0 0 0 15.646 5H8.4a2 2 0 0 0-1.903 1.257L5 10 3 8"/><path d="M7 14h.01"/><path d="M17 14h.01"/><rect width="18" height="8" x="3" y="10" rx="2"/><path d="M5 18v2"/><path d="M19 18v2"/></svg>
                        </div>
                    </div>
                </div>


                {{-- Orders --}}
                <div class="rounded-xl border border-zinc-200 bg-white p-5 shadow-sm
                            dark:border-zinc-800 dark:bg-zinc-900">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="font-display text-sm font-semibold text-zinc-900 dark:text-white">Pending orders</p>
                            <p class="mt-2 text-3xl font-semibold text-zinc-900 dark:text-white">{{ $pendingOrdersCount }}</p>
                            <p class="mt-1 text-xs text-zinc-400">orders</p>
                            <p class="mt-1 text-xs text-zinc-400">awaiting review and car preparation</p>
                        </div>

                        <div class="rounded-lg bg-blue-50 p-2.5 text-blue-600 dark:bg-blue-950/40">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-clipboard-list preview-icon"><rect width="8" height="4" x="8" y="2" rx="1" ry="1"/><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><path d="M12 11h4"/><path d="M12 16h4"/><path d="M8 11h.01"/><path d="M8 16h.01"/></svg>
                        </div>
                    </div>
                </div>


                {{-- Members --}}
                <div class="rounded-xl border border-zinc-200 bg-white p-5 shadow-sm
                            dark:border-zinc-800 dark:bg-zinc-900">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="font-display text-sm font-semibold text-zinc-900 dark:text-white">Total members</p>
                            <p class="mt-2 text-3xl font-semibold text-zinc-900 dark:text-white">{{ $membersCount }}</p>
                            <p class="mt-1 text-xs text-zinc-400">members</p>
                            <p class="mt-1 text-xs text-zinc-400">Basic · Silver · Gold · Platinum</p>
                        </div>

                        <div class="rounded-lg bg-purple-50 p-2.5 text-purple-600 dark:bg-purple-950/40">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-user-group preview-icon"><path d="M17 21v-1a2 2 0 00-2-2H9a2 2 0 00-2 2v1"/><path d="M19 10h1a2 2 0 012 2v1"/><path d="M5 10H4a2 2 0 00-2 2v1"/><circle cx="12" cy="11" r="3"/><circle cx="18" cy="4" r="2"/><circle cx="6" cy="4" r="2"/></svg>
                        </div>
                    </div>
                </div>


                {{-- Reviews --}}
                <div class="rounded-xl border border-zinc-200 bg-white p-5 shadow-sm
                            dark:border-zinc-800 dark:bg-zinc-900">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="font-display text-sm font-semibold text-zinc-900 dark:text-white">Reviews</p>
                            <p class="mt-2 text-3xl font-semibold text-zinc-900 dark:text-white">{{ $averageRating ?? 0 }}</p>
                            <p class="mt-1 text-xs text-zinc-400">average rating</p>
                            <p class="mt-1 text-xs text-zinc-400">{{ $reviewsCount }} {{ \Illuminate\Support\Str::plural('review', $reviewsCount) }}</p>
                        </div>

                        <div class="rounded-lg bg-amber-50 p-2.5 text-amber-600 dark:bg-amber-950/40">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-star preview-icon"><path d="M11.525 2.295a.53.53 0 0 1 .95 0l2.31 4.679a2.123 2.123 0 0 0 1.595 1.16l5.166.756a.53.53 0 0 1 .294.904l-3.736 3.638a2.123 2.123 0 0 0-.611 1.878l.882 5.14a.53.53 0 0 1-.771.56l-4.618-2.428a2.122 2.122 0 0 0-1.973 0L6.396 21.01a.53.53 0 0 1-.77-.56l.881-5.139a2.122 2.122 0 0 0-.611-1.879L2.16 9.795a.53.53 0 0 1 .294-.906l5.165-.755a2.122 2.122 0 0 0 1.597-1.16z"/></svg>
                        </div>
                    </div>
                </div>

            </div>

            {{-- Alert --}}
            <div class="rounded-xl border border-amber-200 bg-amber-50 px-5 py-4 dark:border-amber-900/50 dark:bg-amber-950/20">
                <p class="flex items-center gap-2 text-sm font-medium text-amber-800 dark:text-amber-300">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-circle-alert shrink-0">
                        <circle cx="12" cy="12" r="10"/>
                        <line x1="12" x2="12" y1="8" y2="12"/>
                        <line x1="12" x2="12.01" y1="16" y2="16"/>
                    </svg>

                    <span>{{ $pendingOrdersCount }} {{ \Illuminate\Support\Str::plural('order', $pendingOrdersCount) }} pending · Verify the payment method before changing an order's status</span>
                </p>
            </div>


            {{-- Recent orders --}}
            <div class="rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-800">

                <div class="flex items-center justify-between px-5 py-5">
                    <h2 class="font-display text-xl font-semibold text-zinc-900 dark:text-white">
                        Recent orders
                    </h2>

                    <a href="{{ route('admin.orders.index') }}"
                       class="text-sm font-medium text-red-600 hover:text-red-700">
                        View all orders →
                    </a>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">

                        <thead class="bg-zinc-100 text-xs text-zinc-500 dark:bg-zinc-700/50 dark:text-zinc-400">
                            <tr>
                                <th class="px-5 py-3 font-medium">Order no.</th>
                                <th class="px-5 py-3 font-medium">Member</th>
                                <th class="px-5 py-3 font-medium">Date</th>
                                <th class="px-5 py-3 font-medium">Total</th>
                                <th class="px-5 py-3 font-medium">Status</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">

                            @forelse ($latestOrders as $order)

                                @php
                                    $statusLabel = match ($order->status) {
                                        'pending' => 'Pending',
                                        'processing' => 'Preparing car',
                                        'completed' => 'Ready for handover',
                                        'cancelled' => 'Cancelled',
                                        default => ucfirst($order->status),
                                    };

                                    $statusClass = match ($order->status) {
                                        'pending' => 'text-amber-600',
                                        'processing' => 'text-zinc-500',
                                        'completed' => 'text-emerald-600',
                                        'cancelled' => 'text-red-600',
                                        default => 'text-zinc-500',
                                    };
                                @endphp

                                <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-700/30">
                                    <td class="px-5 py-4 font-semibold text-zinc-900 dark:text-white">
                                        VLC-{{ $order->order_id }}
                                    </td>
                                    <td class="px-5 py-4 text-zinc-600 dark:text-zinc-300">
                                        {{ $order->member?->name ?? '-' }}
                                    </td>
                                    <td class="px-5 py-4 text-zinc-600 dark:text-zinc-300">
                                        {{ $order->order_date?->format('j M Y') ?? '-' }}
                                    </td>
                                    <td class="px-5 py-4 font-semibold text-zinc-900 dark:text-white">
                                        ฿{{ number_format($order->total_amount, 0) }}
                                    </td>
                                    <td class="px-5 py-4 font-medium {{ $statusClass }}">
                                        {{ $statusLabel }}
                                    </td>
                                </tr>

                            @empty

                                <tr>
                                    <td colspan="5" class="px-5 py-8 text-center text-zinc-500">
                                        No orders yet
                                    </td>
                                </tr>

                            @endforelse

                        </tbody>
                    </table>
                </div>
            </div>


            {{-- Low stock + Revenue --}}
            <div class="grid grid-cols-1 gap-4 lg:grid-cols-5">

                {{-- Low stock cars --}}
                <div class="rounded-xl border border-zinc-200 bg-white shadow-sm
                            dark:border-zinc-800 dark:bg-zinc-900 lg:col-span-3">

                    <div class="flex items-start justify-between px-5 py-5">
                        <div>
                            <h2 class="font-display text-xl font-semibold text-zinc-900 dark:text-white">
                                Low stock cars
                            </h2>
                            <p class="mt-1 text-sm text-zinc-500">
                                Fewer than {{ $lowStockThreshold }} left in stock
                            </p>
                        </div>

                        @if ($lowStockTotal > 0)
                            <a href="{{ route('admin.cars.index', ['stock' => 'low']) }}"
                               class="text-sm font-medium text-red-600 hover:text-red-700">
                                View all ({{ $lowStockTotal }}) →
                            </a>
                        @endif
                    </div>

                    <div class="divide-y divide-zinc-200 px-5 dark:divide-zinc-700">
                        @forelse ($lowStockCars as $car)
                            @php
                                $img = $car->image_url;
                                $imgSrc = $img
                                    ? (\Illuminate\Support\Str::startsWith($img, ['http://', 'https://', '/']) ? $img : asset($img))
                                    : null;
                                $carName = trim(($car->brand?->brand_name ?? '') . ' ' . $car->model_name . ' ' . $car->model_year);
                                $editUrl = \Illuminate\Support\Facades\Route::has('admin.cars.edit')
                                    ? route('admin.cars.edit', $car->getKey())
                                    : route('admin.cars.index');
                            @endphp

                            <div class="flex items-center gap-4 py-4">
                                @if ($imgSrc)
                                    <img src="{{ $imgSrc }}" alt="{{ $carName }}"
                                         class="h-14 w-20 shrink-0 rounded-md object-cover">
                                @else
                                    <div class="flex h-14 w-20 shrink-0 items-center justify-center rounded-md
                                                bg-zinc-100 text-xs text-zinc-400 dark:bg-zinc-800">
                                        No image
                                    </div>
                                @endif

                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-sm font-semibold uppercase text-zinc-900 dark:text-white">
                                        {{ $carName }}
                                    </p>
                                    <p class="mt-0.5 text-xs text-zinc-500">
                                        ฿{{ number_format($car->price, 0) }}
                                        @if ($car->category?->category_name)
                                            · {{ $car->category->category_name }}
                                        @endif
                                    </p>
                                </div>

                                @if ($car->stock_qty <= 0)
                                    <span class="shrink-0 rounded-full bg-red-100 px-3 py-1 text-xs font-medium text-red-700
                                                 dark:bg-red-950/40 dark:text-red-400">
                                        Out of stock
                                    </span>
                                @else
                                    <span class="shrink-0 rounded-full bg-amber-100 px-3 py-1 text-xs font-medium text-amber-800
                                                 dark:bg-amber-950/40 dark:text-amber-300">
                                        {{ $car->stock_qty }} left
                                    </span>
                                @endif

                                <a href="{{ $editUrl }}"
                                   class="shrink-0 text-sm font-medium text-red-600 hover:text-red-700">
                                    Edit
                                </a>
                            </div>
                        @empty
                            <p class="py-10 text-center text-sm text-zinc-500">
                                All cars are well stocked.
                            </p>
                        @endforelse
                    </div>

                    @if ($lowStockTotal > $lowStockCars->count())
                        <div class="border-t border-zinc-200 px-5 py-3 text-xs text-zinc-500 dark:border-zinc-700">
                            Showing {{ $lowStockCars->count() }} of {{ $lowStockTotal }} low stock cars
                        </div>
                    @endif
                </div>


                {{-- Revenue --}}
                <div class="rounded-xl border border-zinc-200 bg-white shadow-sm
                            dark:border-zinc-800 dark:bg-zinc-900 lg:col-span-2">

                    <div class="px-5 py-5">
                        <h2 class="font-display text-xl font-semibold text-zinc-900 dark:text-white">
                            Revenue
                        </h2>
                        <p class="mt-1 text-sm text-zinc-500">
                            Preparing and ready-for-handover orders
                        </p>
                    </div>

                    <div class="space-y-3 px-5 pb-5">
                        @foreach ($revenueRows as $row)
                            <div class="flex items-center justify-between rounded-lg border border-zinc-200 p-4
                                        dark:border-zinc-700">
                                <div>
                                    <p class="text-sm font-medium text-zinc-900 dark:text-white">
                                        {{ $row['label'] }}
                                    </p>
                                    <p class="mt-0.5 text-xs text-zinc-500">
                                        {{ $row['orders'] }} {{ \Illuminate\Support\Str::plural('order', $row['orders']) }}
                                    </p>
                                </div>

                                <p class="font-display text-xl font-semibold text-zinc-900 dark:text-white">
                                    ฿{{ number_format($row['amount'], 0) }}
                                </p>
                            </div>
                        @endforeach

                        <p class="pt-1 text-xs text-zinc-500">
                            Pending and cancelled orders are not counted.
                        </p>
                    </div>
                </div>

            </div>


            
            

        </div>
    </div>
</x-layouts::app>