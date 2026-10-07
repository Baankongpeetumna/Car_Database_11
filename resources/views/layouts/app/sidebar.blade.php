<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white dark:bg-zinc-800">
        <flux:sidebar sticky collapsible="mobile" class="border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:sidebar.header>
                <x-app-logo :sidebar="true" href="{{ route('dashboard') }}" wire:navigate />
                <flux:sidebar.collapse class="lg:hidden" />
            </flux:sidebar.header>

            {{-- ป้ายบอกว่ากำลังใช้บัญชี admin --}}
            @if (auth()->user()->isAdmin())
                <div class="px-2">
                    <flux:badge color="red" icon="shield-check" size="sm">
                        Admin account
                    </flux:badge>
                </div>
            @endif

            <flux:sidebar.nav>
                <flux:sidebar.group :heading="__('Platform')" class="grid">
                    <flux:sidebar.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>
                        {{ __('Dashboard') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="shopping-bag" :href="route('products.index')" :current="request()->routeIs('products.*')">
                        Cars
                    </flux:sidebar.item>
                                        @if (auth()->user()->isMember())
                        <flux:sidebar.item
                            icon="shopping-cart"
                            :href="route('cart.index')"
                            :current="request()->routeIs('cart.*')"
                        >
                            My Cart
                        </flux:sidebar.item>
                        <flux:sidebar.item
                            icon="clipboard-document-list"
                            :href="route('orders.index')"
                            :current="request()->routeIs('orders.*')"
                        >
                            My Orders
                        </flux:sidebar.item>
                        <flux:sidebar.item
                            icon="trophy"
                            :href="route('membership.index')"
                            :current="request()->routeIs('membership.*')"
                        >
                            Membership
                        </flux:sidebar.item>
                    @endif
                </flux:sidebar.group>

                {{-- เมนู Admin (หลังร้าน) --}}
                @if (auth()->user()->isAdmin())
                    <flux:sidebar.group heading="Admin Panel" class="grid">
                        <flux:sidebar.item
                            icon="cog"
                            :href="route('admin.dashboard')"
                            :current="request()->routeIs('admin.dashboard')"
                            wire:navigate
                        >
                            Admin Dashboard
                        </flux:sidebar.item>

                        <flux:sidebar.item
                            icon="clipboard-document-list"
                            :href="route('admin.orders.index')"
                            :current="request()->routeIs('admin.orders.*')"
                        >
                            Orders
                        </flux:sidebar.item>

                        {{-- จัดการข้อมูลร้าน (routes/admin-catalog.php) --}}
                        <flux:sidebar.item
                            icon="tag"
                            :href="route('admin.brands.index')"
                            :current="request()->routeIs('admin.brands.*')"
                        >
                            Brands
                        </flux:sidebar.item>

                        <flux:sidebar.item
                            icon="squares-2x2"
                            :href="route('admin.categories.index')"
                            :current="request()->routeIs('admin.categories.*')"
                        >
                            Categories
                        </flux:sidebar.item>

                        <flux:sidebar.item
                            icon="truck"
                            :href="route('admin.cars.index')"
                            :current="request()->routeIs('admin.cars.*')"
                        >
                            Cars
                        </flux:sidebar.item>

                        <flux:sidebar.item
                            icon="trophy"
                            :href="route('admin.tiers.index')"
                            :current="request()->routeIs('admin.tiers.*')"
                        >
                            Membership Tiers
                        </flux:sidebar.item>

                        <flux:sidebar.item
                            icon="star"
                            :href="route('admin.reviews.index')"
                            :current="request()->routeIs('admin.reviews.*')"
                        >
                            Reviews
                        </flux:sidebar.item>

                        <flux:sidebar.item
                            icon="document-text"
                            :href="route('admin.logs.index')"
                            :current="request()->routeIs('admin.logs.*')"
                        >
                            Activity Log
                        </flux:sidebar.item>
                    </flux:sidebar.group>
                @endif
            </flux:sidebar.nav>

            <flux:spacer />

            <flux:sidebar.nav>
                <flux:sidebar.item icon="folder-git-2" href="https://github.com/laravel/livewire-starter-kit" target="_blank">
                    {{ __('Repository') }}
                </flux:sidebar.item>

                <flux:sidebar.item icon="book-open-text" href="https://laravel.com/docs/starter-kits#livewire" target="_blank">
                    {{ __('Documentation') }}
                </flux:sidebar.item>
            </flux:sidebar.nav>

            <x-desktop-user-menu class="hidden lg:block" :name="auth()->user()->name" />
        </flux:sidebar>

        <!-- Mobile User Menu -->
        <flux:header class="lg:hidden">
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

            <flux:spacer />

            <flux:dropdown position="top" align="end">
                <flux:profile
                    :initials="auth()->user()->initials()"
                    icon-trailing="chevron-down"
                />

                <flux:menu>
                    <flux:menu.radio.group>
                        <div class="p-0 text-sm font-normal">
                            <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                <flux:avatar
                                    :name="auth()->user()->name"
                                    :initials="auth()->user()->initials()"
                                />

                                <div class="grid flex-1 text-start text-sm leading-tight">
                                    <flux:heading class="truncate">{{ auth()->user()->name }}</flux:heading>
                                    <flux:text class="truncate">{{ auth()->user()->email }}</flux:text>
                                </div>
                            </div>
                        </div>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <flux:menu.radio.group>
                        <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>
                            {{ __('Settings') }}
                        </flux:menu.item>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item
                            as="button"
                            type="submit"
                            icon="arrow-right-start-on-rectangle"
                            class="w-full cursor-pointer"
                            data-test="logout-button"
                        >
                            {{ __('Log out') }}
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:header>

        {{ $slot }}

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>