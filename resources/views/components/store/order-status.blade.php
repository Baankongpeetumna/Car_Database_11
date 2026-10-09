@props(['status'])

@php
    // สถานะของตาราง ORDERS ตาม CommerceService: pending → processing → completed / cancelled
    [$label, $classes] = match ($status) {
        'pending' => ['Pending', 'bg-sun-soft text-amber-800 ring-sun'],
        'processing' => ['Processing', 'bg-sky-50 text-sky-700 ring-sky-200'],
        'completed' => ['Completed', 'bg-emerald-50 text-emerald-700 ring-emerald-200'],
        'cancelled' => ['Cancelled', 'bg-race-soft text-race-dark ring-race/30'],
        default => [$status, 'bg-paper text-zinc-600 ring-line'],
    };
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset {$classes}"]) }}>
    <span class="size-1.5 rounded-full bg-current"></span>{{ $label }}
</span>
