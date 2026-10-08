<x-layouts::app.sidebar :title="$title ?? null">
    <flux:main class="!p-0 bg-zinc-50 dark:bg-zinc-950">
        {{ $slot }}
    </flux:main>
</x-layouts::app.sidebar>