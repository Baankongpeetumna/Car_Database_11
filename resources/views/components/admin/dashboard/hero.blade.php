@props(['name' => 'Admin'])

@php
    $firstName = \Illuminate\Support\Str::of($name)->before(' ');
@endphp

{{-- Header: no card, sits directly on the page --}}
<div class="flex flex-col gap-4 px-1 sm:flex-row sm:items-end sm:justify-between">
    <div>
        <div class="flex items-center gap-2 text-sm text-zinc-400">
            <span>Admin</span>
            <span>/</span>
            <span class="text-zinc-600 dark:text-zinc-300">Dashboard</span>
        </div>

        <h1 class="font-display mt-1 text-4xl font-black uppercase italic leading-none tracking-tight
                   text-zinc-900 sm:text-5xl dark:text-white">
            Welcome, <span class="text-red-600">{{ $firstName }}</span>
        </h1>
        <div class="mt-2 h-1 w-14 -skew-x-12 bg-red-600"></div>

        <p class="mt-2 text-sm text-zinc-500">
            Overview of cars, orders and members at
            <span class="font-semibold text-zinc-900 dark:text-white">VELOCE</span>
        </p>
    </div>

    <div class="flex flex-wrap items-center gap-3">
        <a href="{{ route('admin.orders.index') }}"
           class="inline-flex items-center justify-center rounded-xl border border-zinc-300 px-5 py-2.5 text-sm font-semibold
                  text-zinc-700 transition hover:border-zinc-500 dark:border-zinc-700 dark:text-zinc-300">
            View orders
        </a>

        <a href="{{ route('admin.cars.create') }}"
           class="inline-flex items-center justify-center gap-2 rounded-xl bg-red-600 px-5 py-2.5 text-sm font-semibold text-white
                  shadow-md shadow-red-600/30 transition hover:bg-red-500 focus:outline-none focus:ring-2 focus:ring-red-500/40">
            <span class="text-lg leading-none">+</span>
            Add new car
        </a>
    </div>
</div>
