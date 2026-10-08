@props([
    'sidebar' => false,
])

<a {{ $attributes->merge(['class' => 'flex items-center gap-1.5' . ($sidebar ? ' px-2' : '')]) }}>
    <span class="font-display text-2xl font-extrabold italic tracking-tight text-zinc-900 dark:text-white">VELOCE</span>
    <span class="inline-block h-3.5 w-6 -skew-x-12 bg-red-600"></span>
</a>