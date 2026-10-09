@props(['condition'])

@php
    $isNew = \App\Support\CarVisual::isNew($condition);
    $label = \App\Support\CarVisual::conditionLabel($condition);
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-[11px] font-semibold leading-none '.($isNew ? 'bg-sun text-ink' : 'bg-ink text-white')]) }}>
    @if ($isNew)
        <x-store.icon name="sparkles" class="size-3" />
    @endif
    {{ $label }}
</span>
