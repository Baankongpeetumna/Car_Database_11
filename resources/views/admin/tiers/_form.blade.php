@include('partials.tier-colors')

@php
    $isEdit = $tier->exists;
    $isBaseTier = $isEdit && (int) $tier->getOriginal('min_points') === 0;

    $base = 'h-11 w-full rounded-lg border bg-zinc-50 px-4 text-sm text-zinc-900 transition focus:outline-none focus:ring-2 dark:bg-zinc-800/60 dark:text-white';
    $ok = 'border-zinc-300 focus:border-red-500 focus:ring-red-500/20 dark:border-zinc-700';
    $bad = 'border-red-500 focus:border-red-500 focus:ring-red-500/20';
    $field = fn (string $name) => $base.' '.($errors->has($name) ? $bad : $ok);

    $chip = 'rounded-lg border border-zinc-300 bg-white px-3 py-1.5 text-sm font-medium transition hover:border-red-500 hover:text-red-500 dark:border-zinc-700 dark:bg-zinc-800';
    $chipTaken = 'cursor-not-allowed rounded-lg border border-dashed border-zinc-300 px-3 py-1.5 text-sm text-zinc-400 dark:border-zinc-700';

    $ladder = collect(
        $tiers ?? \App\Models\MembershipTier::orderBy('min_points')->get()
    )
        ->reject(fn ($t) => $isEdit && (string) $t->tier_id === (string) $tier->tier_id)
        ->map(fn ($t) => [
            'id' => $t->tier_id,
            'name' => $t->tier_name,
            'min' => (int) $t->min_points,
            'discount' => (float) $t->discount_percent,
            'color' => $t->color_hex,
        ])
        ->sortBy('min')
        ->values();

    $takenNames = $ladder->pluck('name')
        ->map(fn ($name) => mb_strtolower(trim($name)))
        ->all();

    $takenPoints = $ladder->pluck('min')->all();

    $presets = [
        ['name' => 'Bronze', 'min' => 200, 'discount' => 1, 'color' => '#B45309'],
        ['name' => 'Silver', 'min' => 500, 'discount' => 3, 'color' => '#0EA5E9'],
        ['name' => 'Gold', 'min' => 2000, 'discount' => 5, 'color' => '#F59E0B'],
        ['name' => 'Platinum', 'min' => 5000, 'discount' => 10, 'color' => '#8B5CF6'],
        ['name' => 'Diamond', 'min' => 7000, 'discount' => 15, 'color' => '#06B6D4'],
        ['name' => 'Elite', 'min' => 10000, 'discount' => 18, 'color' => '#EC4899'],
        ['name' => 'Legend', 'min' => 15000, 'discount' => 20, 'color' => '#10B981'],
        ['name' => 'Titan', 'min' => 25000, 'discount' => 25, 'color' => '#F97316'],
        ['name' => 'King', 'min' => 30000, 'discount' => 30, 'color' => '#EF4444'],
    ];

    $addedCount = collect($presets)
        ->filter(fn ($p) => in_array(mb_strtolower($p['name']), $takenNames, true))
        ->count();

    $selectedColor = old(
        'color',
        $isEdit ? $tier->color_hex : '#EF4444'
    );

    if (
        !is_string($selectedColor)
        || !preg_match('/^#[0-9a-fA-F]{6}$/', $selectedColor)
    ) {
        $selectedColor = '#EF4444';
    }

    $selectedColor = strtoupper($selectedColor);

    $palette = [
        'Slate' => '#64748B',
        'Gray' => '#71717A',
        'Black' => '#18181B',
        'Brown' => '#92400E',
        'Bronze' => '#B45309',
        'Orange' => '#F97316',
        'Amber' => '#F59E0B',
        'Yellow' => '#EAB308',
        'Lime' => '#84CC16',
        'Green' => '#22C55E',
        'Emerald' => '#10B981',
        'Teal' => '#14B8A6',
        'Cyan' => '#06B6D4',
        'Sky' => '#0EA5E9',
        'Blue' => '#3B82F6',
        'Indigo' => '#6366F1',
        'Violet' => '#8B5CF6',
        'Purple' => '#A855F7',
        'Fuchsia' => '#D946EF',
        'Pink' => '#EC4899',
        'Rose' => '#F43F5E',
        'Red' => '#EF4444',
        'Dark red' => '#B91C1C',
        'White' => '#FFFFFF',
    ];
@endphp

{{-- Selected swatch ring. Driven by aria-pressed, so hover/focus/click states can't clear it. --}}
<style>
    [data-tier-swatch] { position: relative; --swatch-gap: #ffffff; }
    .dark [data-tier-swatch] { --swatch-gap: #18181b; }
    [data-tier-swatch][aria-pressed="true"] {
        z-index: 1;
        transform: scale(1.08);
        box-shadow: 0 0 0 2px var(--swatch-gap), 0 0 0 4px #ef4444;
    }

    /* Selected state for the quick-pick chips (discount, points, suggested tiers) */
    .js-dis[aria-pressed="true"],
    .js-pts[aria-pressed="true"],
    .js-preset[aria-pressed="true"] {
        border-color: #ef4444 !important;
        color: #ef4444 !important;
        background-color: rgba(239, 68, 68, 0.10) !important;
        box-shadow: 0 0 0 2px rgba(239, 68, 68, 0.35);
        font-weight: 700;
    }
</style>

{{-- LEFT: tier details --}}
<div class="relative overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
    <div class="pointer-events-none absolute inset-x-0 top-0 h-0.5 bg-gradient-to-r from-red-600 via-red-500/50 to-transparent"></div>

    <div class="space-y-6 p-5 sm:p-6">
        <div class="flex items-center gap-3">
            <span class="flex h-12 w-12 shrink-0 -skew-x-6 items-center justify-center rounded-xl bg-red-600 text-white shadow-lg shadow-red-600/30">
                @if ($isEdit)
                    <svg viewBox="0 0 24 24" class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M12 20h9M16.5 3.5a2.1 2.1 0 013 3L7 19l-4 1 1-4z"/>
                    </svg>
                @else
                    <svg viewBox="0 0 24 24" class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true">
                        <path d="M12 5v14M5 12h14"/>
                    </svg>
                @endif
            </span>

            <div>
                <h2 class="text-lg font-bold">Tier details</h2>
                <p class="text-sm text-zinc-500">
                    Set the name, minimum points, discount and color for this membership level.
                </p>
            </div>
        </div>

        {{-- Name --}}
        <div>
            <label for="tier_name" class="mb-1.5 block text-sm font-semibold">
                Tier name
            </label>

            <input
                id="tier_name"
                name="tier_name"
                type="text"
                required
                maxlength="255"
                value="{{ old('tier_name', $tier->tier_name) }}"
                placeholder="e.g. Gold"
                class="{{ $field('tier_name') }}"
                @error('tier_name') aria-invalid="true" @enderror
            >

            @error('tier_name')
                <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="grid gap-5 sm:grid-cols-2">
            {{-- Points --}}
            <div>
                <label for="min_points" class="mb-1.5 block text-sm font-semibold">
                    Minimum points
                </label>

                @if ($isBaseTier)
                    <input type="hidden" name="min_points" value="0">

                    <input
                        id="min_points"
                        type="number"
                        value="0"
                        disabled
                        class="{{ $base }} {{ $ok }} cursor-not-allowed opacity-60"
                    >

                    <p class="mt-1.5 text-xs text-zinc-500">
                        The starting tier for new members must have 0 minimum points.
                    </p>
                @else
                    <input
                        id="min_points"
                        name="min_points"
                        type="number"
                        required
                        min="0"
                        max="4294967295"
                        step="1"
                        value="{{ old('min_points', $tier->min_points) }}"
                        placeholder="e.g. 2000"
                        class="{{ $field('min_points') }}"
                        @error('min_points') aria-invalid="true" @enderror
                    >

                    <p class="mt-1.5 text-xs text-zinc-500">
                        Every ฿1,000 of completed purchases earns 1 point.
                    </p>
                @endif

                @error('min_points')
                    <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Discount --}}
            <div>
                <label for="discount_percent" class="mb-1.5 block text-sm font-semibold">
                    Discount (%)
                </label>

                <input
                    id="discount_percent"
                    name="discount_percent"
                    type="number"
                    required
                    min="0"
                    max="100"
                    step="0.01"
                    value="{{ old('discount_percent', $tier->discount_percent ?? 0) }}"
                    class="{{ $field('discount_percent') }}"
                    @error('discount_percent') aria-invalid="true" @enderror
                >

                <p class="mt-1.5 text-xs text-zinc-500">Any value from 0 to 100%.</p>

                @error('discount_percent')
                    <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        {{-- Color --}}
        <div class="space-y-3">
            <label for="tier_color" class="block text-sm font-semibold">
                Tier color
            </label>

            <div class="flex flex-wrap items-center gap-3">
                <input
                    id="tier_color"
                    name="color"
                    type="color"
                    value="{{ $selectedColor }}"
                    required
                    aria-describedby="tier-color-help"
                    class="h-12 w-20 cursor-pointer rounded-lg border border-zinc-300 bg-white p-1 dark:border-zinc-700 dark:bg-zinc-800"
                >

                <span id="tier-color-code" class="font-mono text-sm text-zinc-600 dark:text-zinc-300">
                    {{ $selectedColor }}
                </span>
            </div>

            <p id="tier-color-help" class="text-xs text-zinc-500">
                Click the color box to pick your own, or choose one of the 24 colors below.
            </p>

            <div class="grid grid-cols-8 gap-2 p-1 sm:grid-cols-12">
                @foreach ($palette as $colorName => $hex)
                    <button
                        type="button"
                        data-tier-swatch="{{ $hex }}"
                        title="{{ $colorName }} {{ $hex }}"
                        aria-label="Select color {{ $colorName }}"
                        aria-pressed="{{ $selectedColor === $hex ? 'true' : 'false' }}"
                        class="h-9 w-full rounded-lg border border-zinc-300 transition hover:scale-105 focus:outline-none dark:border-zinc-600"
                        style="background-color: {{ $hex }}"
                    ></button>
                @endforeach
            </div>

            @error('color')
                <p class="text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Duplicate warning --}}
        <div id="tier-warning"
             class="hidden rounded-xl border border-red-300 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-900 dark:bg-red-950/30 dark:text-red-300"
             role="alert">
            <p id="tier-warning-text"></p>
        </div>

        @if ($isEdit)
            <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800 dark:border-amber-900 dark:bg-amber-950/25 dark:text-amber-300">
                Changing the minimum points will not move members to another tier right away.
                Tiers are recalculated when a member's next order is completed.
            </div>
        @endif

        {{-- Suggested tiers --}}
        @unless ($isBaseTier)
            <div>
                <div class="flex items-center justify-between text-xs text-zinc-500">
                    <span class="font-semibold">Suggested tiers</span>
                    <span>{{ $addedCount }} of {{ count($presets) }} already added</span>
                </div>

                <div class="mt-2 h-1.5 overflow-hidden rounded-full bg-zinc-200 dark:bg-zinc-800">
                    <div class="h-full rounded-full bg-emerald-500"
                         style="width: {{ round($addedCount / count($presets) * 100) }}%"></div>
                </div>

                <div class="mt-3 flex flex-wrap gap-2">
                    @foreach ($presets as $p)
                        @if (in_array(mb_strtolower($p['name']), $takenNames, true))
                            <span class="{{ $chipTaken }}" title="Already added">
                                <span class="text-emerald-500">✓</span>
                                <span class="line-through">{{ $p['name'] }}</span>
                                <span class="ml-1 text-[10px] uppercase">Added</span>
                            </span>
                        @else
                            <button
                                type="button"
                                class="js-preset {{ $chip }}"
                                data-name="{{ $p['name'] }}"
                                data-min="{{ $p['min'] }}"
                                data-discount="{{ $p['discount'] }}"
                                data-color="{{ $p['color'] }}"
                            >
                                <span class="mr-1 inline-block h-2 w-2 rounded-full"
                                      style="background-color: {{ $p['color'] }}"></span>
                                {{ $p['name'] }}
                                <span class="ml-1 text-xs text-zinc-500">
                                    {{ number_format($p['min']) }} pts · {{ $p['discount'] }}%
                                </span>
                            </button>
                        @endif
                    @endforeach
                </div>
            </div>
        @endunless

        {{-- Quick points / discount --}}
        <div class="grid gap-5 sm:grid-cols-2">
            @unless ($isBaseTier)
                <div>
                    <div class="mb-2 text-xs font-semibold text-zinc-500">
                        Quick points
                    </div>

                    <div class="flex flex-wrap gap-2">
                        @foreach ([500, 1000, 2500, 5000, 10000, 20000] as $value)
                            @if (in_array($value, $takenPoints, true))
                                <span class="{{ $chipTaken }} line-through">
                                    {{ number_format($value) }}
                                </span>
                            @else
                                <button type="button"
                                        class="js-pts {{ $chip }}"
                                        data-value="{{ $value }}">
                                    {{ number_format($value) }}
                                </button>
                            @endif
                        @endforeach
                    </div>
                </div>
            @endunless

            <div>
                <div class="mb-2 text-xs font-semibold text-zinc-500">
                    Quick discount
                </div>

                <div class="flex flex-wrap gap-2">
                    @foreach ([3, 5, 10, 15, 20, 25] as $value)
                        <button type="button"
                                class="js-dis {{ $chip }}"
                                data-value="{{ $value }}">
                            {{ $value }}%
                        </button>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- Save bar --}}
    <div class="flex flex-wrap items-center justify-between gap-3 border-t border-zinc-200 bg-zinc-50 px-5 py-4 dark:border-zinc-800 dark:bg-zinc-900/60 sm:px-6">
        <span id="tier-hint" class="text-sm text-zinc-500">
            Enter a tier name to continue
        </span>

        <div class="flex gap-3">
            <a href="{{ route('admin.tiers.index') }}"
               class="inline-flex h-10 items-center justify-center rounded-xl border border-zinc-300 px-5 text-sm font-semibold dark:border-zinc-700">
                Cancel
            </a>

            <button type="submit"
                    class="inline-flex h-10 items-center justify-center rounded-xl bg-red-600 px-6 text-sm font-semibold text-white transition hover:bg-red-500">
                {{ $isEdit ? 'Save changes' : 'Save tier' }}
            </button>
        </div>
    </div>
</div>

{{-- RIGHT: preview --}}
<aside class="space-y-5">
    <section class="rounded-2xl border border-zinc-200 bg-white p-5 dark:border-zinc-800 dark:bg-zinc-900">
        <div class="flex items-center justify-between text-xs text-zinc-500">
            <span>Live preview</span>
            <span>Updates as you type</span>
        </div>

        <div id="tier-live-preview"
             class="tier-tint relative mt-3 overflow-hidden rounded-xl border border-zinc-200 p-4 dark:border-zinc-800"
             style="--tier-color: {{ $selectedColor }}">
            <div class="tier-bar absolute inset-x-0 top-0 h-1"></div>

            <div class="flex items-center gap-2">
                <span class="tier-badge inline-flex max-w-[75%] items-center gap-1.5 rounded-full px-3 py-1 text-sm font-semibold">
                    <span class="tier-dot h-2 w-2 shrink-0 rounded-full"></span>
                    <span id="pv-name" class="truncate">
                        {{ old('tier_name', $tier->tier_name) ?: 'Tier name' }}
                    </span>
                </span>

                <span class="text-xs text-zinc-500">
                    {{ $isEdit ? '#'.$tier->tier_id : '#NEW' }}
                </span>
            </div>

            <div class="tier-text mt-4 text-5xl font-black italic leading-none">
                <span id="pv-discount">0</span><span class="text-2xl">%</span>
            </div>

            <p class="mt-1 text-sm text-zinc-500">member discount</p>

            <dl class="mt-4 space-y-2 border-t border-zinc-200 pt-3 text-sm dark:border-zinc-800">
                <div class="flex justify-between gap-3">
                    <dt class="text-zinc-500">Points</dt>
                    <dd id="pv-range" class="font-bold">—</dd>
                </div>

                <div class="flex justify-between gap-3">
                    <dt class="text-zinc-500">Color</dt>
                    <dd id="pv-color" class="font-mono">{{ $selectedColor }}</dd>
                </div>

                @unless ($isEdit)
                    <div class="flex justify-between">
                        <dt class="text-zinc-500">Members</dt>
                        <dd class="font-bold">0</dd>
                    </div>
                @endunless
            </dl>
        </div>

        <p id="pv-position" class="mt-3 text-xs text-zinc-500"></p>
    </section>

    <section class="rounded-2xl border border-zinc-200 bg-white p-5 dark:border-zinc-800 dark:bg-zinc-900">
        <h2 class="text-sm font-bold">Current ladder</h2>
        <p class="mt-1 text-xs text-zinc-500">
            Each tier's color, plus where the tier you're editing fits in.
        </p>
        <ol id="ladder" class="relative mt-3 max-h-80 space-y-2 overflow-y-auto pr-1"></ol>
    </section>

    <section class="rounded-2xl border border-zinc-200 bg-white p-5 dark:border-zinc-800 dark:bg-zinc-900">
        <h2 class="text-base font-bold">Tier tips</h2>

        <ol class="mt-3 space-y-3 text-sm">
            @foreach ([
                ['Points rule', 'Every ฿1,000 of completed purchases earns 1 point.'],
                ['Unique minimum', 'Each tier needs a different minimum points value.'],
                ['Higher = better', 'Higher tiers should give a bigger discount.'],
                ['Tier color', 'The saved color is used on every page that reads it from the tier.'],
            ] as $index => [$title, $text])
                <li class="flex gap-3">
                    <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-md bg-red-600/15 text-xs font-bold text-red-600">
                        {{ $index + 1 }}
                    </span>
                    <div>
                        <div class="font-semibold">{{ $title }}</div>
                        <div class="text-xs text-zinc-500">{{ $text }}</div>
                    </div>
                </li>
            @endforeach
        </ol>
    </section>
</aside>

{{-- Behaviour (live preview, ladder, swatches, presets) lives in its own file --}}
@include('admin.tiers._form-script')