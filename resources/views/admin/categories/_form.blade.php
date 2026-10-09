{{-- Must be rendered inside an element using x-data="@include('admin.categories._state', ...)" --}}
@php $suggest = $suggest ?? false; @endphp

<div>
    <label for="category_name" class="text-sm font-semibold text-zinc-900 dark:text-white">Category name</label>

    <div class="relative mt-2">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
             stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"
             class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-zinc-400">
            <rect width="7" height="7" x="3" y="3" rx="1"/><rect width="7" height="7" x="14" y="3" rx="1"/>
            <rect width="7" height="7" x="14" y="14" rx="1"/><rect width="7" height="7" x="3" y="14" rx="1"/>
        </svg>

        <input id="category_name" name="category_name" type="text" x-model="name" maxlength="255"
               placeholder="e.g. Sedan" autocomplete="off" required @if ($suggest) autofocus @endif
               class="h-14 w-full rounded-xl border bg-zinc-50 pl-12 pr-14 text-lg text-zinc-900 placeholder:text-zinc-400
                      transition focus:outline-none focus:ring-4 dark:bg-zinc-800/60 dark:text-white"
               x-bind:class="{
                   'border-red-500 focus:border-red-500 focus:ring-red-500/20': state === 'duplicate' || state === 'toolong',
                   'border-emerald-500 focus:border-emerald-500 focus:ring-emerald-500/20': state === 'ok',
                   'border-zinc-300 focus:border-red-500 focus:ring-red-500/20 dark:border-zinc-700': state === 'empty' || state === 'unchanged'
               }">

        {{-- status icon --}}
        <span x-show="state === 'ok'" x-cloak
              class="absolute right-4 top-1/2 grid size-7 -translate-y-1/2 place-items-center rounded-full bg-emerald-500 text-sm font-bold text-white">✓</span>
        <span x-show="state === 'duplicate' || state === 'toolong'" x-cloak
              class="absolute right-4 top-1/2 grid size-7 -translate-y-1/2 place-items-center rounded-full bg-red-500 text-sm font-bold text-white">!</span>
    </div>

    <div class="mt-2 flex items-start justify-between gap-4 text-xs">
        <div class="min-h-5 flex-1">
            {{-- Duplicate: say exactly which one it clashes with, and offer a way out --}}
            <div x-show="state === 'duplicate'" x-cloak
                 class="flex flex-wrap items-center gap-x-2 gap-y-1 rounded-lg bg-red-500/10 px-3 py-2 text-sm text-red-600 dark:text-red-400">
                <span>
                    <strong x-text="match && match.name"></strong> already exists
                    <span class="text-red-500/80"
                          x-text="match ? '(#' + match.id + ' · ' + match.count + (match.count === 1 ? ' model' : ' models') + ')' : ''"></span>.
                </span>
                <a x-bind:href="match ? match.url : '#'" class="font-semibold underline underline-offset-2 hover:text-red-700">
                    Edit that one instead →
                </a>
            </div>

            <p x-show="state === 'ok' && similar.length === 0" x-cloak class="text-sm text-emerald-600 dark:text-emerald-400">
                <strong x-text="name.trim()"></strong> is available.
            </p>

            <p x-show="state === 'ok' && similar.length > 0" x-cloak class="text-sm text-amber-600 dark:text-amber-400">
                Available, but similar to
                <strong x-text="similar.map(s => s.name).join(', ')"></strong>. Is it really a new category?
            </p>

            <p x-show="state === 'unchanged'" x-cloak class="text-sm text-zinc-500">This is the current name.</p>
            <p x-show="state === 'toolong'" x-cloak class="text-sm text-red-600 dark:text-red-400">Keep it under 255 characters.</p>

            @error('category_name')
                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <span class="shrink-0 tabular-nums text-zinc-400" x-text="name.length + ' / ' + max"></span>
    </div>
</div>

@if ($suggest)
    <div>
        <div class="flex items-center justify-between gap-4">
            <p class="text-xs font-medium uppercase tracking-wider text-zinc-500">Quick picks</p>
            <p class="text-xs text-zinc-500">
                <span class="font-semibold text-zinc-900 dark:text-white" x-text="picksAdded"></span>
                of <span x-text="picks.length"></span> already added
            </p>
        </div>

        <div class="mt-2 h-1 overflow-hidden rounded-full bg-zinc-200 dark:bg-zinc-800">
            <div class="h-full rounded-full bg-emerald-500 transition-all duration-500"
                 x-bind:style="'width:' + (picksAdded / picks.length * 100) + '%'"></div>
        </div>

        <div class="mt-3 flex flex-wrap gap-2">
            <template x-for="p in picks" x-bind:key="p">
                <button type="button" x-on:click="pick(p)" x-bind:disabled="has(p)"
                        x-bind:title="has(p) ? p + ' already exists' : 'Use ' + p"
                        class="inline-flex items-center gap-1.5 rounded-full border px-3.5 py-1.5 text-sm font-medium transition"
                        x-bind:class="has(p)
                            ? 'cursor-not-allowed border-dashed border-zinc-300 text-zinc-400 line-through decoration-zinc-400/60 dark:border-zinc-700 dark:text-zinc-600'
                            : (key === p.toLowerCase()
                                ? 'border-red-600 bg-red-600 text-white shadow-md shadow-red-600/30'
                                : 'border-zinc-300 text-zinc-700 hover:-translate-y-0.5 hover:border-red-500 hover:text-red-600 dark:border-zinc-700 dark:text-zinc-300')">
                    <span x-show="has(p)" x-cloak class="text-emerald-500 no-underline">✓</span>
                    <span x-text="p"></span>
                    <span x-show="has(p)" x-cloak class="text-[10px] uppercase tracking-wide no-underline">added</span>
                </button>
            </template>
        </div>

        <p class="mt-3 text-xs text-zinc-500">Greyed-out picks already exist in your showroom, so only new ones can be selected.</p>
    </div>
@endif