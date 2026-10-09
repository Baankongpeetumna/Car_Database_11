<x-layouts::app.sidebar :title="$title ?? null">
    {{-- Admins get the slim icon rail on desktop, so keep their content clear of it (lg:!ps-16) --}}
    <flux:main :class="'!p-0 bg-zinc-50 dark:bg-zinc-950 ' . (auth()->user()?->isAdmin() ? 'lg:!ps-16' : '')">
        {{ $slot }}
    </flux:main>
</x-layouts::app.sidebar>