{{-- Must be rendered inside the x-data scope from _state --}}
<div class="overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
    <div class="flex items-center justify-between px-5 pt-5">
        <p class="text-sm font-medium text-zinc-500">Already in your showroom</p>
        <span class="rounded-full bg-red-500/10 px-2.5 py-0.5 text-xs font-semibold text-red-500" x-text="list.length"></span>
    </div>

    <ul class="mt-3 max-h-72 divide-y divide-zinc-100 overflow-y-auto border-t border-zinc-100 dark:divide-zinc-800 dark:border-zinc-800">
        <template x-for="c in list" x-bind:key="c.id">
            <li>
                <a x-bind:href="c.url"
                   class="flex items-center gap-3 px-5 py-3 transition hover:bg-zinc-50 dark:hover:bg-zinc-800/50"
                   x-bind:class="{
                       'bg-red-500/10 ring-1 ring-inset ring-red-500/50': flag(c) === 'same',
                       'bg-amber-500/10': flag(c) === 'similar',
                       'bg-zinc-100 dark:bg-zinc-800/70': flag(c) === 'self'
                   }">
                    <span class="grid size-9 shrink-0 place-items-center rounded-lg text-sm font-bold"
                          x-bind:class="c.tone" x-text="c.name.charAt(0).toUpperCase()"></span>

                    <span class="min-w-0 flex-1">
                        <span class="block truncate text-sm font-semibold text-zinc-900 dark:text-white" x-text="c.name"></span>
                        <span class="text-[11px] text-zinc-500" x-text="'#' + c.id"></span>
                    </span>

                    <span x-show="flag(c) === 'same'" x-cloak
                          class="rounded-full bg-red-500 px-2 py-0.5 text-[10px] font-bold uppercase text-white">Same name</span>
                    <span x-show="flag(c) === 'similar'" x-cloak
                          class="rounded-full bg-amber-500 px-2 py-0.5 text-[10px] font-bold uppercase text-white">Similar</span>
                    <span x-show="flag(c) === 'self'" x-cloak
                          class="rounded-full bg-zinc-500 px-2 py-0.5 text-[10px] font-bold uppercase text-white">Editing</span>

                    <span class="w-12 shrink-0 text-right">
                        <span class="block text-sm font-bold tabular-nums text-zinc-900 dark:text-white" x-text="c.count"></span>
                        <span class="text-[10px] text-zinc-500" x-text="c.count === 1 ? 'model' : 'models'"></span>
                    </span>
                </a>
            </li>
        </template>

        <li x-show="list.length === 0" x-cloak class="px-5 py-8 text-center text-sm text-zinc-500">
            No categories yet. This will be your first.
        </li>
    </ul>

    <p class="border-t border-zinc-100 px-5 py-3 text-xs text-zinc-500 dark:border-zinc-800">
        Type a name and matching rows light up, so you can spot a duplicate before saving.
    </p>
</div>