<x-layouts::app :title="'Edit Category'">
    @php
        // Same colours as the categories list, so the preview matches it.
        $tones = [
            'bg-red-500/15 text-red-500', 'bg-amber-500/15 text-amber-500', 'bg-sky-500/15 text-sky-500',
            'bg-emerald-500/15 text-emerald-500', 'bg-violet-500/15 text-violet-500', 'bg-pink-500/15 text-pink-500',
            'bg-cyan-500/15 text-cyan-500', 'bg-orange-500/15 text-orange-500',
        ];
        $bars = [
            'bg-red-500', 'bg-amber-500', 'bg-sky-500', 'bg-emerald-500',
            'bg-violet-500', 'bg-pink-500', 'bg-cyan-500', 'bg-orange-500',
        ];
        $tone = $tones[(int) $category->category_id % count($tones)];
        $bar = $bars[(int) $category->category_id % count($bars)];
        $count = (int) ($category->cars_count ?? $category->cars()->count());
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
                        <a href="{{ route('admin.categories.index') }}" class="hover:text-red-600">Categories</a>
                        <span>/</span>
                        <span class="text-zinc-600 dark:text-zinc-300">Edit #{{ $category->category_id }}</span>
                    </div>

                    <h1 class="font-display mt-1 text-4xl font-extrabold italic uppercase tracking-tight
                               text-zinc-900 sm:text-5xl dark:text-white">
                        Edit category
                    </h1>
                    <div class="mt-2 h-1 w-14 -skew-x-12 bg-red-600"></div>
                </div>

                <a href="{{ route('admin.categories.index') }}"
                   class="inline-flex items-center gap-2 self-start rounded-lg border border-zinc-300 px-4 py-2.5
                          text-sm font-medium text-zinc-700 transition hover:bg-zinc-100 sm:self-auto
                          dark:border-zinc-700 dark:text-zinc-300 dark:hover:bg-zinc-800">
                    <span aria-hidden="true">←</span> All categories
                </a>
            </div>


            @include('admin.categories._state', [
                'initialName' => $category->category_name,
                'original'    => $category->category_name,
                'selfId'      => (int) $category->category_id,
            ])

            <div x-data="categoryForm()" class="grid gap-6 lg:grid-cols-[1fr_22rem]">

                {{-- Form --}}
                <form method="POST" action="{{ route('admin.categories.update', $category) }}"
                      class="self-start overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm
                             dark:border-zinc-800 dark:bg-zinc-900">
                    @csrf
                    @method('PUT')

                    <div class="h-1 bg-gradient-to-r from-red-600 via-red-500/60 to-transparent"></div>

                    <div class="space-y-6 p-6">
                        <div class="flex items-center gap-4">
                            <div class="flex size-12 shrink-0 items-center justify-center rounded-xl text-xl font-bold {{ $tone }}">
                                {{ mb_strtoupper(mb_substr($category->category_name, 0, 1)) }}
                            </div>
                            <div class="min-w-0 flex-1">
                                <h2 class="font-display text-xl font-semibold text-zinc-900 dark:text-white">Category details</h2>
                                <p class="text-sm text-zinc-500">Renaming updates it everywhere the category appears.</p>
                            </div>
                            <span class="rounded-full bg-zinc-500/10 px-3 py-1 text-xs font-semibold text-zinc-500">
                                ID #{{ $category->category_id }}
                            </span>
                        </div>

                        @include('admin.categories._form')

                        <p x-show="state === 'ok'" x-cloak
                           class="flex items-center gap-2 rounded-lg bg-amber-500/10 px-3 py-2 text-sm text-amber-700
                                  dark:text-amber-300">
                            <span class="size-2 shrink-0 rounded-full bg-amber-500"></span>
                            Unsaved change: "<span x-text="original"></span>" will become "<span x-text="name.trim()"></span>".
                        </p>
                    </div>

                    <div class="flex flex-col gap-3 border-t border-zinc-200 bg-zinc-50 px-6 py-4
                                sm:flex-row sm:items-center sm:justify-between dark:border-zinc-800 dark:bg-zinc-950/40">

                        <p class="flex items-center gap-2 text-sm"
                           x-bind:class="state === 'ok' ? 'text-emerald-600 dark:text-emerald-400'
                                         : (state === 'duplicate' || state === 'toolong' ? 'text-red-600 dark:text-red-400' : 'text-zinc-500')">
                            <span class="size-2 rounded-full"
                                  x-bind:class="state === 'ok' ? 'bg-emerald-500'
                                                : (state === 'duplicate' || state === 'toolong' ? 'bg-red-500' : 'bg-zinc-400')"></span>
                            <span x-text="hint"></span>
                        </p>

                        <div class="flex flex-col-reverse gap-3 sm:flex-row sm:items-center">
                            <a href="{{ route('admin.categories.index') }}"
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
                                Save changes
                            </button>
                        </div>
                    </div>
                </form>


                {{-- Side: live preview, usage, existing, delete --}}
                <aside class="space-y-6 self-start lg:sticky lg:top-6">

                    <div class="overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                        <div class="flex items-center justify-between px-5 pt-5">
                            <p class="text-sm font-medium text-zinc-500">Live preview</p>
                            <span class="flex items-center gap-1.5 text-xs text-zinc-500">
                                <span class="size-2 animate-pulse rounded-full bg-emerald-500"></span> updates as you type
                            </span>
                        </div>

                        <div class="p-5">
                            <div class="flex items-center gap-4 rounded-xl border bg-zinc-50 p-4 transition dark:bg-zinc-800/50"
                                 x-bind:class="state === 'duplicate' ? 'border-red-500/60' : 'border-zinc-200 dark:border-zinc-800'">
                                <div class="flex size-12 shrink-0 items-center justify-center rounded-xl text-xl font-bold transition"
                                     x-bind:class="state === 'duplicate' ? 'bg-red-500/15 text-red-500' : '{{ $tone }}'">
                                    <span x-text="(name.trim().charAt(0) || '?').toUpperCase()">?</span>
                                </div>

                                <div class="min-w-0 flex-1">
                                    <p class="font-display truncate text-lg font-semibold text-zinc-900 dark:text-white"
                                       x-text="name.trim() || 'Category name'">{{ $category->category_name }}</p>
                                    <div class="mt-2 h-1.5 overflow-hidden rounded-full bg-zinc-200 dark:bg-zinc-700">
                                        <div class="h-full rounded-full {{ $bar }}" style="width: {{ $count > 0 ? 60 : 0 }}%"></div>
                                    </div>
                                </div>

                                <div class="text-right">
                                    <p class="font-display text-2xl font-bold tabular-nums
                                              {{ $count === 0 ? 'text-zinc-400 dark:text-zinc-600' : 'text-zinc-900 dark:text-white' }}">
                                        {{ number_format($count) }}
                                    </p>
                                    <p class="text-xs text-zinc-500">{{ \Illuminate\Support\Str::plural('model', $count) }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div class="rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                            <p class="text-xs text-zinc-500">Car models</p>
                            <p class="font-display mt-1 text-3xl font-bold tabular-nums text-zinc-900 dark:text-white">{{ number_format($count) }}</p>
                        </div>
                        <div class="rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                            <p class="text-xs text-zinc-500">Status</p>
                            <p class="mt-2 inline-block rounded-full px-3 py-1 text-xs font-semibold
                                      {{ $count > 0 ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400' : 'bg-zinc-500/10 text-zinc-500' }}">
                                {{ $count > 0 ? 'In use' : 'Empty' }}
                            </p>
                        </div>
                    </div>

                    {{-- Delete (its own form, outside the edit form) --}}
                    <div class="rounded-2xl border border-red-500/30 bg-red-500/5 p-5">
                        <p class="font-display text-lg font-semibold text-zinc-900 dark:text-white">Delete category</p>

                        @if ($count > 0)
                            <p class="mt-1 text-sm text-zinc-500">
                                {{ $count }} car {{ \Illuminate\Support\Str::plural('model', $count) }} use this category,
                                so it can't be deleted. Move those cars to another category first.
                            </p>
                            <span class="mt-4 inline-flex cursor-not-allowed items-center gap-2 rounded-xl border border-dashed
                                         border-zinc-300 px-4 py-2 text-sm text-zinc-400 dark:border-zinc-700">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                                     stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <rect width="18" height="11" x="3" y="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                                </svg>
                                Locked
                            </span>
                        @else
                            <p class="mt-1 text-sm text-zinc-500">No cars use this category. Deleting it can't be undone.</p>
                            <form method="POST" action="{{ route('admin.categories.destroy', $category) }}"
                                  data-confirm="Delete category {{ $category->category_name }}?"
                                  onsubmit="return confirm(this.dataset.confirm)" class="mt-4">
                                @csrf
                                @method('DELETE')

                                <button type="submit"
                                        class="inline-flex items-center gap-2 rounded-xl border border-red-500 px-4 py-2
                                               text-sm font-semibold text-red-500 transition hover:bg-red-500 hover:text-white">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                                         stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <path d="M3 6h18"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"/>
                                        <path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                                    </svg>
                                    Delete category
                                </button>
                            </form>
                        @endif
                    </div>
                </aside>
            </div>

        </div>
    </div>
</x-layouts::app>