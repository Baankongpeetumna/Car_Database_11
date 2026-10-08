@props(['kicker' => null, 'title', 'subtitle' => null])

<div {{ $attributes->merge(['class' => 'flex flex-wrap items-end justify-between gap-4']) }}>
    <div>
        @if ($kicker)
            <p class="flex items-center gap-2 font-display text-xs font-semibold uppercase italic tracking-[0.18em] text-race">
                <span class="inline-block h-0.5 w-6 bg-race"></span>{{ $kicker }}
            </p>
        @endif
        <h2 class="mt-1 font-display text-3xl font-extrabold uppercase italic leading-none tracking-tight text-ink sm:text-4xl">{{ $title }}</h2>
        @if ($subtitle)
            <p class="mt-2 text-sm text-zinc-500">{{ $subtitle }}</p>
        @endif
    </div>

    @isset($action)
        <div>{{ $action }}</div>
    @endisset
</div>
