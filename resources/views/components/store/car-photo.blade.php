@props(['car', 'size' => 'card'])

@php
    $src = \App\Support\CarVisual::imageSrc($car);

    $alt = trim(
        ($car->brand?->brand_name ?? '')
        .' '.$car->model_name
        .' '.$car->model_year
    );
@endphp

<!-- class="absolute inset-0 size-full object-cover" -->

<div {{ $attributes->merge([
    'class' => 'relative overflow-hidden bg-white',
]) }}>
    @if ($src)
        <img
            src="{{ $src }}"
            alt="{{ $alt }}"
            loading="{{ $size === 'hero' ? 'eager' : 'lazy' }}"
            decoding="async"
            class="absolute inset-0 h-full w-full object-contain object-center"
            
        >
    @else
        <div class="absolute inset-0 flex items-center justify-center bg-paper text-sm text-zinc-500">
            ยังไม่มีรูปรถ
        </div>
    @endif
</div>