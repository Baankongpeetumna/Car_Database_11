@php
    $steps = ['Cart', 'Checkout', 'Order placed'];
    $current = $current ?? 1;
    $dark = $dark ?? false; // ใช้บนพื้นสีเข้ม (หน้าสั่งซื้อสำเร็จ)
@endphp

<ol class="flex items-center gap-2 text-xs font-semibold sm:gap-3 sm:text-sm" aria-label="Checkout progress">
    @foreach ($steps as $i => $label)
        @php $n = $i + 1; @endphp
        <li class="flex items-center gap-2 {{ $n <= $current ? ($dark ? 'text-white' : 'text-ink') : 'text-zinc-400' }}" @if ($n === $current) aria-current="step" @endif>
            <span @class([
                'flex size-7 items-center justify-center rounded-full font-display text-xs',
                'bg-race text-white' => $n === $current,
                'bg-ink text-white' => $n < $current && ! $dark,
                'bg-white text-ink' => $n < $current && $dark,
                'border border-line bg-white' => $n > $current,
            ])>
                @if ($n < $current)
                    <x-store.icon name="check" class="size-3.5" />
                @else
                    {{ $n }}
                @endif
            </span>
            <span class="hidden sm:inline">{{ $label }}</span>
        </li>
        @if (! $loop->last)
            <li class="h-px w-6 sm:w-12 {{ $dark ? 'bg-white/30' : 'bg-line' }}" aria-hidden="true"></li>
        @endif
    @endforeach
</ol>
