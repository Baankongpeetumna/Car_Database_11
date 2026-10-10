@php
    use Illuminate\Support\Str;

    $labels = [
        'pending'    => 'Pending',
        'processing' => 'Processing',
        'completed'  => 'Completed',
        'cancelled'  => 'Cancelled',
    ];

    // Same palette as the Orders list (full class names so Tailwind keeps them)
    $statusTheme = [
        'pending'    => ['badge' => 'bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300',         'dot' => 'bg-amber-500',   'avatar' => 'bg-amber-500 text-white'],
        'processing' => ['badge' => 'bg-sky-100 text-sky-700 dark:bg-sky-950/60 dark:text-sky-300',                 'dot' => 'bg-sky-500',     'avatar' => 'bg-sky-600 text-white'],
        'completed'  => ['badge' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300', 'dot' => 'bg-emerald-500', 'avatar' => 'bg-emerald-600 text-white'],
        'cancelled'  => ['badge' => 'bg-zinc-200 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400',                'dot' => 'bg-zinc-400',    'avatar' => 'bg-zinc-600 text-white'],
    ];
    $fallbackTheme = ['badge' => 'bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300', 'dot' => 'bg-zinc-400', 'avatar' => 'bg-zinc-600 text-white'];
    $theme = $statusTheme[$order->status] ?? $fallbackTheme;

    $steps     = ['pending' => 'Placed', 'processing' => 'Preparing', 'completed' => 'Delivered'];
    $stepIndex = array_search($order->status, array_keys($steps), true);
    $cancelled = $order->status === 'cancelled';
    $isOpen    = in_array($order->status, ['pending', 'processing'], true);

    $member  = $order->member;
    $mName   = $member?->name ?? 'Deleted member';
    $initial = Str::upper(Str::substr($mName, 0, 1));

    // Choices in the status form. Same rules as before.
    $choices = [];
    if ($order->status === 'pending') {
        $choices['processing'] = ['title' => 'Processing', 'hint' => 'Start preparing the car',               'dot' => 'bg-sky-500',     'checked' => 'peer-checked:border-sky-500 peer-checked:bg-sky-50 dark:peer-checked:bg-sky-950/30'];
    }
    $choices['completed'] = ['title' => 'Completed', 'hint' => 'Add points and update member tier',            'dot' => 'bg-emerald-500', 'checked' => 'peer-checked:border-emerald-500 peer-checked:bg-emerald-50 dark:peer-checked:bg-emerald-950/30'];
    $choices['cancelled'] = ['title' => 'Cancelled', 'hint' => 'Return stock',                                 'dot' => 'bg-zinc-400',    'checked' => 'peer-checked:border-zinc-500 peer-checked:bg-zinc-100 dark:peer-checked:bg-zinc-800/60'];
@endphp

<x-layouts::app :title="'Order #'.$order->order_id">
    <div class="min-h-full bg-zinc-50 dark:bg-zinc-950">
        <div class="mx-auto max-w-7xl space-y-5 p-2 sm:p-4">

            @include('commerce.messages')

            {{-- Header --}}
            <header class="px-1">
                <nav class="flex items-center gap-2 text-xs text-zinc-500" aria-label="Breadcrumb">
                    <a href="{{ route('admin.dashboard') }}" class="transition hover:text-red-600">Admin</a>
                    <span>/</span>
                    <a href="{{ route('admin.orders.index') }}" class="transition hover:text-red-600">Orders</a>
                    <span>/</span>
                    <span class="text-zinc-900 dark:text-white">Manage</span>
                </nav>

                <div class="mt-2 flex flex-wrap items-end justify-between gap-4">
                    <h1 class="font-display text-4xl font-black uppercase italic leading-none tracking-tight text-zinc-900 dark:text-white sm:text-5xl">
                        Order {{ $order->order_id }}
                    </h1>
                    <div class="flex items-center gap-3 pb-1">
                        <span class="text-sm text-zinc-500">{{ $order->order_date?->format('d M Y, H:i') }}</span>
                        <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-semibold {{ $theme['badge'] }}">
                            <span class="h-1.5 w-1.5 rounded-full {{ $theme['dot'] }}"></span>
                            {{ $labels[$order->status] ?? Str::headline((string) $order->status) }}
                        </span>
                    </div>
                </div>
            </header>

            {{-- Progress --}}
            <div class="overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                <div class="h-1 w-full {{ $theme['dot'] }}"></div>
                <div class="px-5 py-4">
                    @if ($cancelled)
                        <p class="text-sm text-zinc-500">This order was cancelled. Stock has been returned.</p>
                    @else
                        <ol class="flex items-center">
                            @foreach ($steps as $key => $stage)
                                @php
                                    $i       = $loop->index;
                                    $reached = $stepIndex !== false && $i <= $stepIndex;
                                @endphp
                                <li class="flex items-center gap-2 {{ $loop->last ? '' : 'flex-1' }}">
                                    <span class="grid h-6 w-6 shrink-0 place-items-center rounded-full text-xs font-bold
                                                 {{ $reached ? $theme['dot'].' text-white' : 'bg-zinc-200 text-zinc-500 dark:bg-zinc-800' }}">
                                        @if ($reached)
                                            <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 10.5l4 4 8-9"/></svg>
                                        @else
                                            {{ $i + 1 }}
                                        @endif
                                    </span>
                                    <span class="text-sm font-semibold {{ $reached ? 'text-zinc-900 dark:text-white' : 'text-zinc-500' }}">{{ $stage }}</span>
                                    @unless ($loop->last)
                                        <span class="mx-2 h-0.5 flex-1 rounded-full {{ $stepIndex !== false && $i < $stepIndex ? $theme['dot'] : 'bg-zinc-200 dark:bg-zinc-800' }}"></span>
                                    @endunless
                                </li>
                            @endforeach
                        </ol>
                    @endif
                </div>
            </div>

            <div class="grid gap-5 lg:grid-cols-[minmax(0,1fr)_22rem] lg:items-start">

                {{-- Left: order details (shared partial, unchanged) --}}
                <section class="min-w-0">
                    @include('commerce.order-details')
                </section>

                {{-- Right: member + status --}}
                <aside class="space-y-5 lg:sticky lg:top-4">

                    {{-- Member --}}
                    <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                        <div class="flex items-center gap-3">
                            <div class="grid h-12 w-12 shrink-0 place-items-center rounded-xl text-lg font-bold {{ $theme['avatar'] }}">
                                {{ $initial }}
                            </div>
                            <div class="min-w-0">
                                <p class="truncate font-semibold text-zinc-900 dark:text-white">{{ $mName }}</p>
                                <p class="truncate text-sm text-zinc-500">{{ $member?->email }}</p>
                                <p class="text-sm text-zinc-500">{{ $member?->phone ?? '-' }}</p>
                            </div>
                        </div>

                        <dl class="mt-4 grid grid-cols-2 divide-x divide-zinc-200 rounded-xl bg-zinc-100 px-4 py-3 dark:divide-zinc-700/60 dark:bg-zinc-800/50">
                            <div class="pr-4">
                                <dt class="text-xs text-zinc-500">Points</dt>
                                <dd class="mt-0.5 text-lg font-bold tabular-nums text-zinc-900 dark:text-white">{{ number_format($member?->points ?? 0) }}</dd>
                            </div>
                            <div class="pl-4">
                                <dt class="text-xs text-zinc-500">Tier</dt>
                                <dd class="mt-0.5 text-lg font-bold text-zinc-900 dark:text-white">{{ $member?->tier?->tier_name ?? '-' }}</dd>
                            </div>
                        </dl>
                    </div>

                    {{-- Change status --}}
                    @if ($isOpen)
                        <form method="POST"
                              action="{{ route('admin.orders.update', $order) }}"
                              class="space-y-4 rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900"
                                    data-confirm="This is a final status and cannot be changed."
                                    data-confirm-title="Confirm order status"
                                    data-confirm-ok="Confirm"
                                    data-confirm-if="input[name=status][value=completed]:checked, input[name=status][value=cancelled]:checked">
                            @csrf
                            @method('PATCH')

                            <fieldset class="space-y-2">
                                <legend class="mb-2 font-semibold text-zinc-900 dark:text-white">Change order status</legend>

                                @foreach ($choices as $value => $choice)
                                    <label class="block cursor-pointer">
                                        <input type="radio" name="status" value="{{ $value }}" required
                                               @checked(old('status') === $value)
                                               class="peer sr-only">
                                        <span class="flex items-start gap-3 rounded-xl border border-zinc-200 px-4 py-3 transition hover:border-zinc-400
                                                     peer-focus-visible:ring-2 peer-focus-visible:ring-red-500/50
                                                     dark:border-zinc-700 dark:hover:border-zinc-500 {{ $choice['checked'] }}">
                                            <span class="mt-1.5 h-2 w-2 shrink-0 rounded-full {{ $choice['dot'] }}"></span>
                                            <span>
                                                <span class="block text-sm font-semibold text-zinc-900 dark:text-white">{{ $choice['title'] }}</span>
                                                <span class="block text-xs text-zinc-500">{{ $choice['hint'] }}</span>
                                            </span>
                                        </span>
                                    </label>
                                @endforeach

                                @error('status')
                                    <p class="text-xs font-medium text-red-600">{{ $message }}</p>
                                @enderror
                            </fieldset>

                            <p class="text-xs text-zinc-500">
                                Select Completed only after payment and car delivery have been verified.
                                Completed and Cancelled are final statuses.
                            </p>

                            <button type="submit"
                                    class="inline-flex h-10 w-full items-center justify-center rounded-xl bg-red-600 text-sm font-semibold text-white transition hover:bg-red-500 focus:outline-none focus:ring-2 focus:ring-red-500/40">
                                Save status
                            </button>
                        </form>
                    @else
                        <div class="flex items-start gap-3 rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                            <svg class="mt-0.5 h-5 w-5 shrink-0 text-zinc-400" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="4" y="9" width="12" height="8" rx="2"/><path d="M7 9V6a3 3 0 016 0v3"/></svg>
                            <div>
                                <p class="font-semibold text-zinc-900 dark:text-white">This order is closed</p>
                                <p class="mt-0.5 text-sm text-zinc-500">Its status can no longer be changed.</p>
                            </div>
                        </div>
                    @endif

                    <a href="{{ route('admin.orders.index') }}"
                       class="inline-flex h-12 w-full items-center justify-center gap-2 rounded-xl border border-zinc-200 bg-white text-base font-semibold text-zinc-900 shadow-sm transition
                              hover:border-red-500/60 hover:text-red-600 focus:outline-none focus:ring-2 focus:ring-red-500/40
                              dark:border-zinc-700 dark:bg-zinc-900 dark:text-white dark:hover:border-red-500/60 dark:hover:text-red-500">
                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M16 10H4M9 5l-5 5 5 5"/></svg>
                        All orders
                    </a>
                </aside>
            </div>
        </div>
    </div>
</x-layouts::app>