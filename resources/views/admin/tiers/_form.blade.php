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

{{-- LEFT: ข้อมูล Tier --}}
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
                    กำหนดชื่อ คะแนนขั้นต่ำ ส่วนลด และสีของระดับสมาชิก
                </p>
            </div>
        </div>

        {{-- ชื่อ --}}
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
            {{-- คะแนน --}}
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
                        ระดับเริ่มต้นของสมาชิกใหม่ต้องมีคะแนนขั้นต่ำเป็น 0
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
                        ยอดซื้อสำเร็จทุก ฿1,000 ได้ 1 คะแนน
                    </p>
                @endif

                @error('min_points')
                    <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- ส่วนลด --}}
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

                <p class="mt-1.5 text-xs text-zinc-500">ตั้งค่าได้ตั้งแต่ 0–100%</p>

                @error('discount_percent')
                    <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        {{-- เลือกสี --}}
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
                กดช่องสีเพื่อเลือกสีเอง หรือกดเลือกจาก 24 สีด้านล่าง
            </p>

            <div class="grid grid-cols-8 gap-2 sm:grid-cols-12">
                @foreach ($palette as $colorName => $hex)
                    <button
                        type="button"
                        data-tier-swatch="{{ $hex }}"
                        title="{{ $colorName }} {{ $hex }}"
                        aria-label="เลือกสี {{ $colorName }}"
                        aria-pressed="{{ $selectedColor === $hex ? 'true' : 'false' }}"
                        class="h-9 w-full rounded-lg border border-zinc-300 transition hover:scale-110 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-red-500 dark:border-zinc-600"
                        style="background-color: {{ $hex }}"
                    ></button>
                @endforeach
            </div>

            @error('color')
                <p class="text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- แจ้งเตือนค่าซ้ำ --}}
        <div id="tier-warning"
             class="hidden rounded-xl border border-red-300 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-900 dark:bg-red-950/30 dark:text-red-300"
             role="alert">
            <p id="tier-warning-text"></p>
        </div>

        @if ($isEdit)
            <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800 dark:border-amber-900 dark:bg-amber-950/25 dark:text-amber-300">
                การเปลี่ยนคะแนนขั้นต่ำจะไม่ย้ายระดับของสมาชิกทันที
                ระบบจะคำนวณระดับใหม่เมื่อคำสั่งซื้อถัดไปสำเร็จ
            </div>
        @endif

        {{-- Tier สำเร็จรูป --}}
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

        {{-- ปุ่มคะแนนและส่วนลด --}}
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

    {{-- ปุ่มบันทึก --}}
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

{{-- RIGHT: Preview --}}
<aside class="space-y-5">
    <section class="rounded-2xl border border-zinc-200 bg-white p-5 dark:border-zinc-800 dark:bg-zinc-900">
        <div class="flex items-center justify-between text-xs text-zinc-500">
            <span>Live preview</span>
            <span>เปลี่ยนตามข้อมูลที่กรอก</span>
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
            แสดงสีของแต่ละ Tier พร้อมตำแหน่งระดับที่กำลังแก้ไข
        </p>
        <ol id="ladder" class="mt-3 space-y-2"></ol>
    </section>

    <section class="rounded-2xl border border-zinc-200 bg-white p-5 dark:border-zinc-800 dark:bg-zinc-900">
        <h2 class="text-base font-bold">Tier tips</h2>

        <ol class="mt-3 space-y-3 text-sm">
            @foreach ([
                ['Points rule', 'ยอดซื้อสำเร็จทุก ฿1,000 ได้ 1 คะแนน'],
                ['Unique minimum', 'คะแนนขั้นต่ำของแต่ละระดับต้องไม่ซ้ำกัน'],
                ['Higher = better', 'ระดับที่สูงขึ้นควรได้รับส่วนลดมากขึ้น'],
                ['Tier color', 'สีที่บันทึกจะใช้ในหน้าที่อ่านสีจาก Tier เดียวกัน'],
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

<script>
    (() => {
        const nameEl = document.getElementById('tier_name');
        const form = nameEl?.closest('form');

        if (!form || form.dataset.tierFormReady === 'true') return;
        form.dataset.tierFormReady = 'true';

        const tiers = @json($ladder);
        const isEdit = @json($isEdit);
        const find = id => form.querySelector('#' + id);

        const ptsEl = find('min_points');
        const disEl = find('discount_percent');
        const colorEl = find('tier_color');
        const swatches = form.querySelectorAll('[data-tier-swatch]');

        const fmt = value => Number(value).toLocaleString('en-US');

        function validColor(value) {
            return /^#[0-9a-f]{6}$/i.test(String(value))
                ? String(value).toUpperCase()
                : '#71717A';
        }

        function contrastColor(hex) {
            const rgb = [1, 3, 5].map(offset =>
                parseInt(hex.slice(offset, offset + 2), 16) / 255
            );

            const linear = rgb.map(value =>
                value <= 0.04045
                    ? value / 12.92
                    : Math.pow((value + 0.055) / 1.055, 2.4)
            );

            const luminance = linear[0] * 0.2126
                + linear[1] * 0.7152
                + linear[2] * 0.0722;

            return luminance > 0.179 ? '#18181B' : '#FFFFFF';
        }

        function read() {
            const points = ptsEl.value === ''
                ? null
                : Number(ptsEl.value);

            const discount = Number(disEl.value);

            return {
                name: nameEl.value.trim(),
                points: points !== null && Number.isFinite(points)
                    ? points
                    : null,
                discount: Number.isFinite(discount) ? discount : 0,
                color: validColor(colorEl.value),
            };
        }

        function render() {
            const data = read();

            find('pv-name').textContent = data.name || 'Tier name';
            find('pv-discount').textContent = String(
                Number(data.discount.toFixed(2))
            );
            find('pv-color').textContent = data.color;
            find('tier-color-code').textContent = data.color;

            const preview = find('tier-live-preview');
            preview.style.setProperty('--tier-color', data.color);
            preview.style.setProperty(
                '--tier-ink',
                contrastColor(data.color)
            );

            swatches.forEach(button => {
                const selected =
                    validColor(button.dataset.tierSwatch) === data.color;

                button.setAttribute('aria-pressed', String(selected));
                button.style.outline = selected
                    ? '2px solid #EF4444'
                    : '';
                button.style.outlineOffset = selected ? '3px' : '';
            });

            const sameName = data.name && tiers.find(tier =>
                tier.name.trim().toLowerCase() === data.name.toLowerCase()
            );

            const samePoints = data.points !== null && tiers.find(tier =>
                tier.min === data.points
            );

            const rows = tiers.map(tier => ({
                ...tier,
                isMine: false,
            }));

            if (data.points !== null) {
                rows.push({
                    id: 'mine',
                    name: data.name || (isEdit ? 'This tier' : 'New tier'),
                    min: data.points,
                    discount: data.discount,
                    color: data.color,
                    isMine: true,
                });
            }

            rows.sort((a, b) =>
                a.min - b.min || Number(a.isMine) - Number(b.isMine)
            );

            rows.forEach((row, index) => {
                row.max = rows[index + 1]
                    ? rows[index + 1].min - 1
                    : null;
            });

            const rangeOf = row => row.max === null
                ? fmt(row.min) + '+'
                : fmt(row.min) + ' – ' + fmt(Math.max(row.max, row.min));

            const mine = rows.find(row => row.isMine);

            find('pv-range').textContent = mine ? rangeOf(mine) : '—';

            const position = find('pv-position');

            if (!mine) {
                position.textContent = 'กรอกคะแนนขั้นต่ำเพื่อดูตำแหน่งระดับ';
            } else {
                const index = rows.indexOf(mine);
                const below = rows[index - 1];
                const above = rows[index + 1];

                position.textContent = !below && !above
                    ? 'เป็นระดับเดียวในระบบ'
                    : !above
                        ? 'ระดับสูงสุด — อยู่เหนือ ' + below.name
                        : !below
                            ? 'ระดับเริ่มต้น — อยู่ก่อน ' + above.name
                            : 'อยู่ระหว่าง ' + below.name + ' และ ' + above.name;

                if (below && data.discount < below.discount) {
                    position.textContent +=
                        ' · ส่วนลดต่ำกว่าระดับก่อนหน้า (' + below.discount + '%)';
                }
            }

            // สร้าง DOM โดยใช้ textContent เพื่อแสดงชื่อ Tier อย่างปลอดภัย
            const ladder = find('ladder');
            ladder.replaceChildren();

            rows.forEach(row => {
                const item = document.createElement('li');
                const color = validColor(row.color);

                item.className =
                    'flex items-center justify-between gap-3 rounded-lg border px-3 py-2 text-sm';

                item.style.setProperty('--tier-color', color);

                if (row.isMine) {
                    item.style.backgroundColor = color;
                    item.style.borderColor = color;
                    item.style.color = contrastColor(color);
                } else {
                    item.classList.add(
                        'border-zinc-200',
                        'bg-zinc-50',
                        'dark:border-zinc-700',
                        'dark:bg-zinc-800/60'
                    );
                }

                const info = document.createElement('div');
                info.className = 'min-w-0';

                const title = document.createElement('div');
                title.className = 'flex items-center gap-2 font-semibold';

                const dot = document.createElement('span');
                dot.className = 'h-2 w-2 shrink-0 rounded-full';
                dot.style.backgroundColor = row.isMine
                    ? contrastColor(color)
                    : color;

                const name = document.createElement('span');
                name.className = 'truncate';
                name.textContent = row.name;

                title.append(dot, name);

                if (row.isMine) {
                    const tag = document.createElement('span');
                    tag.className =
                        'shrink-0 text-[10px] font-bold uppercase opacity-80';
                    tag.textContent = isEdit ? 'Editing' : 'New';
                    title.append(tag);
                }

                const range = document.createElement('div');
                range.className = row.isMine
                    ? 'mt-1 text-xs opacity-80'
                    : 'mt-1 text-xs text-zinc-500';
                range.textContent = rangeOf(row) + ' pts';

                const discount = document.createElement('span');
                discount.className = 'shrink-0 font-bold';
                discount.textContent = row.discount + '%';

                info.append(title, range);
                item.append(info, discount);
                ladder.append(item);
            });

            if (rows.length === 0) {
                const empty = document.createElement('li');
                empty.className = 'text-xs text-zinc-500';
                empty.textContent = 'No tiers yet.';
                ladder.append(empty);
            }

            const message = sameName
                ? 'มีระดับชื่อ "' + sameName.name + '" อยู่แล้ว'
                : samePoints
                    ? samePoints.name + ' ใช้คะแนนขั้นต่ำ '
                        + fmt(data.points) + ' แล้ว กรุณาเลือกคะแนนอื่น'
                    : '';

            find('tier-warning').classList.toggle('hidden', !message);
            find('tier-warning-text').textContent = message;

            find('tier-hint').textContent = message
                ? 'แก้ไขข้อมูลที่ซ้ำก่อนบันทึก'
                : !data.name
                    ? 'กรอกชื่อระดับสมาชิก'
                    : data.points === null
                        ? 'กรอกคะแนนขั้นต่ำ'
                        : 'พร้อมบันทึก';
        }

        form.querySelectorAll('.js-preset').forEach(button => {
            button.addEventListener('click', () => {
                nameEl.value = button.dataset.name;

                if (!ptsEl.disabled) {
                    ptsEl.value = button.dataset.min;
                }

                disEl.value = button.dataset.discount;
                colorEl.value = button.dataset.color;
                render();
            });
        });

        form.querySelectorAll('.js-pts').forEach(button => {
            button.addEventListener('click', () => {
                if (!ptsEl.disabled) {
                    ptsEl.value = button.dataset.value;
                    render();
                }
            });
        });

        form.querySelectorAll('.js-dis').forEach(button => {
            button.addEventListener('click', () => {
                disEl.value = button.dataset.value;
                render();
            });
        });

        swatches.forEach(button => {
            button.addEventListener('click', () => {
                colorEl.value = button.dataset.tierSwatch;
                render();
            });
        });

        [nameEl, ptsEl, disEl, colorEl].forEach(input => {
            input.addEventListener('input', render);
            input.addEventListener('change', render);
        });

        render();
    })();
</script>