@props(['active' => 'profile', 'heading' => null, 'subheading' => null])

{{-- โครงหน้าบัญชีผู้ใช้: หัวโปรไฟล์ + แท็บ ข้อมูลส่วนตัว / ระดับสมาชิก / ประวัติสั่งซื้อ / ความปลอดภัย --}}
@php
    $user = auth()->user();
    $tabs = [
        ['profile', 'Profile', 'user', route('profile.edit')],
    ];
    if ($user->isMember()) {
        $tabs[] = ['membership', 'Membership', 'crown', route('membership.index')];
        $tabs[] = ['orders', 'My Orders', 'clipboard', route('orders.index')];
    }
    $tabs[] = ['security', 'Security', 'lock', route('security.edit')];
@endphp

<div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
    <section class="relative overflow-hidden rounded-3xl bg-ink px-6 py-7 text-white">
        <div class="speed-lines absolute inset-0 opacity-60"></div>
        <div class="relative flex flex-wrap items-center gap-5">
            <span class="flex size-16 items-center justify-center rounded-2xl bg-race font-display text-2xl font-bold">{{ $user->initials() }}</span>
            <div class="min-w-0 flex-1">
                <p class="font-display text-2xl font-bold">{{ $user->name }}</p>
                <p class="truncate text-sm text-zinc-300">{{ $user->email }} · Member since {{ $user->created_at?->format('d/m/Y') ?? '-' }}</p>
            </div>
            @if ($user->isMember() && $user->tier)
                <div class="rounded-2xl bg-white/10 px-4 py-3 text-right ring-1 ring-white/15">
                    <p class="flex items-center justify-end gap-1.5 font-display text-lg font-bold text-sun"><x-store.icon name="crown" class="size-4" /> {{ $user->tier->tier_name }}</p>
                    <p class="text-xs text-zinc-300">{{ number_format($user->points) }} points</p>
                </div>
            @elseif ($user->isAdmin())
                <span class="rounded-full bg-white/10 px-3 py-1.5 text-xs font-semibold ring-1 ring-white/15">Administrator</span>
            @endif
        </div>
    </section>

    <div class="mt-6 grid gap-6 lg:grid-cols-[240px_1fr] lg:items-start">
        <nav class="flex gap-2 overflow-x-auto rounded-2xl border border-line bg-white p-2 lg:flex-col" aria-label="Account menu">
            @foreach ($tabs as [$key, $label, $icon, $href])
                <a href="{{ $href }}" @class([
                    'flex shrink-0 items-center gap-2.5 rounded-xl px-3.5 py-2.5 text-sm font-medium transition',
                    'bg-race text-white' => $active === $key,
                    'text-ink hover:bg-paper' => $active !== $key,
                ]) @if ($active === $key) aria-current="page" @endif>
                    <x-store.icon :name="$icon" class="size-4" /> {{ $label }}
                </a>
            @endforeach
        </nav>

        <div class="min-w-0">
            @if ($heading)
                <div class="mb-5">
                    <h1 class="font-display text-3xl font-extrabold uppercase italic leading-none text-ink">{{ $heading }}</h1>
                    @if ($subheading)
                        <p class="mt-1.5 text-sm text-zinc-500">{{ $subheading }}</p>
                    @endif
                </div>
            @endif
            {{ $slot }}
        </div>
    </div>
</div>
