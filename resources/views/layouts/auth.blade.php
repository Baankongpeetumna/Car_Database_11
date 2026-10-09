{{-- Layout หน้า login / register / ลืมรหัสผ่าน แบบแบ่งสองฝั่ง --}}
<!DOCTYPE html>
<html lang="en">
    <head>
        @include('partials.store.head')
    </head>
    <body class="min-h-screen bg-paper font-sans text-ink antialiased">
        <div class="grid min-h-screen lg:grid-cols-2">
            {{-- ฝั่งภาพ --}}
            <aside class="relative hidden overflow-hidden bg-ink p-12 text-white lg:flex lg:flex-col">
                <div class="speed-lines absolute inset-0 opacity-70"></div>
                <a href="{{ route('home') }}" class="relative font-display text-3xl font-extrabold italic">VELOCE</a>

                <div class="relative my-auto">
                    <div class="relative">
                        <div class="absolute bottom-5 left-0 right-0 h-3 -skew-x-12 bg-race"></div>
                        <x-store.car-art color="#E5322D" type="coupe" class="relative -rotate-2 drop-shadow-2xl" />
                    </div>
                    <h2 class="mt-10 font-display text-5xl font-extrabold uppercase italic leading-[0.95]">Your next<br>ride starts<br><span class="text-sun">here.</span></h2>
                    <p class="mt-4 max-w-sm text-sm text-zinc-300">VELOCE Club members get tier discounts and earn points on every completed purchase.</p>
                </div>

                <ul class="relative grid grid-cols-3 gap-3 text-xs text-zinc-300">
                    <li class="flex items-center gap-2"><x-store.icon name="shield-check" class="size-4 text-sun" /> Checked before sale</li>
                    <li class="flex items-center gap-2"><x-store.icon name="receipt" class="size-4 text-sun" /> Honest pricing</li>
                    <li class="flex items-center gap-2"><x-store.icon name="coins" class="size-4 text-sun" /> Earn points</li>
                </ul>
            </aside>

            {{-- ฝั่งฟอร์ม --}}
            <main class="flex flex-col px-5 py-8 sm:px-10">
                <div class="flex items-center justify-between">
                    <a href="{{ route('home') }}" class="font-display text-2xl font-extrabold italic text-ink lg:hidden">VELOCE</a>
                    <a href="{{ route('home') }}" class="ml-auto inline-flex items-center gap-1 text-sm font-medium text-zinc-500 hover:text-ink">
                        <x-store.icon name="arrow-left" class="size-4" /> Back to store
                    </a>
                </div>

                <div class="mx-auto my-auto w-full max-w-md py-10">
                    {{ $slot }}
                </div>
            </main>
        </div>

        @fluxScripts
    </body>
</html>
