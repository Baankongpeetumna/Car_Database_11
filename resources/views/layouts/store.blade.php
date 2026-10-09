{{-- Layout หน้าร้าน (component) ใช้ได้ทั้ง <x-layouts::store> และหน้า Livewire ผ่าน #[Layout('layouts::store')] --}}
<!DOCTYPE html>
<html lang="en">
    <head>
        @include('partials.store.head')
    </head>
    <body class="flex min-h-screen flex-col bg-paper font-sans text-ink antialiased">
        <a href="#main" class="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-50 focus:rounded-lg focus:bg-white focus:px-4 focus:py-2">Skip to content</a>

        @include('partials.store.nav')

        <main id="main" class="flex-1">
            {{ $slot }}
        </main>

        @include('partials.store.footer')

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        <script>
            // ปิด dropdown (<details>) เมื่อคลิกที่อื่น หรือกด Esc
            document.addEventListener('click', (event) => {
                document.querySelectorAll('details.store-dropdown[open]').forEach((el) => {
                    if (! el.contains(event.target)) el.removeAttribute('open');
                });
            });
            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape') {
                    document.querySelectorAll('details.store-dropdown[open]').forEach((el) => el.removeAttribute('open'));
                }
            });
        </script>
        @stack('scripts')
        @fluxScripts
    </body>
</html>
