{{--
    Shared by Add and Edit tier pages.
    Must be included INSIDE the <form> element (the form is the 2-column grid).
--}}
@php
    $isEdit     = $tier->exists;
    $isBaseTier = $isEdit && $tier->getOriginal('min_points') === 0;

    // ---- Input styles -------------------------------------------------------
    $base  = 'h-11 w-full rounded-lg border bg-zinc-50 px-4 text-sm text-zinc-900 placeholder:text-zinc-400 transition
              focus:outline-none focus:ring-2 dark:bg-zinc-800/60 dark:text-white';
    $ok    = 'border-zinc-300 focus:border-red-500 focus:ring-red-500/20 dark:border-zinc-700';
    $bad   = 'border-red-500 focus:border-red-500 focus:ring-red-500/20';
    $field = fn (string $name) => $base.' '.($errors->has($name) ? $bad : $ok);

    $chip      = 'rounded-lg border border-zinc-300 bg-white px-3 py-1.5 text-sm font-medium transition hover:border-red-500 hover:text-red-500 dark:border-zinc-700 dark:bg-zinc-800';
    $chipTaken = 'cursor-not-allowed rounded-lg border border-dashed border-zinc-300 px-3 py-1.5 text-sm text-zinc-400 dark:border-zinc-700 dark:text-zinc-500';

    // ---- Other tiers (the one being edited is excluded) ---------------------
    // Prefer passing $tiers from the controller; otherwise load them here
    // (adjust the model name if yours is different).
    $tierModel = \App\Models\MembershipTier::class;
    $ladder = collect($tiers ?? (class_exists($tierModel) ? $tierModel::orderBy('min_points')->get() : []))
        ->reject(fn ($t) => $isEdit && (string) $t->tier_id === (string) $tier->tier_id)
        ->map(fn ($t) => [
            'id'       => $t->tier_id,
            'name'     => $t->tier_name,
            'min'      => (int) $t->min_points,
            'discount' => (float) $t->discount_percent,
        ])
        ->sortBy('min')
        ->values();

    $takenNames  = $ladder->pluck('name')->map(fn ($n) => mb_strtolower($n))->all();
    $takenPoints = $ladder->pluck('min')->all();

    // Suggested tiers (click to fill). Already-added ones are shown as taken.
    $presets = [
        ['name' => 'Bronze',   'min' => 200,   'discount' => 1],
        ['name' => 'Silver',   'min' => 500,   'discount' => 3],
        ['name' => 'Gold',     'min' => 2000,  'discount' => 5],
        ['name' => 'Platinum', 'min' => 5000,  'discount' => 10],
        ['name' => 'Diamond',  'min' => 7000,  'discount' => 15],
        ['name' => 'Elite',    'min' => 10000, 'discount' => 18],
        ['name' => 'Legend',   'min' => 15000, 'discount' => 20],
        ['name' => 'Titan',    'min' => 25000, 'discount' => 25],
        ['name' => 'King',     'min' => 30000, 'discount' => 30],
    ];
    $addedCount = collect($presets)->filter(fn ($p) => in_array(mb_strtolower($p['name']), $takenNames, true))->count();
@endphp

{{-- ===================== LEFT: form card ===================== --}}
<div class="relative overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
    <div class="pointer-events-none absolute inset-x-0 top-0 h-0.5 bg-gradient-to-r from-red-600 via-red-500/50 to-transparent"></div>

    <div class="space-y-6 p-5 sm:p-6">

        <div class="flex items-center gap-3">
            <span class="flex h-12 w-12 shrink-0 -skew-x-6 items-center justify-center rounded-xl bg-red-600 text-white shadow-lg shadow-red-600/30">
                @if ($isEdit)
                    <svg viewBox="0 0 24 24" class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 20h9M16.5 3.5a2.1 2.1 0 013 3L7 19l-4 1 1-4z"/></svg>
                @else
                    <svg viewBox="0 0 24 24" class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
                @endif
            </span>
            <div>
                <h2 class="text-lg font-bold">Tier details</h2>
                <p class="text-sm text-zinc-500">Members reach this tier when their points hit the minimum.</p>
            </div>
        </div>

        {{-- Tier name --}}
        <div>
            <label for="tier_name" class="mb-1.5 block text-sm font-semibold text-zinc-900 dark:text-white">Tier name</label>

            <input id="tier_name" name="tier_name" type="text" required maxlength="255"
                   value="{{ old('tier_name', $tier->tier_name) }}"
                   placeholder="e.g. Gold"
                   @error('tier_name') aria-invalid="true" @enderror
                   class="{{ $field('tier_name') }}">

            @error('tier_name')
                <p class="mt-1.5 text-xs font-medium text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="grid gap-5 sm:grid-cols-2">

            {{-- Minimum points --}}
            <div>
                <label for="min_points" class="mb-1.5 block text-sm font-semibold text-zinc-900 dark:text-white">Minimum points</label>

                @if ($isBaseTier)
                    {{-- The default tier must always be 0 (the server checks this again). --}}
                    <input type="hidden" name="min_points" value="0">

                    <div class="relative">
                        <input id="min_points" type="number" value="0" disabled
                               class="{{ $base }} {{ $ok }} cursor-not-allowed pr-12 opacity-60">
                        <span class="pointer-events-none absolute right-4 top-1/2 -translate-y-1/2 text-xs text-zinc-500">pts</span>
                    </div>

                    <p class="mt-1.5 text-xs text-zinc-500">
                        This is the default tier for new members, so its minimum points must stay 0.
                    </p>
                @else
                    <div class="relative">
                        <input id="min_points" name="min_points" type="number" required min="0" step="1"
                               value="{{ old('min_points', $tier->min_points) }}"
                               placeholder="e.g. 2000"
                               @error('min_points') aria-invalid="true" @enderror
                               class="{{ $field('min_points') }} pr-12">
                        <span class="pointer-events-none absolute right-4 top-1/2 -translate-y-1/2 text-xs text-zinc-500">pts</span>
                    </div>

                    @error('min_points')
                        <p class="mt-1.5 text-xs font-medium text-red-600">{{ $message }}</p>
                    @else
                        <p class="mt-1.5 text-xs text-zinc-500">
                            1 point per ฿1,000 of completed orders. Each tier needs a different value.
                        </p>
                    @enderror
                @endif
            </div>

            {{-- Discount --}}
            <div>
                <label for="discount_percent" class="mb-1.5 block text-sm font-semibold text-zinc-900 dark:text-white">Discount</label>

                <div class="relative">
                    <input id="discount_percent" name="discount_percent" type="number" required
                           min="0" max="100" step="0.01"
                           value="{{ old('discount_percent', $tier->discount_percent ?? 0) }}"
                           @error('discount_percent') aria-invalid="true" @enderror
                           class="{{ $field('discount_percent') }} pr-10">
                    <span class="pointer-events-none absolute right-4 top-1/2 -translate-y-1/2 text-sm text-zinc-500">%</span>
                </div>

                @error('discount_percent')
                    <p class="mt-1.5 text-xs font-medium text-red-600">{{ $message }}</p>
                @else
                    <p class="mt-1.5 text-xs text-zinc-500">From 0 to 100.</p>
                @enderror
            </div>
        </div>

        {{-- Live warnings (duplicate name / duplicate points) --}}
        <div id="tier-warning" class="hidden items-start gap-3 rounded-xl border border-red-300 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-900/60 dark:bg-red-950/30 dark:text-red-300" role="alert">
            <svg class="mt-0.5 h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 9v4M12 17h.01M10.3 3.9L2 18a2 2 0 001.7 3h16.6a2 2 0 001.7-3L13.7 3.9a2 2 0 00-3.4 0z"/></svg>
            <p id="tier-warning-text"></p>
        </div>

        {{-- Edit-only note --}}
        @if ($isEdit)
            <div class="flex items-start gap-3 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800
                        dark:border-amber-900/50 dark:bg-amber-950/25 dark:text-amber-300">
                <svg class="mt-0.5 h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4M12 8h.01"/></svg>
                <p>
                    Changing minimum points does not move existing members right away.
                    Their tier is recalculated on their next completed order.
                </p>
            </div>
        @endif

        {{-- Suggested tiers (hidden for the default tier, whose points are fixed) --}}
        @unless ($isBaseTier)
            <div>
                <div class="flex items-center justify-between text-xs">
                    <span class="font-semibold text-zinc-500">Suggested tiers</span>
                    <span class="text-zinc-500"><b class="text-zinc-900 dark:text-white">{{ $addedCount }}</b> of {{ count($presets) }} already added</span>
                </div>
                <div class="mt-2 h-1.5 overflow-hidden rounded-full bg-zinc-200 dark:bg-zinc-800">
                    <div class="h-full rounded-full bg-emerald-500" style="width: {{ round($addedCount / count($presets) * 100) }}%"></div>
                </div>

                <div class="mt-3 flex flex-wrap gap-2">
                    @foreach ($presets as $p)
                        @if (in_array(mb_strtolower($p['name']), $takenNames, true))
                            <span class="{{ $chipTaken }}" title="Already added">
                                <span class="text-emerald-500">✓</span>
                                <span class="line-through">{{ $p['name'] }}</span>
                                <span class="ml-1 text-[10px] font-bold uppercase">Added</span>
                            </span>
                        @else
                            <button type="button" class="js-preset {{ $chip }}"
                                    data-name="{{ $p['name'] }}" data-min="{{ $p['min'] }}" data-discount="{{ $p['discount'] }}">
                                {{ $p['name'] }}
                                <span class="ml-1 text-xs font-normal text-zinc-500">{{ number_format($p['min']) }} pts · {{ $p['discount'] }}%</span>
                            </button>
                        @endif
                    @endforeach
                </div>
            </div>
        @endunless

        <div class="grid gap-5 sm:grid-cols-2">
            @unless ($isBaseTier)
                <div>
                    <div class="mb-2 text-xs font-semibold text-zinc-500">Quick points</div>
                    <div class="flex flex-wrap gap-2">
                        @foreach ([500, 1000, 2500, 5000, 10000, 20000] as $v)
                            @if (in_array($v, $takenPoints, true))
                                <span class="{{ $chipTaken }} line-through" title="Used by another tier">{{ number_format($v) }}</span>
                            @else
                                <button type="button" class="js-pts {{ $chip }}" data-value="{{ $v }}">{{ number_format($v) }}</button>
                            @endif
                        @endforeach
                    </div>
                </div>
            @endunless

            <div>
                <div class="mb-2 text-xs font-semibold text-zinc-500">Quick discount</div>
                <div class="flex flex-wrap gap-2">
                    @foreach ([3, 5, 10, 15, 20, 25] as $v)
                        <button type="button" class="js-dis {{ $chip }}" data-value="{{ $v }}">{{ $v }}%</button>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- Footer --}}
    <div class="flex flex-wrap items-center justify-between gap-3 border-t border-zinc-200 bg-zinc-50 px-5 py-4 dark:border-zinc-800 dark:bg-zinc-900/60 sm:px-6">
        <span id="tier-hint" class="text-sm text-zinc-500">Enter a tier name to continue</span>

        <div class="flex gap-3">
            <a href="{{ route('admin.tiers.index') }}"
               class="inline-flex h-10 items-center justify-center rounded-xl border border-zinc-300 px-5 text-sm font-semibold text-zinc-700 transition hover:border-zinc-400 dark:border-zinc-700 dark:text-zinc-300">
                Cancel
            </a>
            <button type="submit"
                    class="inline-flex h-10 items-center justify-center rounded-xl bg-red-600 px-6 text-sm font-semibold text-white shadow-lg shadow-red-600/20 transition hover:bg-red-500 focus:outline-none focus:ring-2 focus:ring-red-500/40">
                {{ $isEdit ? 'Save changes' : 'Save tier' }}
            </button>
        </div>
    </div>
</div>

{{-- ===================== RIGHT: preview, ladder, tips ===================== --}}
<aside class="space-y-5">

    <section class="rounded-2xl border border-zinc-200 bg-white p-5 dark:border-zinc-800 dark:bg-zinc-900">
        <div class="flex items-center justify-between text-xs text-zinc-500">
            <span>Live preview</span>
            <span class="flex items-center gap-1.5"><span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>updates as you type</span>
        </div>

        <div class="relative mt-3 overflow-hidden rounded-xl border border-zinc-200 bg-gradient-to-b from-red-600/10 to-transparent p-4 dark:border-zinc-800">
            <div class="absolute inset-x-0 top-0 h-1 bg-red-600"></div>
            <div class="flex items-center gap-2">
                <span class="inline-flex max-w-[70%] items-center gap-1.5 rounded-full bg-red-600/15 px-3 py-1 text-sm font-semibold text-red-600 dark:text-red-400">
                    <span class="h-1.5 w-1.5 shrink-0 rounded-full bg-current"></span>
                    <span id="pv-name" class="truncate">Tier name</span>
                </span>
                <span class="text-xs text-zinc-500">{{ $isEdit ? '#' . $tier->tier_id : '#NEW' }}</span>
            </div>

            <div class="mt-3 text-5xl font-black italic leading-none text-red-600 dark:text-red-500">
                <span id="pv-discount">0</span><span class="text-2xl">%</span>
            </div>
            <div class="mt-1 text-sm text-zinc-500">member discount</div>

            <dl class="mt-4 space-y-2 border-t border-zinc-200 pt-3 text-sm dark:border-zinc-800">
                <div class="flex justify-between"><dt class="text-zinc-500">Points</dt><dd id="pv-range" class="font-bold">—</dd></div>
                @unless ($isEdit)
                    <div class="flex justify-between"><dt class="text-zinc-500">Members</dt><dd class="font-bold">0</dd></div>
                @endunless
            </dl>
        </div>

        <p id="pv-position" class="mt-3 text-xs text-zinc-500">Enter minimum points to see where this tier sits.</p>
    </section>

    <section class="rounded-2xl border border-zinc-200 bg-white p-5 dark:border-zinc-800 dark:bg-zinc-900">
        <h2 class="text-sm font-bold">Current ladder</h2>
        <p class="mt-0.5 text-xs text-zinc-500">{{ $isEdit ? 'The tier you are editing' : 'Your new tier' }} is highlighted in red.</p>
        <ol id="ladder" class="mt-3 space-y-1.5"></ol>
    </section>

    <section class="rounded-2xl border border-zinc-200 bg-white p-5 dark:border-zinc-800 dark:bg-zinc-900">
        <h2 class="text-base font-bold">Tier tips</h2>
        <ol class="mt-3 space-y-3 text-sm">
            @foreach ([
                ['Points rule', '1 point per ฿1,000 of completed orders.'],
                ['Unique minimum', 'Two tiers can’t start at the same points.'],
                ['Higher = better', 'Give higher tiers a bigger discount.'],
            ] as $i => [$title, $text])
                <li class="flex gap-3">
                    <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-md bg-red-600/15 text-xs font-bold text-red-600">{{ $i + 1 }}</span>
                    <div>
                        <div class="font-semibold">{{ $title }}</div>
                        <div class="text-xs text-zinc-500">{{ $text }}</div>
                    </div>
                </li>
            @endforeach
        </ol>
    </section>
</aside>

<script>
    (() => {
        const tiers  = @json($ladder);
        const isEdit = @json($isEdit);
        const $ = (id) => document.getElementById(id);
        const nameEl = $('tier_name'), ptsEl = $('min_points'), disEl = $('discount_percent');
        if (!nameEl || !ptsEl || !disEl) return;

        const fmt = (n) => Number(n).toLocaleString('en-US');
        const esc = (s) => String(s).replace(/[&<>"']/g, (c) => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));

        function read() {
            const name = nameEl.value.trim();
            const pts = ptsEl.value === '' ? null : parseInt(ptsEl.value, 10);
            const dis = disEl.value === '' ? 0 : parseFloat(disEl.value);
            return { name, pts: Number.isNaN(pts) ? null : pts, dis: Number.isNaN(dis) ? 0 : dis };
        }

        function render() {
            const { name, pts, dis } = read();
            const sameName = name && tiers.find((t) => t.name.toLowerCase() === name.toLowerCase());
            const samePts  = pts !== null && tiers.find((t) => t.min === pts);

            // Preview card
            $('pv-name').textContent = name || 'Tier name';
            $('pv-discount').textContent = Number.isInteger(dis) ? dis : dis.toFixed(2).replace(/0+$/, '').replace(/\.$/, '');

            // Merge this tier into the ladder
            const rows = tiers.map((t) => ({ ...t, isMine: false }));
            if (pts !== null) rows.push({ id: 'mine', name: name || (isEdit ? 'This tier' : 'New tier'), min: pts, discount: dis, isMine: true });
            rows.sort((a, b) => a.min - b.min || (a.isMine ? 1 : -1));
            rows.forEach((r, i) => { r.max = rows[i + 1] ? rows[i + 1].min - 1 : null; });
            const me = rows.find((r) => r.isMine);

            $('pv-range').textContent = !me ? '—' : (me.max === null ? fmt(me.min) + '+' : fmt(me.min) + ' – ' + fmt(Math.max(me.max, me.min)));

            // Position text
            const pos = $('pv-position');
            if (!me) {
                pos.textContent = 'Enter minimum points to see where this tier sits.';
            } else {
                const idx = rows.indexOf(me);
                const below = rows[idx - 1], above = rows[idx + 1];
                pos.textContent = !below && !above ? 'This is the only tier.'
                    : !above ? 'Highest tier — above ' + below.name + '.'
                    : !below ? 'Lowest tier — below ' + above.name + '.'
                    : 'Sits between ' + below.name + ' and ' + above.name + '.';
                if (below && dis < below.discount) pos.textContent += ' Its discount is lower than ' + below.name + '’s (' + below.discount + '%).';
            }

            // Ladder list
            $('ladder').innerHTML = rows.length ? rows.map((r) => `
                <li class="flex items-center justify-between rounded-lg px-3 py-2 text-sm ${r.isMine ? 'bg-red-600 text-white' : 'bg-zinc-50 dark:bg-zinc-800/60'}">
                    <span class="min-w-0">
                        <span class="block truncate font-semibold">${esc(r.name)}${r.isMine ? ' <span class="ml-1 text-[10px] font-bold uppercase opacity-80">' + (isEdit ? 'Editing' : 'New') + '</span>' : ''}</span>
                        <span class="text-xs ${r.isMine ? 'text-white/80' : 'text-zinc-500'}">${fmt(r.min)}${r.max === null ? '+' : ' – ' + fmt(Math.max(r.max, r.min))} pts</span>
                    </span>
                    <span class="shrink-0 font-bold">${r.discount}%</span>
                </li>`).join('') : '<li class="text-xs text-zinc-500">No tiers yet.</li>';

            // Warnings
            const warn = $('tier-warning'), text = $('tier-warning-text');
            const msg = sameName ? 'A tier named “' + sameName.name + '” already exists.'
                : samePts ? samePts.name + ' already starts at ' + fmt(pts) + ' points. Pick a different minimum.' : '';
            warn.classList.toggle('hidden', !msg);
            warn.classList.toggle('flex', !!msg);
            text.textContent = msg;

            // Footer hint
            $('tier-hint').textContent = msg ? 'Fix the issue above to continue'
                : !name ? 'Enter a tier name to continue'
                : pts === null ? 'Enter minimum points to continue'
                : (isEdit ? 'Ready to save changes' : 'Ready to save');
        }

        const setValue = (el, v) => { el.value = v; el.dispatchEvent(new Event('input', { bubbles: true })); };

        document.querySelectorAll('.js-preset').forEach((b) => b.addEventListener('click', () => {
            setValue(nameEl, b.dataset.name);
            if (!ptsEl.disabled) setValue(ptsEl, b.dataset.min);
            setValue(disEl, b.dataset.discount);
        }));
        document.querySelectorAll('.js-pts').forEach((b) => b.addEventListener('click', () => { if (!ptsEl.disabled) setValue(ptsEl, b.dataset.value); }));
        document.querySelectorAll('.js-dis').forEach((b) => b.addEventListener('click', () => setValue(disEl, b.dataset.value)));

        [nameEl, ptsEl, disEl].forEach((el) => el.addEventListener('input', render));
        render();
    })();
</script>