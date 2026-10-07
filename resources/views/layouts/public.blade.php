<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white text-zinc-900 dark:bg-zinc-900 dark:text-zinc-100">
        <header class="border-b border-zinc-200 dark:border-zinc-700">
            <div class="mx-auto flex max-w-6xl items-center justify-between px-4 py-3">
                <a href="{{ route('home') }}" class="font-semibold">
                    {{ config('app.name', 'Laravel') }}
                </a>

                <nav class="flex items-center gap-4 text-sm">
                    <a href="{{ route('products.index') }}">Cars</a>
                    <a href="{{ route('membership.index') }}">Membership</a>
                    @auth
    @if (auth()->user()->isMember())
        <a href="{{ route('cart.index') }}">
            My Cart
        </a>

        <a href="{{ route('orders.index') }}">
            My Orders
        </a>
    @endif

    {{-- admin: ลิงก์เข้าหลังร้าน + ป้ายบอกว่าเป็นบัญชี admin --}}
    @if (auth()->user()->isAdmin())
        <a href="{{ route('admin.dashboard') }}">
            Admin Panel
        </a>

        <span class="rounded-full bg-red-100 px-2 py-0.5 text-xs font-semibold text-red-700 dark:bg-red-900/40 dark:text-red-300">
            🛡️ Admin
        </span>
    @endif
@endauth

                    @auth
                        <a href="{{ route('dashboard') }}">Dashboard</a>
                    @else
                        <a href="{{ route('login') }}">Log in</a>
                        <a href="{{ route('register') }}">Register</a>
                    @endauth
                </nav>
            </div>
        </header>

        <main class="mx-auto max-w-6xl px-4 py-8">
            @yield('content')
        </main>
    </body>
</html>