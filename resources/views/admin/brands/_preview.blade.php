{{-- Live preview: looks like a row in the brands directory. Render inside the brandForm() scope. --}}
<div class="overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
    <div class="flex items-center justify-between px-5 pt-5">
        <p class="text-sm font-medium text-zinc-500">Live preview</p>
        <span class="flex items-center gap-1.5 text-xs text-zinc-500">
            <span class="size-2 animate-pulse rounded-full bg-emerald-500"></span> updates as you type
        </span>
    </div>

    <div class="p-5">
        <div class="relative overflow-hidden rounded-xl border bg-zinc-50 py-4 pl-6 pr-4 transition dark:bg-zinc-800/50"
             x-bind:class="state === 'duplicate' ? 'border-red-500/60' : 'border-zinc-200 dark:border-zinc-800'">

            <span class="absolute inset-y-0 left-0 w-1.5 transition"
                  x-bind:class="state === 'duplicate' ? 'bg-red-500' : 'bg-red-600'"></span>

            <div class="flex items-start justify-between gap-3">
                <p class="truncate text-[11px] font-semibold uppercase tracking-wider text-zinc-400"
                   x-text="(country.trim() || 'Country') + ' · ' + pad(sameCountry.length + 1) + (sameCountry.length === 0 ? ' brand' : ' brands')"></p>

                <span class="shrink-0 -skew-x-12 rounded-sm bg-red-600 px-2 py-0.5 text-[11px] font-bold tracking-wider text-white">
                    <span class="inline-block skew-x-12" x-text="code(country)"></span>
                </span>
            </div>

            <p class="font-display mt-2 truncate text-3xl font-black uppercase italic leading-none tracking-tight text-zinc-900 dark:text-white"
               x-text="name.trim() || 'Brand name'">Brand name</p>

            <p class="mt-1 text-xs text-zinc-400" x-text="'Brand #' + (selfId || nextId || '')"></p>

            <div class="mt-4 flex items-center justify-between gap-3">
                <div class="flex items-center gap-1" aria-hidden="true">
                    <template x-if="models === 0">
                        <span class="h-2.5 w-5 -skew-x-12 border border-dashed border-zinc-300 dark:border-zinc-600"></span>
                    </template>
                    <template x-for="t in ticks" x-bind:key="t">
                        <span class="h-2.5 w-5 -skew-x-12 bg-red-600"></span>
                    </template>
                </div>
                <span class="text-xs font-medium uppercase tracking-wide text-zinc-400"
                      x-text="models === 0 ? 'No models' : models + (models === 1 ? ' model' : ' models')"></span>
            </div>
        </div>

        <p class="mt-3 text-xs text-zinc-500">
            How it will appear in the brands directory, under its country.
        </p>
    </div>
</div>