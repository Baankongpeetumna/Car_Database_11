@php
    use Illuminate\Support\Str;

    $statusValue = request('status');
    $total       = method_exists($orders, 'total') ? $orders->total() : $orders->count();
    $hasFilter   = filled($statusValue);
    $indexUrl    = route('admin.orders.index');
    $with        = fn (array $changes) => request()->fullUrlWithQuery($changes + ['page' => null]);

    $statusCounts = $statusCounts ?? null;
    if ($statusCounts instanceof \Illuminate\Support\Collection) {
        $statusCounts = $statusCounts->all();
    }

    $labels = [
        'pending'    => 'Pending',
        'processing' => 'Processing',
        'completed'  => 'Completed',
        'cancelled'  => 'Cancelled',
    ];

    // ลำดับของเส้นสถานะ (cancelled ไม่อยู่ในเส้น)
    $steps = ['pending', 'processing', 'completed'];

    // ถ้ามี route นี้ เส้นสถานะจะลากเปลี่ยนสถานะได้ ถ้าไม่มีจะเป็นแสดงผลอย่างเดียว
    $canEdit = \Illuminate\Support\Facades\Route::has('admin.orders.status');

    if ($statusCounts === null && ! $hasFilter) {
        $pageRows     = method_exists($orders, 'getCollection') ? $orders->getCollection() : collect($orders);
        $statusCounts = collect($labels)->map(fn ($l, $key) => $pageRows->where('status', $key)->count())->all();
    }
    $allCount = $statusCounts !== null ? array_sum($statusCounts) : null;

    $statusTheme = [
        'pending'    => ['on' => 'bg-amber-500 text-white',   'dot' => 'bg-amber-500',   'avatar' => 'from-amber-400 to-amber-600',   'ring' => 'ring-amber-200 dark:ring-amber-900'],
        'processing' => ['on' => 'bg-sky-600 text-white',     'dot' => 'bg-sky-500',     'avatar' => 'from-sky-400 to-sky-600',       'ring' => 'ring-sky-200 dark:ring-sky-900'],
        'completed'  => ['on' => 'bg-emerald-600 text-white', 'dot' => 'bg-emerald-500', 'avatar' => 'from-emerald-400 to-emerald-600', 'ring' => 'ring-emerald-200 dark:ring-emerald-900'],
        'cancelled'  => ['on' => 'bg-zinc-600 text-white',    'dot' => 'bg-zinc-400',    'avatar' => 'from-zinc-500 to-zinc-700',     'ring' => 'ring-zinc-200 dark:ring-zinc-800'],
    ];
    $fallbackTheme = ['on' => 'bg-zinc-600 text-white', 'dot' => 'bg-zinc-400', 'avatar' => 'from-zinc-500 to-zinc-700', 'ring' => 'ring-zinc-200 dark:ring-zinc-800'];
@endphp

<x-layouts::app :title="'Manage Orders'">
    <div class="relative min-h-full overflow-hidden bg-zinc-50 dark:bg-zinc-950">
        {{-- glow ด้านบน --}}
        <div class="pointer-events-none absolute -top-32 left-1/2 h-72 w-[42rem] -translate-x-1/2 rounded-full bg-red-500/10 blur-3xl dark:bg-red-500/10"></div>

        <div class="relative mx-auto max-w-7xl space-y-6 p-2 sm:p-4">

            @include('commerce.messages')

            {{-- Header --}}
            <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <div class="flex items-center gap-2 text-sm text-zinc-400">
                        <a href="{{ route('admin.dashboard') }}" class="hover:text-red-600">Admin</a>
                        <span>/</span>
                        <span class="text-zinc-600 dark:text-zinc-300">Orders</span>
                    </div>

                    <h1 class="font-display mt-1 text-4xl font-black uppercase italic leading-none tracking-tight
                               text-zinc-900 sm:text-5xl dark:text-white">
                        Orders
                    </h1>
                    <div class="mt-2 h-1 w-14 -skew-x-12 bg-red-600"></div>
                </div>

                    <p class="shrink-0 rounded-full border border-zinc-200 bg-white px-4 py-1.5 text-sm text-zinc-500 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                        <span class="font-bold text-zinc-900 dark:text-white">{{ number_format($total) }}</span>
                        {{ Str::plural('order', $total) }}
                    </p>
                </div>
            </header>

            {{-- Filter --}}
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ $with(['status' => null]) }}"
                   class="inline-flex items-center gap-1.5 rounded-full border px-4 py-1.5 text-sm font-semibold transition
                          {{ blank($statusValue)
                                ? 'border-transparent bg-zinc-900 text-white shadow-lg shadow-zinc-900/20 dark:bg-white dark:text-zinc-900'
                                : 'border-zinc-200 bg-white text-zinc-700 hover:-translate-y-0.5 hover:border-zinc-400 hover:shadow-sm dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-300' }}">
                    All
                    @if ($allCount !== null)
                        <span class="rounded-full bg-black/10 px-1.5 text-xs {{ blank($statusValue) ? '' : 'text-zinc-500' }}">{{ $allCount }}</span>
                    @endif
                </a>

                @foreach ($labels as $value => $label)
                    @php
                        $on    = (string) $statusValue === $value;
                        $theme = $statusTheme[$value] ?? $fallbackTheme;
                        $cnt   = $statusCounts[$value] ?? null;
                    @endphp
                    <a href="{{ $with(['status' => $value]) }}"
                       class="inline-flex items-center gap-1.5 rounded-full border px-4 py-1.5 text-sm font-semibold transition
                              {{ $on
                                    ? $theme['on'].' border-transparent shadow-lg'
                                    : 'border-zinc-200 bg-white text-zinc-700 hover:-translate-y-0.5 hover:border-zinc-400 hover:shadow-sm dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-300' }}">
                        <span class="h-2 w-2 rounded-full {{ $on ? 'bg-white' : $theme['dot'] }}"></span>
                        {{ $label }}
                        @if ($cnt !== null)
                            <span class="rounded-full bg-black/10 px-1.5 text-xs {{ $on ? '' : 'text-zinc-500' }}">{{ $cnt }}</span>
                        @endif
                    </a>
                @endforeach

                @if ($hasFilter)
                    <a href="{{ $indexUrl }}" class="ml-1 text-xs font-semibold text-red-600 hover:text-red-700">Clear filter</a>
                @endif
            </div>

            {{-- Orders --}}
            @if ($orders->count())
                <div class="space-y-3">
                    @foreach ($orders as $order)
                        @php
                            $theme     = $statusTheme[$order->status] ?? $fallbackTheme;
                            $mName     = $order->member?->name ?? 'Deleted member';
                            $initial   = Str::upper(Str::substr($mName, 0, 1));
                            $showUrl   = route('admin.orders.show', $order);
                            $cancelled = $order->status === 'cancelled';
                            $idx       = array_search($order->status, $steps, true);
                            $idx       = $idx === false ? 0 : $idx;
                            $editable  = $canEdit && ! $cancelled;
                        @endphp

                        <article class="group relative flex flex-col gap-4 overflow-hidden rounded-2xl border border-zinc-200 bg-white p-4 pl-6 shadow-sm transition duration-200
                                        hover:-translate-y-0.5 hover:border-zinc-300 hover:shadow-xl hover:shadow-zinc-900/5
                                        dark:border-zinc-800 dark:bg-zinc-900 dark:hover:border-zinc-700
                                        md:flex-row md:items-center md:gap-6">
                            <div class="absolute inset-y-0 left-0 w-1.5 {{ $theme['dot'] }}"></div>

                            {{-- Order no. --}}
                            <a href="{{ $showUrl }}"
                               class="shrink-0 rounded-lg bg-zinc-100 px-2.5 py-1 text-center text-base font-bold text-zinc-900 transition hover:bg-red-600 hover:text-white dark:bg-zinc-800 dark:text-white md:w-14">
                                #{{ $order->order_id }}
                            </a>

                            {{-- Member --}}
                            <div class="flex min-w-0 flex-1 items-center gap-3">
                                <div class="grid h-12 w-12 shrink-0 place-items-center rounded-xl bg-gradient-to-br text-lg font-bold text-white ring-4 {{ $theme['avatar'] }} {{ $theme['ring'] }}">
                                    {{ $initial }}
                                </div>
                                <div class="min-w-0">
                                    <p class="truncate font-semibold text-zinc-900 dark:text-white">{{ $mName }}</p>
                                    <p class="truncate text-sm text-zinc-500">{{ $order->member?->email }}</p>
                                </div>
                            </div>

                            {{-- Date --}}
                            @php
                                $orderDate = $order->order_date?->copy()->timezone('Asia/Bangkok');
                            @endphp

                            <div class="md:w-28">
                                <p class="text-[11px] font-medium uppercase tracking-wider text-zinc-400">Date</p>
                                <p class="mt-0.5 text-sm font-semibold text-zinc-900 dark:text-white">
                                    {{ $orderDate?->format('d M Y') }}
                                </p>
                                <p class="text-xs text-zinc-500">
                                    {{ $orderDate?->format('H:i') }}
                                </p>
                            </div>

                            {{-- Total --}}
                            <div class="md:w-44">
                                <p class="text-[11px] font-medium uppercase tracking-wider text-zinc-400">Total</p>
                                <p class="mt-0.5 text-lg font-bold tabular-nums {{ $cancelled ? 'text-zinc-400 line-through dark:text-zinc-500' : 'text-zinc-900 dark:text-white' }}">
                                    ฿{{ \App\Support\Money::display(\App\Support\Money::cents($order->total_amount)) }}
                                </p>
                            </div>

                            {{-- Status: เส้นสถานะ ลากเลื่อนได้ --}}
                            <div class="md:w-60">
                                @if ($cancelled)
                                    <div class="flex items-center gap-3">
                                        <div class="h-0 flex-1 border-t-2 border-dashed border-zinc-300 dark:border-zinc-700"></div>
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-zinc-200 px-3 py-1 text-xs font-semibold text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400">
                                            <svg class="h-3 w-3" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"><path d="M5 5l10 10M15 5L5 15"/></svg>
                                            Cancelled
                                        </span>
                                        <div class="h-0 flex-1 border-t-2 border-dashed border-zinc-300 dark:border-zinc-700"></div>
                                    </div>
                                @else
                                    <div x-data="{
        idx: {{ $idx }},
        orig: {{ $idx }},
        drag: false,
        editable: {{ $editable ? 'true' : 'false' }},
        steps: @js($steps),
        names: @js(array_values(array_map(fn ($s) => $labels[$s], $steps))),
        knob: ['bg-amber-500', 'bg-sky-600', 'bg-emerald-600'],
        fill: ['bg-amber-500', 'bg-sky-500', 'bg-emerald-500'],
        pick(e) {
            const r = $refs.track.getBoundingClientRect();
            const p = (e.clientX - r.left) / r.width;
            this.idx = Math.max(0, Math.min(2, Math.round(p * 2)));
        },
        start(e) {
            if (!this.editable) return;
            this.drag = true;
            e.currentTarget.setPointerCapture(e.pointerId);
            this.pick(e);
        },
        move(e) { if (this.drag) this.pick(e); },
        end() {
            if (!this.drag) return;
            this.drag = false;
            if (this.idx === this.orig) return;
            $refs.status.value = this.steps[this.idx];
            $refs.form.dataset.confirm = 'Change order #{{ $order->order_id }} status to ' + this.names[this.idx] + '?';
            $refs.form.requestSubmit();
        }
    }">

                                        @if ($editable)
                                            <form x-ref="form" method="POST" action="{{ route('admin.orders.status', $order) }}" class="hidden"
                                                    data-confirm="Change order status?"
                                                    data-confirm-title="Change order status"
                                                    data-confirm-ok="Confirm"
                                                    data-confirm-tone="default"
                                                    @confirm-cancelled="idx = orig">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="status" x-ref="status" value="{{ $order->status }}">
                                            </form>
                                        @endif

                                        {{-- ชื่อสถานะปัจจุบัน --}}
                                        <p class="mb-2 text-xs font-bold text-zinc-700 dark:text-zinc-200" x-text="names[idx]"></p>

                                        {{-- เส้น --}}
                                        <div class="px-2">
                                            <div x-ref="track"
                                                 @pointerdown="start($event)" @pointermove="move($event)" @pointerup="end()" @pointercancel="end()"
                                                 class="relative h-6 select-none"
                                                 :class="editable ? 'cursor-grab touch-none' : ''"
                                                 :style="drag ? 'cursor:grabbing' : ''">
                                                {{-- เส้นพื้น --}}
                                                <div class="absolute inset-x-0 top-1/2 h-1.5 -translate-y-1/2 rounded-full bg-zinc-200 dark:bg-zinc-700"></div>
                                                {{-- เส้นที่เต็มแล้ว --}}
                                                <div class="absolute left-0 top-1/2 h-1.5 -translate-y-1/2 rounded-full transition-all"
                                                     :class="[fill[idx], drag ? 'duration-0' : 'duration-300']"
                                                     :style="`width:${idx * 50}%`"></div>
                                                {{-- จุด 3 จุด --}}
                                                @foreach ($steps as $i => $s)
                                                    <span class="absolute top-1/2 h-3 w-3 -translate-x-1/2 -translate-y-1/2 rounded-full border-2 border-white transition-colors dark:border-zinc-900"
                                                          :class="idx >= {{ $i }} ? fill[{{ $i }}] : 'bg-zinc-300 dark:bg-zinc-600'"
                                                          style="left: {{ $i * 50 }}%"></span>
                                                @endforeach
                                                {{-- ปุ่มเลื่อน --}}
                                                <span class="absolute top-1/2 grid h-6 w-6 -translate-x-1/2 -translate-y-1/2 place-items-center rounded-full shadow-lg ring-4 ring-white transition-all dark:ring-zinc-900"
                                                      :class="[knob[idx], drag ? 'scale-110 duration-0' : 'duration-300']"
                                                      :style="`left:${idx * 50}%`">
                                                    <span class="h-2 w-2 rounded-full bg-white"></span>
                                                </span>
                                            </div>
                                        </div>

                                        {{-- ป้ายใต้จุด --}}
                                        <div class="relative mt-1 h-4 px-2 text-[10px] font-medium text-zinc-400">
                                            <span class="absolute left-2">Pending</span>
                                            <span class="absolute left-1/2 -translate-x-1/2">Processing</span>
                                            <span class="absolute right-2">Delivered</span>
                                        </div>
                                    </div>
                                @endif
                            </div>

                            {{-- Action --}}
                            <a href="{{ $showUrl }}"
                               class="inline-flex h-10 shrink-0 items-center justify-center gap-2 rounded-xl bg-gradient-to-b from-red-500 to-red-600 px-5 text-sm font-semibold text-white shadow-md shadow-red-600/25 transition hover:-translate-y-0.5 hover:shadow-lg hover:shadow-red-600/30 focus:outline-none focus:ring-2 focus:ring-red-500/40">
                                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 6h12M4 10h12M4 14h7"/></svg>
                                Manage
                            </a>
                        </article>
                    @endforeach
                </div>
            @else
                <div class="rounded-2xl border border-dashed border-zinc-300 bg-white px-6 py-16 text-center dark:border-zinc-700 dark:bg-zinc-900">
                    <p class="text-lg font-semibold text-zinc-900 dark:text-white">No orders found</p>
                    <p class="mt-1 text-sm text-zinc-500">
                        {{ $hasFilter ? 'No orders have this status yet.' : 'Orders will show up here once members check out.' }}
                    </p>
                    @if ($hasFilter)
                        <a href="{{ $indexUrl }}"
                           class="mt-5 inline-flex items-center justify-center rounded-xl bg-red-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-red-500">
                            Show all orders
                        </a>
                    @endif
                </div>
            @endif

            @if (method_exists($orders, 'hasPages') && $orders->hasPages())
                <div class="pt-1">
                    {{ $orders->withQueryString()->links() }}
                </div>
            @endif

        </div>
    </div>
</x-layouts::app>