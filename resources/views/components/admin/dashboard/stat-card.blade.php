@props([
    'label',
    'icon',
    'value'    => 0,
    'decimals' => 0,
    'color'    => 'red',
    'caption'  => null,
    'href'     => null,   // when set, the whole card becomes a link
    'pulse'    => false,  // little blinking dot on the icon badge
])

@php
    // Full class strings so Tailwind can see them
    $themes = [
        'red' => [
            'tint'   => 'from-red-50 dark:from-red-950/40',
            'shadow' => 'hover:shadow-red-600/10',
            'bar'    => 'from-red-500 to-red-700',
            'mark'   => 'text-red-600/10',
            'badge'  => 'bg-red-600 shadow-red-600/30',
            'arrow'  => 'group-hover:bg-red-600 group-hover:text-white',
            'ring'   => 'focus-visible:ring-red-500',
        ],
        'blue' => [
            'tint'   => 'from-blue-50 dark:from-blue-950/40',
            'shadow' => 'hover:shadow-blue-600/10',
            'bar'    => 'from-blue-500 to-indigo-600',
            'mark'   => 'text-blue-600/10',
            'badge'  => 'bg-blue-600 shadow-blue-600/30',
            'arrow'  => 'group-hover:bg-blue-600 group-hover:text-white',
            'ring'   => 'focus-visible:ring-blue-500',
        ],
        'purple' => [
            'tint'   => 'from-purple-50 dark:from-purple-950/40',
            'shadow' => 'hover:shadow-purple-600/10',
            'bar'    => 'from-purple-500 to-fuchsia-600',
            'mark'   => 'text-purple-600/10',
            'badge'  => 'bg-purple-600 shadow-purple-600/30',
            'arrow'  => 'group-hover:bg-purple-600 group-hover:text-white',
            'ring'   => 'focus-visible:ring-purple-500',
        ],
        'amber' => [
            'tint'   => 'from-amber-50 dark:from-amber-950/40',
            'shadow' => 'hover:shadow-amber-500/10',
            'bar'    => 'from-amber-400 to-orange-500',
            'mark'   => 'text-amber-500/10',
            'badge'  => 'bg-amber-500 shadow-amber-500/30',
            'arrow'  => 'group-hover:bg-amber-500 group-hover:text-white',
            'ring'   => 'focus-visible:ring-amber-500',
        ],
    ];

    $t   = $themes[$color] ?? $themes['red'];
    $tag = $href ? 'a' : 'div';

    $classes = 'group relative block overflow-hidden rounded-2xl border border-zinc-200 bg-gradient-to-br '
        . $t['tint']
        . ' via-white to-white p-5 shadow-sm transition duration-300 hover:-translate-y-1 hover:shadow-xl '
        . $t['shadow']
        . ' dark:border-zinc-800 dark:via-zinc-900 dark:to-zinc-900'
        . ' focus:outline-none focus-visible:ring-2 ' . $t['ring'];
@endphp

<{{ $tag }}
    @if ($href) href="{{ $href }}" @endif
    {{ $attributes->merge(['class' => $classes]) }}>

    {{-- top accent bar --}}
    <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r {{ $t['bar'] }}"></div>

    {{-- big faded watermark icon --}}
    <x-admin.dashboard.icon :name="$icon" :size="24" stroke="1.5"
        class="pointer-events-none absolute -bottom-5 -right-5 h-32 w-32 {{ $t['mark'] }} transition duration-500 group-hover:-rotate-6 group-hover:scale-110" />

    <div class="relative flex items-start justify-between">
        <p class="flex items-center gap-1.5 text-xs font-semibold uppercase tracking-wider text-zinc-500 transition-colors group-hover:text-zinc-900 dark:group-hover:text-white">
            {{ $label }}
            @if ($href)
                <x-admin.dashboard.icon name="arrow" :size="12" stroke="2.5"
                    class="-translate-x-1 opacity-0 transition duration-200 group-hover:translate-x-0 group-hover:opacity-100" />
            @endif
        </p>

        <div class="relative grid h-10 w-10 place-items-center rounded-xl text-white shadow-lg {{ $t['badge'] }}">
            <x-admin.dashboard.icon :name="$icon" />

            @if ($pulse)
                <span class="absolute -right-1 -top-1 flex h-3 w-3">
                    <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-amber-400 opacity-75"></span>
                    <span class="relative inline-flex h-3 w-3 rounded-full bg-amber-500 ring-2 ring-white dark:ring-zinc-900"></span>
                </span>
            @endif
        </div>
    </div>

    <p class="relative mt-4 font-display text-5xl font-bold leading-none text-zinc-900 dark:text-white"
       data-countup="{{ (float) $value }}"
       data-decimals="{{ $decimals }}">{{ number_format((float) $value, $decimals) }}</p>

    @if ($caption)
        <p class="relative mt-2 text-sm text-zinc-500">{{ $caption }}</p>
    @endif

    {{-- extra content (chips, stars...) --}}
    @if (! $slot->isEmpty())
        <div class="relative mt-4 flex flex-wrap items-center gap-1.5">
            {{ $slot }}
        </div>
    @endif
</{{ $tag }}>