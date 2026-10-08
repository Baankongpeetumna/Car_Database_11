@props(['year'])

{{-- ป้ายปีรถ หน้าตาคล้ายป้ายทะเบียน --}}
<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1 rounded-md border-2 border-ink bg-white px-2 py-0.5 font-display text-xs font-semibold leading-none text-ink shadow-[0_2px_0_0_var(--color-ink)]']) }}>
    <span class="size-1 rounded-full bg-ink/40"></span>
    {{ $year }}
    <span class="size-1 rounded-full bg-ink/40"></span>
</span>
