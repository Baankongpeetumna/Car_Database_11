@extends('layouts.public')

@section('bleed')
    @if ($progress)
        {{-- สมาชิกที่ login: แสดงในหน้าบัญชี --}}
        <x-store.account-shell active="membership" heading="Membership" :subheading="'Earn 1 point for every ฿'.number_format($bahtPerPoint).' of completed orders. Reach a higher tier to get a bigger discount on every order.'">
            @include('membership.partials.progress')
            @include('membership.partials.tiers')
        </x-store.account-shell>
    @else
        <section class="relative overflow-hidden bg-ink text-white">
            <div class="speed-lines absolute inset-0 opacity-60"></div>
            <div class="relative mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8">
                <p class="font-display text-xs font-semibold uppercase italic tracking-[0.2em] text-sun">VELOCE Club</p>
                <h1 class="mt-2 font-display text-5xl font-extrabold uppercase italic leading-none">Membership</h1>
                <p class="mt-4 max-w-xl text-sm text-zinc-300">
                    Earn 1 point for every ฿{{ number_format($bahtPerPoint) }} of completed orders.
                    Reach a higher tier to get a bigger discount on every order.
                </p>
                @guest
                    <div class="mt-6 flex flex-wrap items-center gap-3 text-sm">
                        <a href="{{ route('register') }}" class="rounded-xl bg-race px-5 py-2.5 font-semibold text-white hover:bg-race-dark">Create an account</a>
                        <span class="text-zinc-400">or</span>
                        <a href="{{ route('login') }}" class="font-semibold text-white underline-offset-4 hover:underline">log in</a>
                        <span class="text-zinc-400">to start earning points.</span>
                    </div>
                @endguest
            </div>
        </section>

        <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
            @include('membership.partials.tiers')
        </div>
    @endif
@endsection
