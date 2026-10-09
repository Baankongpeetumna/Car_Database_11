<x-layouts::app :title="'Admin Dashboard'">
    @php
        // Route helper: returns null (card not clickable) if a route name doesn't exist
        $link = fn (string $name, array $params = []) => \Illuminate\Support\Facades\Route::has($name)
            ? route($name, $params)
            : null;

        $rating = (float) ($averageRating ?? 0);
        $pendingUrl = $link('admin.orders.index', ['status' => 'pending']);
    @endphp

    <div class="min-h-full bg-zinc-50 dark:bg-zinc-950">
        <div class="mx-auto max-w-[1600px] space-y-6 p-2 sm:p-4">

            <x-admin.dashboard.hero :name="auth()->user()?->name ?? 'Admin'" />

            {{-- Statistics: every card links to its page --}}
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">

                <x-admin.dashboard.stat-card
                    label="Total cars" icon="car" color="red"
                    :value="$carsCount" caption="cars in the system"
                    :href="$link('admin.cars.index')">
                    <span class="rounded-full bg-red-100 px-2.5 py-1 text-[11px] font-medium text-red-700 dark:bg-red-950/60 dark:text-red-300">{{ $brandsCount }} brands</span>
                    <span class="rounded-full bg-zinc-100 px-2.5 py-1 text-[11px] font-medium text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">{{ $categoriesCount }} categories</span>
                </x-admin.dashboard.stat-card>

                <x-admin.dashboard.stat-card
                    label="Pending orders" icon="orders" color="blue"
                    :value="$pendingOrdersCount" caption="awaiting review and car preparation"
                    :pulse="$pendingOrdersCount > 0"
                    :href="$pendingUrl">
                    @if ($pendingOrdersCount > 0)
                        <span class="rounded-full bg-amber-100 px-2.5 py-1 text-[11px] font-medium text-amber-800 dark:bg-amber-950/60 dark:text-amber-300">Needs attention</span>
                    @else
                        <span class="rounded-full bg-emerald-100 px-2.5 py-1 text-[11px] font-medium text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300">All caught up ✓</span>
                    @endif
                </x-admin.dashboard.stat-card>

                <x-admin.dashboard.stat-card
                    label="Total members" icon="members" color="purple"
                    :value="$membersCount" caption="registered members"
                    :href="$link('admin.members.index')">
                    <span class="rounded-full bg-zinc-100 px-2 py-1 text-[10px] font-medium text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">Basic</span>
                    <span class="rounded-full bg-slate-200 px-2 py-1 text-[10px] font-medium text-slate-600 dark:bg-slate-800 dark:text-slate-300">Silver</span>
                    <span class="rounded-full bg-amber-100 px-2 py-1 text-[10px] font-medium text-amber-700 dark:bg-amber-950/60 dark:text-amber-300">Gold</span>
                    <span class="rounded-full bg-violet-100 px-2 py-1 text-[10px] font-medium text-violet-700 dark:bg-violet-950/60 dark:text-violet-300">Platinum</span>
                </x-admin.dashboard.stat-card>

                <x-admin.dashboard.stat-card
                    label="Reviews" icon="star" color="amber"
                    :value="$rating" :decimals="1" caption="average rating"
                    :href="$link('admin.reviews.index')">
                    <div class="flex items-center gap-0.5" aria-label="{{ number_format($rating, 1) }} out of 5">
                        @for ($i = 1; $i <= 5; $i++)
                            <x-admin.dashboard.icon name="star" :size="16" stroke="1.5"
                                :fill="$i <= round($rating) ? 'currentColor' : 'none'"
                                :class="$i <= round($rating) ? 'text-amber-400' : 'text-zinc-300 dark:text-zinc-600'" />
                        @endfor
                    </div>
                    <span class="text-[11px] font-medium text-zinc-500">{{ $reviewsCount }} {{ \Illuminate\Support\Str::plural('review', $reviewsCount) }}</span>
                </x-admin.dashboard.stat-card>

            </div>

            <x-admin.dashboard.pending-alert :count="$pendingOrdersCount" />

            <x-admin.dashboard.recent-orders :orders="$latestOrders" />

            {{-- Low stock + Revenue --}}
            <div class="grid grid-cols-1 gap-4 lg:grid-cols-5">
                <x-admin.dashboard.low-stock class="lg:col-span-3" :threshold="3" />
                <x-admin.dashboard.revenue class="lg:col-span-2" />
            </div>

        </div>
    </div>

    <x-admin.dashboard.count-up-script />
</x-layouts::app>