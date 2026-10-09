@php
    $user = auth()->user();
    $isAdmin = $user?->isAdmin() ?? false;
    $isMember = $user?->isMember() ?? false;

    $cartCount = 0;
    if ($isMember) {
        $cartCount = (int) ($user->cart?->cars()->sum('CART_ITEM.quantity') ?? 0);
    }

    $condition = request('condition');
    $onProducts = request()->routeIs('products.index');

    $links = [
        ['label' => 'Home', 'href' => route('home'), 'active' => request()->routeIs('home')],
        ['label' => 'All Cars', 'href' => route('products.index'), 'active' => $onProducts && ! in_array($condition, ['new', 'used'], true)],
        ['label' => 'New', 'href' => route('products.index', ['condition' => 'new']), 'active' => $onProducts && $condition === 'new'],
        ['label' => 'Used', 'href' => route('products.index', ['condition' => 'used']), 'active' => $onProducts && $condition === 'used'],
        ['label' => 'Membership', 'href' => route('membership.index'), 'active' => request()->routeIs('membership.*')],
    ];
@endphp

<header class="sticky top-0 z-40 border-b border-line bg-white/90 backdrop-blur">
    <div class="mx-auto flex h-16 max-w-7xl items-center gap-6 px-4 sm:px-6 lg:px-8">
        {{-- โลโก้ --}}
        <a href="{{ route('home') }}" class="flex items-center gap-2" aria-label="VELOCE home">
            <span class="font-display text-2xl font-extrabold italic tracking-tight text-ink">VELOCE</span>
            <span class="hidden h-4 w-6 -skew-x-12 bg-race sm:inline-block"></span>
        </a>

        {{-- เมนูหลัก (จอใหญ่) --}}
        <nav class="hidden flex-1 items-center justify-center gap-1 md:flex" aria-label="Main menu">
            @foreach ($links as $link)
                <a href="{{ $link['href'] }}"
                   @class([
                       'relative rounded-lg px-3 py-2 text-sm font-medium transition',
                       'text-race after:absolute after:inset-x-3 after:-bottom-[13px] after:h-0.5 after:bg-race' => $link['active'],
                       'text-zinc-600 hover:text-ink' => ! $link['active'],
                   ])
                   @if ($link['active']) aria-current="page" @endif>
                    {{ $link['label'] }}
                </a>
            @endforeach
        </nav>

        <div class="ml-auto flex items-center gap-1.5 md:ml-0">
            <a href="{{ route('products.index') }}#search" class="rounded-full p-2 text-zinc-600 hover:bg-paper hover:text-ink" aria-label="Search cars">
                <x-store.icon name="search" />
            </a>

            @if (! $isAdmin)
                <a href="{{ $isMember ? route('cart.index') : route('login') }}" class="relative rounded-full p-2 text-zinc-600 hover:bg-paper hover:text-ink" aria-label="Cart ({{ $cartCount }})">
                    <x-store.icon name="cart" />
                    @if ($cartCount > 0)
                        <span class="absolute -right-0.5 -top-0.5 flex size-[18px] items-center justify-center rounded-full bg-race text-[10px] font-bold text-white">{{ $cartCount > 9 ? '9+' : $cartCount }}</span>
                    @endif
                </a>
            @endif

            @guest
                <a href="{{ route('login') }}" class="ml-1 hidden rounded-xl border border-line px-4 py-2 text-sm font-semibold text-ink hover:border-ink sm:inline-flex">Log in</a>
                <a href="{{ route('register') }}" class="hidden rounded-xl bg-race px-4 py-2 text-sm font-semibold text-white hover:bg-race-dark lg:inline-flex">Sign up</a>
            @endguest

            @auth
                @if ($isAdmin)
                    <a href="{{ route('admin.dashboard') }}" class="ml-1 hidden items-center gap-1.5 rounded-xl bg-ink px-3.5 py-2 text-sm font-semibold text-white hover:bg-ink-soft sm:inline-flex">
                        <x-store.icon name="shield" class="size-4 text-sun" />
                        Admin Panel
                    </a>
                @endif

                {{-- เมนูบัญชีผู้ใช้ --}}
                <details class="store-dropdown relative ml-1 hidden sm:block">
                    <summary class="flex cursor-pointer list-none items-center gap-2 rounded-full border border-line py-1 pl-1 pr-2.5 hover:border-ink [&::-webkit-details-marker]:hidden">
                        <span class="flex size-8 items-center justify-center rounded-full bg-ink font-display text-xs font-semibold text-white">{{ $user->initials() }}</span>
                        <span class="hidden max-w-[8rem] truncate text-sm font-medium text-ink lg:block">{{ $user->first_name }}</span>
                        <x-store.icon name="chevron-down" class="size-4 text-zinc-500" />
                    </summary>

                    <div class="absolute right-0 mt-2 w-64 overflow-hidden rounded-2xl border border-line bg-white shadow-xl">
                        <div class="border-b border-line bg-paper px-4 py-3">
                            <p class="truncate text-sm font-semibold text-ink">{{ $user->name }}</p>
                            <p class="truncate text-xs text-zinc-500">{{ $user->email }}</p>
                            @if ($isMember && $user->tier)
                                <p class="mt-2 inline-flex items-center gap-1 rounded-full bg-sun px-2 py-0.5 text-[11px] font-semibold text-ink">
                                    <x-store.icon name="crown" class="size-3" />
                                    {{ $user->tier->tier_name }} · {{ number_format($user->points) }} pts
                                </p>
                            @elseif ($isAdmin)
                                <p class="mt-2 inline-flex items-center gap-1 rounded-full bg-ink px-2 py-0.5 text-[11px] font-semibold text-white">Administrator</p>
                            @endif
                        </div>
                        <div class="p-1.5 text-sm">
                            @if ($isAdmin)
                                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2.5 rounded-lg px-3 py-2 text-ink hover:bg-paper">
                                    <x-store.icon name="grid" class="size-4 text-zinc-500" /> Admin Panel
                                </a>
                            @endif
                            <a href="{{ route('profile.edit') }}" class="flex items-center gap-2.5 rounded-lg px-3 py-2 text-ink hover:bg-paper">
                                <x-store.icon name="user" class="size-4 text-zinc-500" /> My Profile
                            </a>
                            @if ($isMember)
                                <a href="{{ route('orders.index') }}" class="flex items-center gap-2.5 rounded-lg px-3 py-2 text-ink hover:bg-paper">
                                    <x-store.icon name="clipboard" class="size-4 text-zinc-500" /> My Orders
                                </a>
                            @endif
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="flex w-full items-center gap-2.5 rounded-lg px-3 py-2 text-left text-race hover:bg-race-soft">
                                    <x-store.icon name="logout" class="size-4" /> Log out
                                </button>
                            </form>
                        </div>
                    </div>
                </details>
            @endauth

            {{-- เมนูมือถือ --}}
            <details class="store-dropdown md:hidden">
                <summary class="list-none rounded-full p-2 text-ink hover:bg-paper [&::-webkit-details-marker]:hidden" aria-label="Open menu">
                    <x-store.icon name="menu" />
                </summary>
                <div class="absolute inset-x-0 top-16 border-b border-line bg-white px-4 pb-5 pt-2 shadow-lg">
                    <nav class="grid gap-1" aria-label="Mobile menu">
                        @foreach ($links as $link)
                            <a href="{{ $link['href'] }}" @class(['rounded-xl px-3 py-2.5 text-sm font-medium', 'bg-race-soft text-race' => $link['active'], 'text-ink hover:bg-paper' => ! $link['active']])>{{ $link['label'] }}</a>
                        @endforeach
                    </nav>
                    <div class="mt-3 grid gap-2 border-t border-line pt-3">
                        @guest
                            <a href="{{ route('login') }}" class="rounded-xl border border-line px-4 py-2.5 text-center text-sm font-semibold text-ink">Log in</a>
                            <a href="{{ route('register') }}" class="rounded-xl bg-race px-4 py-2.5 text-center text-sm font-semibold text-white">Sign up</a>
                        @endguest
                        @auth
                            @if ($isAdmin)
                                <a href="{{ route('admin.dashboard') }}" class="rounded-xl bg-ink px-4 py-2.5 text-center text-sm font-semibold text-white">Admin Panel</a>
                            @endif
                            <a href="{{ route('profile.edit') }}" class="rounded-xl px-3 py-2.5 text-sm text-ink hover:bg-paper">My Profile</a>
                            @if ($isMember)
                                <a href="{{ route('orders.index') }}" class="rounded-xl px-3 py-2.5 text-sm text-ink hover:bg-paper">My Orders</a>
                            @endif
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="w-full rounded-xl px-3 py-2.5 text-left text-sm text-race hover:bg-race-soft">Log out</button>
                            </form>
                        @endauth
                    </div>
                </div>
            </details>
        </div>
    </div>
</header>
