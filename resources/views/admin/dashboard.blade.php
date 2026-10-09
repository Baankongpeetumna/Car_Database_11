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

            @php
                $adminName = auth()->user()?->name ?? 'Admin';
                $firstName = \Illuminate\Support\Str::of($adminName)->before(' ');
                $hour = now()->hour;
                $greeting = $hour < 12 ? 'Good morning' : ($hour < 18 ? 'Good afternoon' : 'Good evening');
                $rating = (float) ($averageRating ?? 0);

                // Inner SVG paths (lucide) reused for the badge icon and the big watermark
                $icons = [
                    'car'     => '<path d="m21 8-2 2-1.5-3.7A2 2 0 0 0 15.646 5H8.4a2 2 0 0 0-1.903 1.257L5 10 3 8"/><path d="M7 14h.01"/><path d="M17 14h.01"/><rect width="18" height="8" x="3" y="10" rx="2"/><path d="M5 18v2"/><path d="M19 18v2"/>',
                    'orders'  => '<rect width="8" height="4" x="8" y="2" rx="1" ry="1"/><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><path d="M12 11h4"/><path d="M12 16h4"/><path d="M8 11h.01"/><path d="M8 16h.01"/>',
                    'members' => '<path d="M17 21v-1a2 2 0 00-2-2H9a2 2 0 00-2 2v1"/><path d="M19 10h1a2 2 0 012 2v1"/><path d="M5 10H4a2 2 0 00-2 2v1"/><circle cx="12" cy="11" r="3"/><circle cx="18" cy="4" r="2"/><circle cx="6" cy="4" r="2"/>',
                    'star'    => '<path d="M11.525 2.295a.53.53 0 0 1 .95 0l2.31 4.679a2.123 2.123 0 0 0 1.595 1.16l5.166.756a.53.53 0 0 1 .294.904l-3.736 3.638a2.123 2.123 0 0 0-.611 1.878l.882 5.14a.53.53 0 0 1-.771.56l-4.618-2.428a2.122 2.122 0 0 0-1.973 0L6.396 21.01a.53.53 0 0 1-.77-.56l.881-5.139a2.122 2.122 0 0 0-.611-1.879L2.16 9.795a.53.53 0 0 1 .294-.906l5.165-.755a2.122 2.122 0 0 0 1.597-1.16z"/>',
                ];
            @endphp

            {{-- Hero header --}}
            <div class="relative overflow-hidden rounded-2xl bg-zinc-950 text-white shadow-xl ring-1 ring-white/10">

                {{-- glow + speed lines --}}
                <div class="pointer-events-none absolute -right-24 -top-28 h-80 w-80 rounded-full bg-red-600/40 blur-3xl"></div>
                <div class="pointer-events-none absolute -bottom-32 left-1/3 h-64 w-64 rounded-full bg-red-500/20 blur-3xl"></div>
                <div class="pointer-events-none absolute inset-y-0 right-0 w-3/5 opacity-100"
                     style="background: repeating-linear-gradient(115deg, transparent 0 22px, rgba(255,255,255,.045) 22px 24px);
                            -webkit-mask-image: linear-gradient(to left, black, transparent);
                            mask-image: linear-gradient(to left, black, transparent);"></div>

                {{-- big faded car --}}
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="0.6"
                     stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"
                     class="pointer-events-none absolute -bottom-6 right-4 hidden h-52 w-52 text-white/10 sm:block">
                    {!! $icons['car'] !!}
                </svg>

                {{-- racing stripe --}}
                <div class="absolute inset-x-0 bottom-0 h-1 bg-gradient-to-r from-red-600 via-red-500 to-transparent"></div>

                <div class="relative flex flex-col gap-6 p-6 sm:flex-row sm:items-end sm:justify-between sm:p-8">
                    <div>
                        <div class="flex flex-wrap items-center gap-2 text-xs">
                            <span class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1 font-medium text-zinc-200 ring-1 ring-white/10 backdrop-blur">
                                <span class="relative flex h-2 w-2">
                                    <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-75"></span>
                                    <span class="relative inline-flex h-2 w-2 rounded-full bg-emerald-400"></span>
                                </span>
                                Live · {{ now()->format('l, j F Y') }}
                            </span>
                            <span class="text-zinc-500">Admin / Dashboard</span>
                        </div>

                        <h1 class="font-display mt-4 text-3xl font-bold leading-tight tracking-tight sm:text-4xl">
                            {{ $greeting }}, <span class="bg-gradient-to-r from-red-400 to-red-600 bg-clip-text text-transparent">{{ $firstName }}</span>
                        </h1>

                        <p class="mt-2 max-w-xl text-sm text-zinc-400">
                            Overview of cars, orders and members at
                            <span class="font-semibold text-white">VELOCE</span>
                        </p>
                    </div>

                    <div class="flex flex-wrap items-center gap-3">
                        <a href="{{ route('admin.orders.index') }}"
                           class="inline-flex items-center justify-center rounded-xl border border-white/15 bg-white/5
                                  px-5 py-2.5 text-sm font-medium text-white backdrop-blur transition hover:bg-white/15">
                            View orders
                        </a>

                        <a href="{{ route('admin.cars.index') }}"
                           class="inline-flex items-center justify-center gap-2 rounded-xl bg-red-600 px-5 py-2.5
                                  text-sm font-semibold text-white shadow-lg shadow-red-600/40 transition
                                  hover:-translate-y-0.5 hover:bg-red-500">
                            <span class="text-lg leading-none">+</span>
                            Add new car
                        </a>
                    </div>
                </div>
            </div>


            {{-- Statistics --}}
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">

                {{-- Cars --}}
                <div class="group relative overflow-hidden rounded-2xl border border-zinc-200 bg-gradient-to-br from-red-50 via-white to-white p-5 shadow-sm
                            transition duration-300 hover:-translate-y-1 hover:shadow-xl hover:shadow-red-600/10
                            dark:border-zinc-800 dark:from-red-950/40 dark:via-zinc-900 dark:to-zinc-900">
                    <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-red-500 to-red-700"></div>
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"
                         class="pointer-events-none absolute -bottom-5 -right-5 h-32 w-32 text-red-600/10 transition duration-500 group-hover:-rotate-6 group-hover:scale-110">{!! $icons['car'] !!}</svg>

                    <div class="relative flex items-start justify-between">
                        <p class="text-xs font-semibold uppercase tracking-wider text-zinc-500">Total cars</p>
                        <div class="grid h-10 w-10 place-items-center rounded-xl bg-red-600 text-white shadow-lg shadow-red-600/30">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $icons['car'] !!}</svg>
                        </div>
                    </div>

                    <p class="relative mt-4 font-display text-5xl font-bold leading-none text-zinc-900 dark:text-white"
                       data-countup="{{ (int) $carsCount }}">{{ $carsCount }}</p>
                    <p class="relative mt-2 text-sm text-zinc-500">cars in the system</p>

                    <div class="relative mt-4 flex flex-wrap gap-1.5">
                        <span class="rounded-full bg-red-100 px-2.5 py-1 text-[11px] font-medium text-red-700 dark:bg-red-950/60 dark:text-red-300">{{ $brandsCount }} brands</span>
                        <span class="rounded-full bg-zinc-100 px-2.5 py-1 text-[11px] font-medium text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">{{ $categoriesCount }} categories</span>
                    </div>
                </div>


                {{-- Orders --}}
                <div class="group relative overflow-hidden rounded-2xl border border-zinc-200 bg-gradient-to-br from-blue-50 via-white to-white p-5 shadow-sm
                            transition duration-300 hover:-translate-y-1 hover:shadow-xl hover:shadow-blue-600/10
                            dark:border-zinc-800 dark:from-blue-950/40 dark:via-zinc-900 dark:to-zinc-900">
                    <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-blue-500 to-indigo-600"></div>
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"
                         class="pointer-events-none absolute -bottom-5 -right-5 h-32 w-32 text-blue-600/10 transition duration-500 group-hover:-rotate-6 group-hover:scale-110">{!! $icons['orders'] !!}</svg>

                    <div class="relative flex items-start justify-between">
                        <p class="text-xs font-semibold uppercase tracking-wider text-zinc-500">Pending orders</p>
                        <div class="relative grid h-10 w-10 place-items-center rounded-xl bg-blue-600 text-white shadow-lg shadow-blue-600/30">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $icons['orders'] !!}</svg>
                            @if ($pendingOrdersCount > 0)
                                <span class="absolute -right-1 -top-1 flex h-3 w-3">
                                    <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-amber-400 opacity-75"></span>
                                    <span class="relative inline-flex h-3 w-3 rounded-full bg-amber-500 ring-2 ring-white dark:ring-zinc-900"></span>
                                </span>
                            @endif
                        </div>
                    </div>

                    <p class="relative mt-4 font-display text-5xl font-bold leading-none text-zinc-900 dark:text-white"
                       data-countup="{{ (int) $pendingOrdersCount }}">{{ $pendingOrdersCount }}</p>
                    <p class="relative mt-2 text-sm text-zinc-500">awaiting review and car preparation</p>

                    <div class="relative mt-4 flex flex-wrap gap-1.5">
                        @if ($pendingOrdersCount > 0)
                            <span class="rounded-full bg-amber-100 px-2.5 py-1 text-[11px] font-medium text-amber-800 dark:bg-amber-950/60 dark:text-amber-300">Needs attention</span>
                        @else
                            <span class="rounded-full bg-emerald-100 px-2.5 py-1 text-[11px] font-medium text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300">All caught up ✓</span>
                        @endif
                    </div>
                </div>


                {{-- Members --}}
                <div class="group relative overflow-hidden rounded-2xl border border-zinc-200 bg-gradient-to-br from-purple-50 via-white to-white p-5 shadow-sm
                            transition duration-300 hover:-translate-y-1 hover:shadow-xl hover:shadow-purple-600/10
                            dark:border-zinc-800 dark:from-purple-950/40 dark:via-zinc-900 dark:to-zinc-900">
                    <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-purple-500 to-fuchsia-600"></div>
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"
                         class="pointer-events-none absolute -bottom-5 -right-5 h-32 w-32 text-purple-600/10 transition duration-500 group-hover:-rotate-6 group-hover:scale-110">{!! $icons['members'] !!}</svg>

                    <div class="relative flex items-start justify-between">
                        <p class="text-xs font-semibold uppercase tracking-wider text-zinc-500">Total members</p>
                        <div class="grid h-10 w-10 place-items-center rounded-xl bg-purple-600 text-white shadow-lg shadow-purple-600/30">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $icons['members'] !!}</svg>
                        </div>
                    </div>

                    <p class="relative mt-4 font-display text-5xl font-bold leading-none text-zinc-900 dark:text-white"
                       data-countup="{{ (int) $membersCount }}">{{ $membersCount }}</p>
                    <p class="relative mt-2 text-sm text-zinc-500">registered members</p>

                    <div class="relative mt-4 flex flex-wrap gap-1.5">
                        <span class="inline-flex items-center gap-1 rounded-full bg-zinc-100 px-2.5 py-1 text-[11px] font-medium text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300"><span class="h-1.5 w-1.5 rounded-full bg-zinc-400"></span>Basic</span>
                        <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2.5 py-1 text-[11px] font-medium text-slate-600 dark:bg-slate-800 dark:text-slate-300"><span class="h-1.5 w-1.5 rounded-full bg-slate-400"></span>Silver</span>
                        <span class="inline-flex items-center gap-1 rounded-full bg-amber-100 px-2.5 py-1 text-[11px] font-medium text-amber-700 dark:bg-amber-950/60 dark:text-amber-300"><span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>Gold</span>
                        <span class="inline-flex items-center gap-1 rounded-full bg-violet-100 px-2.5 py-1 text-[11px] font-medium text-violet-700 dark:bg-violet-950/60 dark:text-violet-300"><span class="h-1.5 w-1.5 rounded-full bg-violet-500"></span>Platinum</span>
                    </div>
                </div>


                {{-- Reviews --}}
                <div class="group relative overflow-hidden rounded-2xl border border-zinc-200 bg-gradient-to-br from-amber-50 via-white to-white p-5 shadow-sm
                            transition duration-300 hover:-translate-y-1 hover:shadow-xl hover:shadow-amber-500/10
                            dark:border-zinc-800 dark:from-amber-950/40 dark:via-zinc-900 dark:to-zinc-900">
                    <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-amber-400 to-orange-500"></div>
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"
                         class="pointer-events-none absolute -bottom-5 -right-5 h-32 w-32 text-amber-500/10 transition duration-500 group-hover:rotate-12 group-hover:scale-110">{!! $icons['star'] !!}</svg>

                    <div class="relative flex items-start justify-between">
                        <p class="text-xs font-semibold uppercase tracking-wider text-zinc-500">Reviews</p>
                        <div class="grid h-10 w-10 place-items-center rounded-xl bg-amber-500 text-white shadow-lg shadow-amber-500/30">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $icons['star'] !!}</svg>
                        </div>
                    </div>

                    <p class="relative mt-4 font-display text-5xl font-bold leading-none text-zinc-900 dark:text-white"
                       data-countup="{{ $rating }}" data-decimals="1">{{ number_format($rating, 1) }}</p>
                    <p class="relative mt-2 text-sm text-zinc-500">average rating</p>

                    <div class="relative mt-4 flex items-center gap-2">
                        <div class="flex items-center gap-0.5" aria-label="{{ number_format($rating, 1) }} out of 5">
                            @for ($i = 1; $i <= 5; $i++)
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" stroke-width="1.5" stroke-linejoin="round" aria-hidden="true"
                                     fill="{{ $i <= round($rating) ? 'currentColor' : 'none' }}" stroke="currentColor"
                                     class="{{ $i <= round($rating) ? 'text-amber-400' : 'text-zinc-300 dark:text-zinc-600' }}">{!! $icons['star'] !!}</svg>
                            @endfor
                        </div>
                        <span class="text-[11px] font-medium text-zinc-500">{{ $reviewsCount }} {{ \Illuminate\Support\Str::plural('review', $reviewsCount) }}</span>
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

                    @if ($lowStockCars->isEmpty())
                        <p class="px-5 pb-10 pt-4 text-center text-sm text-zinc-500">
                            All cars are well stocked.
                        </p>
                    @else
                        <div class="grid grid-cols-1 gap-3 px-5 pb-5 sm:grid-cols-3">
                            @foreach ($lowStockCars as $car)
                                @php
                                    // Same image logic as the storefront product pages
                                    $imgSrc = \App\Support\CarVisual::imageSrc($car);
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

                    @if ($lowStockTotal > $lowStockCars->count())
                        <div class="border-t border-zinc-200 px-5 py-3 text-xs text-zinc-500 dark:border-zinc-700">
                            Showing {{ $lowStockCars->count() }} of {{ $lowStockTotal }} low stock cars
                        </div>
                    @endif
                </div>


                {{-- Revenue --}}
                @php
                    $revToday = $revenueRows[0];
                    $revMonth = $revenueRows[1];
                    $revYear  = $revenueRows[2];

                    $pctOfYear = fn ($amount) => $revYear['amount'] > 0
                        ? (int) min(100, round($amount / $revYear['amount'] * 100))
                        : 0;

                    $avgOrder = $revYear['orders'] > 0 ? $revYear['amount'] / $revYear['orders'] : 0;
                @endphp

                <div class="flex flex-col rounded-xl border border-zinc-200 bg-white shadow-sm
                            dark:border-zinc-800 dark:bg-zinc-900 lg:col-span-2">

                    <div class="flex items-start justify-between px-5 py-5">
                        <div>
                            <h2 class="font-display text-xl font-semibold text-zinc-900 dark:text-white">
                                Revenue
                            </h2>
                            <p class="mt-1 text-sm text-zinc-500">
                                Preparing and ready-for-handover orders
                            </p>
                        </div>

                        <div class="rounded-lg bg-emerald-50 p-2.5 text-emerald-600 dark:bg-emerald-950/40">
                            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="22 7 13.5 15.5 8.5 10.5 2 17"/><polyline points="16 7 22 7 22 13"/></svg>
                        </div>
                    </div>

                    <div class="flex flex-1 flex-col gap-3 px-5 pb-5">

                        {{-- Hero: this year --}}
                        <div class="relative overflow-hidden rounded-xl bg-gradient-to-br from-red-600 to-red-800 p-5 text-white shadow-sm">
                            <div class="pointer-events-none absolute -right-8 -top-8 h-28 w-28 rounded-full bg-white/10"></div>
                            <div class="pointer-events-none absolute -bottom-10 right-6 h-24 w-24 rounded-full bg-white/10"></div>

                            <p class="relative text-xs font-medium uppercase tracking-wider text-red-100">
                                This year
                            </p>
                            <p class="relative mt-1 font-display text-3xl font-bold leading-tight">
                                ฿{{ number_format($revYear['amount'], 0) }}
                            </p>
                            <div class="relative mt-2 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-red-100">
                                <span>{{ $revYear['orders'] }} {{ \Illuminate\Support\Str::plural('order', $revYear['orders']) }}</span>
                                @if ($avgOrder > 0)
                                    <span class="opacity-60">•</span>
                                    <span>avg ฿{{ number_format($avgOrder, 0) }} / order</span>
                                @endif
                            </div>
                        </div>

                        {{-- Today + This month, with share of the year --}}
                        <div class="grid grid-cols-2 gap-3">
                            @foreach ([['row' => $revToday, 'color' => 'bg-sky-500', 'dot' => 'bg-sky-500'], ['row' => $revMonth, 'color' => 'bg-amber-500', 'dot' => 'bg-amber-500']] as $tile)
                                @php $r = $tile['row']; $pct = $pctOfYear($r['amount']); @endphp

                                <div class="rounded-xl border border-zinc-200 p-3.5 dark:border-zinc-700">
                                    <div class="flex items-center gap-1.5">
                                        <span class="h-2 w-2 rounded-full {{ $tile['dot'] }}"></span>
                                        <p class="text-xs font-medium text-zinc-500">{{ $r['label'] }}</p>
                                    </div>

                                    <p class="mt-1.5 font-display text-lg font-bold leading-tight text-zinc-900 dark:text-white">
                                        ฿{{ number_format($r['amount'], 0) }}
                                    </p>
                                    <p class="mt-0.5 text-[11px] text-zinc-500">
                                        {{ $r['orders'] }} {{ \Illuminate\Support\Str::plural('order', $r['orders']) }}
                                    </p>

                                    <div class="mt-2.5 h-1.5 overflow-hidden rounded-full bg-zinc-100 dark:bg-zinc-800">
                                        <div class="h-full rounded-full {{ $tile['color'] }}" style="width: {{ $pct }}%"></div>
                                    </div>
                                    <p class="mt-1 text-[10px] text-zinc-400">{{ $pct }}% of this year</p>
                                </div>
                            @endforeach
                        </div>

                        <p class="mt-auto pt-1 text-xs text-zinc-500">
                            Pending and cancelled orders are not counted.
                        </p>
                    </div>
                </div>

            </div>

        </div>
    </div>

    <script>
        // Count-up animation for the dashboard stat numbers (skipped if the user prefers reduced motion)
        (function () {
            if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

            document.querySelectorAll('[data-countup]').forEach(function (el) {
                var target = parseFloat(el.dataset.countup);
                if (isNaN(target) || target === 0) return;

                var decimals = parseInt(el.dataset.decimals || '0', 10);
                var duration = 900;
                var start = null;

                function fmt(n) {
                    return n.toLocaleString(undefined, {
                        minimumFractionDigits: decimals,
                        maximumFractionDigits: decimals
                    });
                }

                function step(ts) {
                    if (start === null) start = ts;
                    var t = Math.min((ts - start) / duration, 1);
                    var eased = 1 - Math.pow(1 - t, 3);
                    el.textContent = fmt(target * eased);
                    if (t < 1) requestAnimationFrame(step);
                    else el.textContent = fmt(target);
                }

                el.textContent = fmt(0);
                requestAnimationFrame(step);
            });
        })();
    </script>
</x-layouts::app>