<footer id="contact" class="mt-auto bg-ink text-zinc-300">
    <div class="h-1.5 bg-[repeating-linear-gradient(90deg,var(--color-race)_0_40px,var(--color-sun)_40px_52px)]"></div>
    <div class="mx-auto grid max-w-7xl gap-10 px-4 py-12 sm:px-6 md:grid-cols-[1.4fr_1fr_1.2fr] lg:px-8">
        <div>
            <p class="font-display text-3xl font-extrabold italic text-white">VELOCE</p>
            <p class="mt-3 max-w-xs text-sm leading-relaxed text-zinc-400">
                New and quality-checked used cars at honest prices. Join free, earn points and save on every purchase.
            </p>
            <p class="mt-5 inline-flex items-center gap-2 font-display text-xs font-semibold uppercase italic tracking-[0.2em] text-sun">
                <x-store.icon name="flag" class="size-4" /> Your next ride starts here
            </p>
        </div>

        <div>
            <p class="font-display text-sm font-semibold uppercase tracking-wider text-white">Shop</p>
            <ul class="mt-4 space-y-2.5 text-sm">
                <li><a href="{{ route('products.index') }}" class="hover:text-white">All Cars</a></li>
                <li><a href="{{ route('products.index', ['condition' => 'new']) }}" class="hover:text-white">New Cars</a></li>
                <li><a href="{{ route('products.index', ['condition' => 'used']) }}" class="hover:text-white">Used Cars</a></li>
                <li><a href="{{ route('membership.index') }}" class="hover:text-white">VELOCE Club Membership</a></li>
            </ul>
        </div>

        <div>
            <p class="font-display text-sm font-semibold uppercase tracking-wider text-white">Contact</p>
            <ul class="mt-4 space-y-3 text-sm">
                <li class="flex gap-3"><x-store.icon name="map-pin" class="size-4 text-race" /> 88 Nimmanhaemin Rd, Suthep, Mueang, Chiang Mai 50200</li>
                <li class="flex gap-3"><x-store.icon name="phone" class="size-4 text-race" /> 053-000-880</li>
                <li class="flex gap-3"><x-store.icon name="mail" class="size-4 text-race" /> hello@veloce.example</li>
                <li class="flex gap-3"><x-store.icon name="clock" class="size-4 text-race" /> Daily 09:00–18:00</li>
            </ul>
        </div>
    </div>
    <div class="border-t border-white/10">
        <div class="mx-auto flex max-w-7xl flex-wrap justify-between gap-2 px-4 py-5 text-xs text-zinc-500 sm:px-6 lg:px-8">
            <p>© {{ date('Y') }} VELOCE · Database Systems course project</p>
            <p>Cars and prices on this site are for demonstration only.</p>
        </div>
    </div>
</footer>
