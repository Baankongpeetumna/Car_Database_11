{{-- Render inside an element using x-data="brandForm()" (see _state). Pass suggest => true on the Add page. --}}
@php $suggest = $suggest ?? false; @endphp

{{-- Brand name --}}
<div>
    <label for="brand_name" class="text-sm font-semibold text-zinc-900 dark:text-white">Brand name</label>

    <div class="relative mt-2">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
             stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"
             class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-zinc-400">
            <path d="M12.586 2.586A2 2 0 0 0 11.172 2H4a2 2 0 0 0-2 2v7.172a2 2 0 0 0 .586 1.414l8.704 8.704a2.426 2.426 0 0 0 3.42 0l6.58-6.58a2.426 2.426 0 0 0 0-3.42z"/>
            <circle cx="7.5" cy="7.5" r=".5" fill="currentColor"/>
        </svg>

        <input id="brand_name" name="brand_name" type="text" x-model="name" maxlength="255" required
               placeholder="e.g. Toyota" autocomplete="off" @if ($suggest) autofocus @endif
               class="h-14 w-full rounded-lg border bg-zinc-50 pl-12 pr-14 text-lg font-semibold text-zinc-900
                      placeholder:font-normal placeholder:text-zinc-400 transition focus:outline-none focus:ring-4
                      dark:bg-zinc-800/60 dark:text-white"
               x-bind:class="{
                   'border-red-500 focus:border-red-500 focus:ring-red-500/20': state === 'duplicate' || state === 'toolong',
                   'border-emerald-500 focus:border-emerald-500 focus:ring-emerald-500/20': match === null && key !== '' && state !== 'toolong',
                   'border-zinc-300 focus:border-red-500 focus:ring-red-500/20 dark:border-zinc-700': key === ''
               }">

        <span x-show="key !== '' && !match && state !== 'toolong'" x-cloak
              class="absolute right-4 top-1/2 grid size-7 -translate-y-1/2 -skew-x-12 place-items-center rounded-md bg-emerald-500 text-sm font-bold text-white">
            <span class="skew-x-12">✓</span>
        </span>
        <span x-show="state === 'duplicate' || state === 'toolong'" x-cloak
              class="absolute right-4 top-1/2 grid size-7 -translate-y-1/2 -skew-x-12 place-items-center rounded-md bg-red-500 text-sm font-bold text-white">
            <span class="skew-x-12">!</span>
        </span>
    </div>

    <div class="mt-2 flex items-start justify-between gap-4">
        <div class="min-h-5 flex-1 text-sm">
            <div x-show="state === 'duplicate'" x-cloak
                 class="flex flex-wrap items-center gap-x-2 gap-y-1 rounded-lg bg-red-500/10 px-3 py-2 text-red-600 dark:text-red-400">
                <span>
                    <strong x-text="match && match.name"></strong> already exists
                    <span class="text-red-500/80"
                          x-text="match ? '(Brand #' + match.id + ' · ' + (match.country || 'no country') + ' · ' + match.count + (match.count === 1 ? ' model' : ' models') + ')' : ''"></span>.
                </span>
                <a x-bind:href="match ? match.url : '#'" class="font-semibold underline underline-offset-2 hover:text-red-700">
                    Edit that one instead →
                </a>
            </div>

            <p x-show="state === 'toolong'" x-cloak class="text-red-600 dark:text-red-400">Keep the name under 255 characters.</p>

            @error('brand_name')
                <p class="mt-1 text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <span class="shrink-0 text-xs tabular-nums text-zinc-400" x-text="name.length + ' / 255'"></span>
    </div>
</div>


{{-- Country --}}
<div>
    <label for="country" class="text-sm font-semibold text-zinc-900 dark:text-white">Country</label>

    <div class="relative mt-2">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
             stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"
             class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-zinc-400">
            <circle cx="12" cy="12" r="10"/><path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20"/><path d="M2 12h20"/>
        </svg>

        <input id="country" name="country" type="text" x-model="country" maxlength="100" required
               placeholder="e.g. Japan" autocomplete="off"
               class="h-14 w-full rounded-lg border border-zinc-300 bg-zinc-50 pl-12 pr-16 text-lg text-zinc-900
                      placeholder:text-zinc-400 transition focus:border-red-500 focus:outline-none focus:ring-4 focus:ring-red-500/20
                      dark:border-zinc-700 dark:bg-zinc-800/60 dark:text-white">

        <span x-show="ckey !== ''" x-cloak
              class="absolute right-4 top-1/2 -translate-y-1/2 -skew-x-12 rounded-md bg-red-600 px-2.5 py-1 text-xs font-bold tracking-wider text-white">
            <span class="inline-block skew-x-12" x-text="code(country)"></span>
        </span>
    </div>

    <div class="mt-2 min-h-5 text-sm">
        <p x-show="ckey !== '' && !isNewCountry" x-cloak class="text-zinc-500">
            Joins <strong class="text-zinc-900 dark:text-white" x-text="country.trim()"></strong>
            with <span x-text="sameCountry.length"></span> other <span x-text="sameCountry.length === 1 ? 'brand' : 'brands'"></span>.
        </p>
        <p x-show="isNewCountry" x-cloak class="text-amber-600 dark:text-amber-400">
            New country. A <strong x-text="country.trim()"></strong> group will be created in the directory.
        </p>
        @error('country')
            <p class="mt-1 text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>

    <div class="mt-3 flex flex-wrap gap-2">
        <template x-for="c in countryChips" x-bind:key="c.name">
            <button type="button" x-on:click="country = c.name"
                    class="inline-flex items-center gap-2 rounded-md border px-3 py-1.5 text-sm font-medium transition"
                    x-bind:class="ckey === c.name.toLowerCase()
                        ? 'border-red-600 bg-red-600 text-white shadow-md shadow-red-600/30'
                        : (c.count > 0
                            ? 'border-zinc-300 text-zinc-700 hover:border-red-500 hover:text-red-600 dark:border-zinc-700 dark:text-zinc-300'
                            : 'border-dashed border-zinc-300 text-zinc-500 hover:border-red-500 hover:text-red-600 dark:border-zinc-700')">
                <span class="rounded-sm bg-zinc-200/80 px-1.5 text-[10px] font-bold tracking-wider text-zinc-600 dark:bg-zinc-700 dark:text-zinc-200"
                      x-text="code(c.name)"></span>
                <span x-text="c.name"></span>
                <span x-show="c.count > 0" x-cloak class="text-xs opacity-70" x-text="c.count"></span>
            </button>
        </template>
    </div>
</div>


{{-- Popular makes: fills name + country; the ones you already have are crossed out --}}
@if ($suggest)
    <div>
        <div class="flex items-center justify-between gap-4">
            <p class="text-xs font-medium uppercase tracking-wider text-zinc-500">Popular makes</p>
            <p class="text-xs text-zinc-500">
                <span class="font-semibold text-zinc-900 dark:text-white" x-text="makesAdded"></span>
                of <span x-text="makes.length"></span> already added
            </p>
        </div>

        <div class="mt-2 h-1 overflow-hidden rounded-full bg-zinc-200 dark:bg-zinc-800">
            <div class="h-full rounded-full bg-emerald-500 transition-all duration-500"
                 x-bind:style="'width:' + (makesAdded / makes.length * 100) + '%'"></div>
        </div>

        <div class="mt-3 flex flex-wrap gap-2">
            <template x-for="m in makes" x-bind:key="m.n">
                <button type="button" x-on:click="pickMake(m)" x-bind:disabled="has(m)"
                        x-bind:title="has(m) ? m.n + ' already exists' : 'Use ' + m.n + ' (' + m.c + ')'"
                        class="inline-flex items-center gap-1.5 rounded-md border px-3 py-1.5 text-sm font-semibold transition"
                        x-bind:class="has(m)
                            ? 'cursor-not-allowed border-dashed border-zinc-300 text-zinc-400 line-through decoration-zinc-400/60 dark:border-zinc-700 dark:text-zinc-600'
                            : (key === m.n.toLowerCase()
                                ? 'border-red-600 bg-red-600 text-white shadow-md shadow-red-600/30'
                                : 'border-zinc-300 text-zinc-700 hover:-translate-y-0.5 hover:border-red-500 hover:text-red-600 dark:border-zinc-700 dark:text-zinc-300')">
                    <span x-show="has(m)" x-cloak class="text-emerald-500 no-underline">✓</span>
                    <span x-text="m.n"></span>
                    <span x-show="has(m)" x-cloak class="text-[10px] uppercase tracking-wide no-underline">added</span>
                </button>
            </template>
        </div>
    </div>
@endif