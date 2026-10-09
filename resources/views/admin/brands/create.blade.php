<x-layouts::app :title="'Add Brand'">
    @php
        $nextId = (int) (\App\Models\Brand::max('brand_id') ?? 0) + 1;
    @endphp

    <div class="relative min-h-full overflow-hidden bg-zinc-50 dark:bg-zinc-950">

        {{-- Background decoration --}}
        <div aria-hidden="true"
             class="pointer-events-none absolute inset-x-0 top-0 hidden h-80 dark:block
                    bg-[radial-gradient(ellipse_at_top,rgba(220,38,38,0.18),transparent_65%)]"></div>
        <div aria-hidden="true"
             class="pointer-events-none absolute inset-x-0 top-0 h-96 opacity-70
                    bg-[radial-gradient(rgba(0,0,0,0.08)_1px,transparent_1px)]
                    dark:bg-[radial-gradient(rgba(255,255,255,0.07)_1px,transparent_1px)]
                    [background-size:22px_22px] [mask-image:linear-gradient(to_bottom,black,transparent)]"></div>
        <div aria-hidden="true"
             class="pointer-events-none absolute right-8 top-6 hidden flex-col items-end gap-1.5 lg:flex">
            <span class="h-2 w-28 -skew-x-12 bg-red-600/70"></span>
            <span class="h-2 w-20 -skew-x-12 bg-red-600/45"></span>
            <span class="h-2 w-12 -skew-x-12 bg-red-600/25"></span>
        </div>

        <div class="relative mx-auto max-w-6xl space-y-6 p-4 sm:p-6">

            @include('commerce.messages')

            {{-- Header --}}
            <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <div class="flex items-center gap-2 text-sm text-zinc-400">
                        <a href="{{ route('admin.dashboard') }}" class="hover:text-red-600">Admin</a>
                        <span>/</span>
                        <a href="{{ route('admin.brands.index') }}" class="hover:text-red-600">Brands</a>
                        <span>/</span>
                        <span class="text-zinc-600 dark:text-zinc-300">New</span>
                    </div>

                    <h1 class="font-display mt-1 text-4xl font-extrabold italic uppercase tracking-tight
                               text-zinc-900 sm:text-5xl dark:text-white">
                        New brand
                    </h1>
                    <div class="mt-2 h-1 w-14 -skew-x-12 bg-red-600"></div>
                </div>

                <a href="{{ route('admin.brands.index') }}"
                   class="inline-flex items-center gap-2 self-start rounded-lg border border-zinc-300 px-4 py-2.5
                          text-sm font-medium text-zinc-700 transition hover:bg-zinc-100 sm:self-auto
                          dark:border-zinc-700 dark:text-zinc-300 dark:hover:bg-zinc-800">
                    <span aria-hidden="true">←</span> All brands
                </a>
            </div>


            @include('admin.brands._state', ['initialName' => '', 'initialCountry' => '', 'nextId' => $nextId])

            <div x-data="brandForm()" class="grid gap-6 lg:grid-cols-[1fr_22rem]">

                {{-- Form --}}
                <form method="POST" action="{{ route('admin.brands.store') }}"
                      class="self-start overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm
                             dark:border-zinc-800 dark:bg-zinc-900">
                    @csrf

                    <div class="h-1 bg-gradient-to-r from-red-600 via-red-500/60 to-transparent"></div>

                    <div class="space-y-6 p-6">
                        <div class="flex items-center gap-4">
                            <div class="flex size-12 shrink-0 -skew-x-12 items-center justify-center rounded-lg bg-red-600 text-white shadow-lg shadow-red-600/25">
                                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none"
                                     stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                     aria-hidden="true" class="skew-x-12">
                                    <path d="M12 5v14"/><path d="M5 12h14"/>
                                </svg>
                            </div>
                            <div class="min-w-0 flex-1">
                                <h2 class="font-display text-xl font-semibold text-zinc-900 dark:text-white">Brand details</h2>
                                <p class="text-sm text-zinc-500">Shown on car listings and in the shop's brand filter.</p>
                            </div>
                        </div>

                        @include('admin.brands._form', ['suggest' => true])
                    </div>

                    <div class="flex flex-col gap-3 border-t border-zinc-200 bg-zinc-50 px-6 py-4
                                sm:flex-row sm:items-center sm:justify-between dark:border-zinc-800 dark:bg-zinc-950/40">

                        <p class="flex items-center gap-2 text-sm"
                           x-bind:class="state === 'ok' ? 'text-emerald-600 dark:text-emerald-400'
                                         : (state === 'duplicate' || state === 'toolong' || state === 'countrylong' ? 'text-red-600 dark:text-red-400' : 'text-zinc-500')">
                            <span class="h-2 w-3 -skew-x-12"
                                  x-bind:class="state === 'ok' ? 'bg-emerald-500'
                                                : (state === 'duplicate' || state === 'toolong' || state === 'countrylong' ? 'bg-red-500' : 'bg-zinc-400')"></span>
                            <span x-text="hint"></span>
                        </p>

                        <div class="flex flex-col-reverse gap-3 sm:flex-row sm:items-center">
                            <a href="{{ route('admin.brands.index') }}"
                               class="rounded-xl px-5 py-2.5 text-center text-sm font-medium text-zinc-600 transition
                                      hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white">
                                Cancel
                            </a>

                            <button type="submit" x-bind:disabled="!canSave"
                                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-red-600 px-7 py-2.5
                                           text-sm font-semibold text-white shadow-lg shadow-red-600/25 transition
                                           hover:bg-red-700 disabled:cursor-not-allowed disabled:opacity-40 disabled:shadow-none">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                                     stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M20 6 9 17l-5-5"/>
                                </svg>
                                Save brand
                            </button>
                        </div>
                    </div>
                </form>


                {{-- Side --}}
                <aside class="space-y-6 self-start lg:sticky lg:top-6">

                    @include('admin.brands._preview')

                    <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                        <p class="font-display text-lg font-semibold text-zinc-900 dark:text-white">Naming tips</p>

                        <ol class="mt-4 space-y-4">
                            @foreach ([
                                ['Use the official name', 'Mercedes-Benz, not Mercedes or Benz.'],
                                ['Country of origin', 'Where the brand is headquartered. Brands are grouped by it.'],
                                ['Choose once', 'Brands that cars use are locked from deletion.'],
                            ] as $i => [$title, $text])
                                <li class="flex gap-3">
                                    <span class="flex size-7 shrink-0 -skew-x-12 items-center justify-center rounded-md bg-red-600/10 text-sm font-bold text-red-600 dark:text-red-400">
                                        <span class="skew-x-12">{{ $i + 1 }}</span>
                                    </span>
                                    <div>
                                        <p class="text-sm font-semibold text-zinc-900 dark:text-white">{{ $title }}</p>
                                        <p class="text-xs text-zinc-500">{{ $text }}</p>
                                    </div>
                                </li>
                            @endforeach
                        </ol>
                    </div>
                </aside>
            </div>

        </div>
    </div>
</x-layouts::app>