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