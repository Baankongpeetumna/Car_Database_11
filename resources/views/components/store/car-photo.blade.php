@props(['car', 'size' => 'card'])

@php
    use App\Support\CarVisual;

    $src = CarVisual::imageSrc($car);
    $alt = trim(($car->brand?->brand_name ?? '').' '.$car->model_name.' '.$car->model_year);
@endphp

<div {{ $attributes->merge(['class' => 'relative overflow-hidden bg-paper']) }}>
    @if ($src)
        <img src="{{ $src }}" alt="{{ $alt }}" loading="lazy" class="absolute inset-0 size-full object-cover">
    @else
        <div class="absolute inset-0 bg-gradient-to-b from-white to-paper"></div>
        <div class="speed-lines absolute inset-0"></div>
        <div class="absolute inset-x-0 bottom-[14%] h-px bg-line"></div>
        <div @class([
            'absolute inset-0 flex items-end justify-center',
            'px-10 pb-[8%]' => $size === 'hero',
            'px-5 pb-[7%]' => $size === 'card',
            'px-1 !items-center' => $size === 'thumb',
        ])>
            <x-store.car-art
                :color="CarVisual::colorHex($car->color)"
                :type="CarVisual::bodyType($car->category?->category_name)"
                class="w-full max-w-[560px]"
            />
        </div>
    @endif
</div>
