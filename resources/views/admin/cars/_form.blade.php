{{-- Shared by Add car and Edit car. Same field names as before, so the controller is unchanged. --}}
@php
    use Illuminate\Support\Str;

    $isEdit   = (bool) ($car->exists ?? false);
    // Same image logic as the cars list and the storefront (falls back to image_url)
    $imageSrc = $isEdit
        ? (\App\Support\CarVisual::imageSrc($car) ?: ($car->image_url ?? null))
        : null;

    // ---------- Styles ----------
    $card   = 'rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900';
    $step   = 'grid h-8 w-8 shrink-0 place-items-center rounded-lg bg-red-600 text-sm font-bold text-white';
    $h2     = 'text-base font-semibold text-zinc-900 dark:text-white';
    $label  = 'mb-2 block text-sm font-medium text-zinc-600 dark:text-zinc-300';
    $hint   = 'mt-2 text-xs text-zinc-500';
    $error  = 'mt-2 text-xs font-medium text-red-600 dark:text-red-400';
    $input  = 'h-11 w-full rounded-xl border border-zinc-200 bg-zinc-50 px-3.5 text-sm text-zinc-900 placeholder:text-zinc-400
               focus:border-red-500 focus:outline-none focus:ring-2 focus:ring-red-500/20
               dark:border-zinc-700 dark:bg-zinc-800 dark:text-white';
    $tile   = 'rounded-xl border border-zinc-200 bg-white transition hover:border-zinc-400 dark:border-zinc-700 dark:bg-zinc-900 dark:hover:border-zinc-500
               peer-focus-visible:ring-2 peer-focus-visible:ring-red-500/50';
    $tileOn = 'peer-checked:border-red-500 peer-checked:bg-red-50 peer-checked:ring-2 peer-checked:ring-red-500/20 dark:peer-checked:bg-red-950/30';

    // ---------- Option colors: every choice gets its own hue ----------
    $hues = [
        'sky' => ['tile' => 'border-sky-200 bg-sky-50/70 dark:border-sky-900/40 dark:bg-sky-950/20', 'on' => 'peer-checked:border-sky-500 peer-checked:bg-sky-100 peer-checked:ring-2 peer-checked:ring-sky-500/30 dark:peer-checked:bg-sky-950/50', 'chip' => 'bg-sky-500 text-white', 'ic' => 'text-sky-500 dark:text-sky-400'],
        'emerald' => ['tile' => 'border-emerald-200 bg-emerald-50/70 dark:border-emerald-900/40 dark:bg-emerald-950/20', 'on' => 'peer-checked:border-emerald-500 peer-checked:bg-emerald-100 peer-checked:ring-2 peer-checked:ring-emerald-500/30 dark:peer-checked:bg-emerald-950/50', 'chip' => 'bg-emerald-500 text-white', 'ic' => 'text-emerald-500 dark:text-emerald-400'],
        'orange' => ['tile' => 'border-orange-200 bg-orange-50/70 dark:border-orange-900/40 dark:bg-orange-950/20', 'on' => 'peer-checked:border-orange-500 peer-checked:bg-orange-100 peer-checked:ring-2 peer-checked:ring-orange-500/30 dark:peer-checked:bg-orange-950/50', 'chip' => 'bg-orange-500 text-white', 'ic' => 'text-orange-500 dark:text-orange-400'],
        'violet' => ['tile' => 'border-violet-200 bg-violet-50/70 dark:border-violet-900/40 dark:bg-violet-950/20', 'on' => 'peer-checked:border-violet-500 peer-checked:bg-violet-100 peer-checked:ring-2 peer-checked:ring-violet-500/30 dark:peer-checked:bg-violet-950/50', 'chip' => 'bg-violet-500 text-white', 'ic' => 'text-violet-500 dark:text-violet-400'],
        'pink' => ['tile' => 'border-pink-200 bg-pink-50/70 dark:border-pink-900/40 dark:bg-pink-950/20', 'on' => 'peer-checked:border-pink-500 peer-checked:bg-pink-100 peer-checked:ring-2 peer-checked:ring-pink-500/30 dark:peer-checked:bg-pink-950/50', 'chip' => 'bg-pink-500 text-white', 'ic' => 'text-pink-500 dark:text-pink-400'],
        'rose' => ['tile' => 'border-rose-200 bg-rose-50/70 dark:border-rose-900/40 dark:bg-rose-950/20', 'on' => 'peer-checked:border-rose-500 peer-checked:bg-rose-100 peer-checked:ring-2 peer-checked:ring-rose-500/30 dark:peer-checked:bg-rose-950/50', 'chip' => 'bg-rose-500 text-white', 'ic' => 'text-rose-500 dark:text-rose-400'],
        'teal' => ['tile' => 'border-teal-200 bg-teal-50/70 dark:border-teal-900/40 dark:bg-teal-950/20', 'on' => 'peer-checked:border-teal-500 peer-checked:bg-teal-100 peer-checked:ring-2 peer-checked:ring-teal-500/30 dark:peer-checked:bg-teal-950/50', 'chip' => 'bg-teal-500 text-white', 'ic' => 'text-teal-500 dark:text-teal-400'],
        'indigo' => ['tile' => 'border-indigo-200 bg-indigo-50/70 dark:border-indigo-900/40 dark:bg-indigo-950/20', 'on' => 'peer-checked:border-indigo-500 peer-checked:bg-indigo-100 peer-checked:ring-2 peer-checked:ring-indigo-500/30 dark:peer-checked:bg-indigo-950/50', 'chip' => 'bg-indigo-500 text-white', 'ic' => 'text-indigo-500 dark:text-indigo-400'],
    ];
    $hueKeys = array_keys($hues);
    $hueAt   = fn ($i) => $hues[$hueKeys[abs((int) $i) % count($hueKeys)]];

    $catHue = function (string $name, $id) use ($hues, $hueAt) {
        $n = Str::lower($name);
        return match (true) {
            Str::contains($n, 'sedan')               => $hues['sky'],
            Str::contains($n, ['suv', 'crossover'])  => $hues['emerald'],
            Str::contains($n, ['pickup', 'truck'])   => $hues['orange'],
            Str::contains($n, 'hatch')               => $hues['pink'],
            Str::contains($n, 'coupe')               => $hues['rose'],
            Str::contains($n, 'electric')            => $hues['teal'],
            Str::contains($n, 'van')                 => $hues['violet'],
            default                                  => $hueAt($id),
        };
    };

    // Small red check in the corner of the selected option
    $chk = '<span class="pointer-events-none absolute right-1.5 top-1.5 grid h-4 w-4 place-items-center rounded-full bg-red-600 text-white opacity-0 shadow transition peer-checked:opacity-100"><svg viewBox="0 0 12 12" class="h-2.5 w-2.5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2.5 6.5l2.2 2.2L9.5 3.8"/></svg></span>';
    $tileBase = 'rounded-xl border transition hover:brightness-95 dark:hover:brightness-125 peer-focus-visible:ring-2 peer-focus-visible:ring-red-500/50';

    // ---------- Values (old input wins, then the car) ----------
    $state = [
        'model_name'    => (string) old('model_name', $car->model_name),
        'brand_id'      => (string) old('brand_id', $car->brand_id),
        'category_id'   => (string) old('category_id', $car->category_id),
        'model_year'    => (string) old('model_year', $car->model_year),
        'color'         => (string) old('color', $car->color),
        'fuel_type'     => (string) old('fuel_type', $car->fuel_type),
        'transmission'  => (string) old('transmission', $car->transmission),
        'car_condition' => (string) old('car_condition', $car->car_condition),
        'engine_cc'     => (string) old('engine_cc', $car->engine_cc),
        'mileage_km'    => (string) old('mileage_km', $car->mileage_km ?? 0),
        'price'         => (string) old('price', $car->price),
        'stock_qty'     => (string) old('stock_qty', $car->stock_qty ?? 0),
        'description'   => (string) old('description', $car->description),
        'remove_image'  => (bool) old('remove_image'),
    ];

    $brandMap = $brands->mapWithKeys(fn ($b) => [(string) $b->brand_id => $b->brand_name])->all();
    $catMap   = $categories->mapWithKeys(fn ($c) => [(string) $c->category_id => $c->category_name])->all();

    // Color name -> hex, for the swatches and the little dot
    $colorHex = [
        'white' => '#ffffff', 'black' => '#18181b', 'silver' => '#c4c7cc', 'grey' => '#71717a', 'gray' => '#71717a',
        'red' => '#dc2626', 'blue' => '#2563eb', 'green' => '#16a34a', 'yellow' => '#facc15', 'orange' => '#f97316',
        'brown' => '#92400e', 'gold' => '#d4a017', 'beige' => '#d6c7a1', 'purple' => '#7c3aed', 'pink' => '#ec4899',
    ];
    $swatches = ['White', 'Black', 'Silver', 'Grey', 'Red', 'Blue', 'Green', 'Yellow', 'Orange', 'Brown', 'Gold', 'Beige'];

    // ---------- Category: side-view car illustrations (viewBox 0 0 120 50, facing right) ----------
    $carParts = [
        'sedan' => [
            'body' => 'M8 38V31C8 29.5 9 28.5 10.5 28L28 25L40 15.5C41.5 14.3 43 14 45 14H72C74.5 14 76.5 14.8 78 16.2L90 25.5L106 28C109 28.6 112 30.5 112 34V38Z',
            'win'  => ['M42 24L48.5 17H58.5V24Z', 'M62 24V17H73C74 17 75 17.4 76 18.2L83 24Z'],
        ],
        'coupe' => [
            'body' => 'M8 38V32C8 30.5 9 29.5 10.5 29L30 26L46 16.5C47.5 15.6 49 15.3 51 15.3H66C69 15.3 71.5 16.3 73.5 18.2L88 26.5L106 29C109.5 29.6 112 31.5 112 35V38Z',
            'win'  => ['M49 25L54 18.5H65C66.5 18.5 67.5 19 68.7 20L76 25.5Z'],
        ],
        'electric' => [
            'body' => 'M8 38V32C8 30 9.5 28.8 11 28.4L30 25C36 17 42 13.5 50 13.5H66C73 13.5 80 18 86 25L104 28C109.5 29 112 31 112 35V38Z',
            'win'  => ['M44 24.5C48 18 52 16.8 56 16.8H58.5V24.5Z', 'M62 24.5V16.8H66C70 16.8 74 19 79 24.5Z'],
            'extra'=> '<path d="M61 27L57 33H60L58.5 37.5L63.5 31H60.5Z" fill="#fff" fill-opacity=".85"/>',
        ],
        'hatchback' => [
            'body' => 'M10 38V27C10 25.5 11 24.5 12.5 24L18 22L24 13.5C25 12.5 26.5 12 28 12H66C68.5 12 70.5 13 72 14.6L86 26L106 28.5C109.5 29.2 112 31 112 34.5V38Z',
            'win'  => ['M27 21.5L30.5 14.5H44V21.5Z', 'M48 21.5V14.5H64.5C65.5 14.5 66.5 14.9 67.5 15.7L76 21.5Z'],
        ],
        'pickup' => [
            'body' => 'M8 38V28C8 26.8 8.8 26 10 26H62V17C62 15.5 63 14.5 64.5 14L74 12H80C82 12 83.5 12.8 84.5 14.2L92 25L106 28C109.5 28.7 112 30.5 112 34V38Z',
            'win'  => ['M66 24V16.5L72 15H79C80.5 15 81.5 15.6 82.5 17L87 24Z'],
            'extra'=> '<rect x="10" y="26" width="50" height="3" fill="#fff" fill-opacity=".25"/>',
        ],
        'suv' => [
            'body' => 'M8 38V28C8 26.5 9 25.5 10.5 25L24 22.5L30 13.5C31 12.5 32 12 33.5 12H76C78 12 79.5 12.8 80.5 14.2L90 25.5L106 28C109.5 28.7 112 30.5 112 34V38Z',
            'win'  => ['M32 21.5L36 15H48V21.5Z', 'M52 21.5V15H64V21.5Z', 'M68 21.5V15H76C77 15 77.8 15.4 78.5 16.2L84 21.5Z'],
        ],
        'van' => [
            'body' => 'M8 38V16C8 13.5 10 12 12.5 12H84C86 12 87.5 12.8 88.5 14L103 28C109.5 29 112 31 112 34.5V38Z',
            'win'  => ['M14 20.5V15.5H28V20.5Z', 'M32 20.5V15.5H48V20.5Z', 'M52 20.5V15.5H68V20.5Z', 'M72 21V15.5H83.5C84.5 15.5 85.3 15.9 86 16.7L92 21Z'],
        ],
    ];

    $carSvg = function (string $type) use ($carParts) {
        $p   = $carParts[$type] ?? $carParts['sedan'];
        $out = '<ellipse cx="60" cy="46" rx="48" ry="2" fill="currentColor" opacity=".15"/>'
             . '<path d="'.$p['body'].'" fill="currentColor"/>';

        foreach ($p['win'] as $w) {
            $out .= '<path d="'.$w.'" fill="#fff" fill-opacity=".5"/>';
        }

        $out .= $p['extra'] ?? '';

        foreach ([28, 92] as $x) {
            $out .= '<circle cx="'.$x.'" cy="38" r="7.5" fill="#18181b" stroke="currentColor" stroke-width="1.5"/>'
                  . '<circle cx="'.$x.'" cy="38" r="3" fill="#a1a1aa"/>';
        }

        return $out;
    };

    $shapeFor = function (string $name) {
        $n = Str::lower($name);
        return match (true) {
            Str::contains($n, ['electric'])                => 'electric',
            Str::contains($n, ['pickup', 'truck'])         => 'pickup',
            Str::contains($n, ['suv', 'crossover', '4x4']) => 'suv',
            Str::contains($n, ['hatch'])                   => 'hatchback',
            Str::contains($n, ['coupe'])                   => 'coupe',
            Str::contains($n, ['van'])                     => 'van',
            default                                        => 'sedan',
        };
    };

    // ---------- Fuel: icon + color ----------
    $fuelIcons = [
        'bolt'  => '<path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z"/>',
        'drop'  => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 3.5C12 3.5 5.5 10.5 5.5 14.5a6.5 6.5 0 0013 0C18.5 10.5 12 3.5 12 3.5z"/>',
        'leaf'  => '<path stroke-linecap="round" stroke-linejoin="round" d="M5 19c0-8 5-13.5 14-14 0 9-5 14-14 14z"/><path stroke-linecap="round" d="M5 19l8-8"/>',
        'pump'  => '<path stroke-linecap="round" stroke-linejoin="round" d="M4 20V5.5A1.5 1.5 0 015.5 4h7A1.5 1.5 0 0114 5.5V20M3 20h12M14 9h2.2a1.8 1.8 0 011.8 1.8v5.2a1.5 1.5 0 003 0V9l-2.5-2.5"/><path stroke-linecap="round" d="M7 8h4"/>',
    ];
    $fuelTheme = function (string $name) use ($hues) {
        $n = Str::lower($name);
        return match (true) {
            Str::contains($n, ['electric']) || $n === 'ev' => ['icon' => 'bolt', 'h' => $hues['sky']],
            Str::contains($n, ['hybrid'])                  => ['icon' => 'leaf', 'h' => $hues['emerald']],
            Str::contains($n, ['diesel'])                  => ['icon' => 'drop', 'h' => $hues['indigo']],
            Str::contains($n, ['gas', 'petrol', 'benzin']) => ['icon' => 'pump', 'h' => $hues['orange']],
            default                                        => ['icon' => 'pump', 'h' => $hues['violet']],
        };
    };

    // ---------- Condition colors ----------
    $condOn = function (string $name) {
        return match (Str::lower($name)) {
            'new'   => 'peer-checked:bg-emerald-600 peer-checked:text-white',
            'used'  => 'peer-checked:bg-amber-500 peer-checked:text-white',
            default => 'peer-checked:bg-red-600 peer-checked:text-white',
        };
    };

    $initState = [
        'f'        => $state,
        'brands'   => $brandMap,
        'cats'     => $catMap,
        'swatches' => $colorHex,
        'hasImage' => (bool) $imageSrc,
    ];
@endphp

<style>[x-cloak]{display:none !important}</style>

<div class="grid items-start gap-6 lg:grid-cols-[minmax(0,1fr)_21rem]"
     x-data="{
        ...@js($initState),
        preview: null,
        fileName: '',
        get brandName() { return this.brands[this.f.brand_id] || '' },
        get catName() { return this.cats[this.f.category_id] || '' },
        get hex() { return this.swatches[(this.f.color || '').trim().toLowerCase()] || null },
        get showExisting() { return this.hasImage && !this.f.remove_image && !this.preview },
        get showPlaceholder() { return !this.preview && !this.showExisting },
        get priceLabel() {
            const n = parseFloat(this.f.price);
            return isNaN(n) ? '฿ —' : '฿' + n.toLocaleString(undefined, { maximumFractionDigits: 2 });
        },
        get engineLabel() {
            if (this.f.engine_cc === '') return '';
            return Number(this.f.engine_cc) === 0 ? 'Electric' : Number(this.f.engine_cc).toLocaleString() + ' cc';
        },
        get stock() {
            const q = parseInt(this.f.stock_qty) || 0;
            if (q <= 0) return { text: 'Out of stock', cls: 'bg-red-100 text-red-700 dark:bg-red-950/60 dark:text-red-300', dot: 'bg-red-500' };
            if (q === 1) return { text: 'Only 1 left', cls: 'bg-red-100 text-red-700 dark:bg-red-950/60 dark:text-red-300', dot: 'bg-red-500' };
            if (q < 3) return { text: q + ' left · low stock', cls: 'bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300', dot: 'bg-amber-500' };
            return { text: q + ' in stock', cls: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300', dot: 'bg-emerald-500' };
        },
        set(k, v) { this.f[k] = String(v) },
        step(n) { this.f.stock_qty = String(Math.max(0, Math.min(100000, (parseInt(this.f.stock_qty) || 0) + n))) },
        pickFile(e) {
            const x = e.target.files[0];
            this.preview = x ? URL.createObjectURL(x) : null;
            this.fileName = x ? x.name : '';
        },
     }">

    {{-- ================= LEFT: build the car step by step ================= --}}
    <div class="space-y-5">

        {{-- 1 · Model & brand --}}
        <section class="{{ $card }}">
            <div class="mb-5 flex items-center gap-3">
                <span class="{{ $step }}">1</span>
                <div>
                    <h2 class="{{ $h2 }}">Model &amp; brand</h2>
                    <p class="text-xs text-zinc-500">What is this car?</p>
                </div>
            </div>

            <div>
                <label for="model_name" class="{{ $label }}">Model name <span class="text-red-500">*</span></label>
                <input id="model_name" name="model_name" type="text" required maxlength="255"
                       x-model="f.model_name" placeholder="e.g. Camry 2.5 HEV"
                       class="{{ $input }} !h-12 text-base font-semibold">
                @error('model_name') <p class="{{ $error }}">{{ $message }}</p> @enderror
            </div>

            <div class="mt-5">
                <span class="{{ $label }}">Brand <span class="text-red-500">*</span></span>

                <div class="grid max-h-60 grid-cols-2 gap-2 overflow-y-auto pr-1 sm:grid-cols-3 md:grid-cols-4">
                    @foreach ($brands as $brand)
                        @php $h = $hueAt($brand->brand_id); @endphp
                        <label class="relative cursor-pointer">
                            <input type="radio" name="brand_id" value="{{ $brand->brand_id }}" x-model="f.brand_id" required class="peer sr-only">
                            <div class="flex items-center gap-2.5 p-2.5 {{ $tileBase }} {{ $h['tile'] }} {{ $h['on'] }}">
                                <span class="grid h-8 w-8 shrink-0 place-items-center rounded-lg text-xs font-bold {{ $h['chip'] }}">
                                    {{ Str::upper(Str::substr($brand->brand_name, 0, 1)) }}
                                </span>
                                <span class="truncate pr-4 text-sm font-semibold text-zinc-900 dark:text-white">{{ $brand->brand_name }}</span>
                            </div>
                            {!! $chk !!}
                        </label>
                    @endforeach
                </div>
                @if ($brands->isEmpty())
                    <p class="{{ $hint }}">No brands yet. Add a brand first, then come back to add the car.</p>
                @endif
                @error('brand_id') <p class="{{ $error }}">{{ $message }}</p> @enderror
            </div>

            <div class="mt-5">
                <span class="{{ $label }}">Category <span class="text-red-500">*</span></span>

                <div class="grid grid-cols-2 gap-2 sm:grid-cols-4">
                    @foreach ($categories as $category)
                        @php $h = $catHue((string) $category->category_name, $category->category_id); @endphp
                        <label class="relative cursor-pointer">
                            <input type="radio" name="category_id" value="{{ $category->category_id }}" x-model="f.category_id" required class="peer sr-only">
                            <div class="flex flex-col items-center gap-1.5 px-2 py-3 {{ $h['ic'] }} {{ $tileBase }} {{ $h['tile'] }} {{ $h['on'] }}">
                                <svg viewBox="0 0 120 50" class="h-11 w-full" aria-hidden="true">{!! $carSvg($shapeFor((string) $category->category_name)) !!}</svg>
                                <span class="text-sm font-semibold text-zinc-900 dark:text-white">{{ $category->category_name }}</span>
                            </div>
                            {!! $chk !!}
                        </label>
                    @endforeach
                </div>
                @error('category_id') <p class="{{ $error }}">{{ $message }}</p> @enderror
            </div>
        </section>

        {{-- 2 · Year & color --}}
        <section class="{{ $card }}">
            <div class="mb-5 flex items-center gap-3">
                <span class="{{ $step }}">2</span>
                <div>
                    <h2 class="{{ $h2 }}">Year &amp; color</h2>
                    <p class="text-xs text-zinc-500">How does it look?</p>
                </div>
            </div>

            <div class="grid gap-6 sm:grid-cols-2">
                <div>
                    <label for="model_year" class="{{ $label }}">Model year <span class="text-red-500">*</span></label>
                    <input id="model_year" name="model_year" type="number" required min="1900" max="{{ now()->year + 1 }}" step="1"
                           x-model="f.model_year" placeholder="{{ now()->year }}"
                           class="{{ $input }} !h-12 text-lg font-semibold tabular-nums">
                    <div class="mt-2 flex flex-wrap gap-1.5">
                        @foreach (range(now()->year + 1, now()->year - 4) as $y)
                            <button type="button" x-on:click="set('model_year', {{ $y }})"
                                    x-bind:class="f.model_year == {{ $y }} ? 'border-red-500 bg-red-600 text-white' : 'border-zinc-200 text-zinc-600 hover:border-zinc-400 dark:border-zinc-700 dark:text-zinc-300'"
                                    class="rounded-full border px-2.5 py-1 text-xs font-semibold tabular-nums transition">{{ $y }}</button>
                        @endforeach
                    </div>
                    @error('model_year') <p class="{{ $error }}">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="color" class="{{ $label }}">Color <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <span class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 rounded-full ring-1 ring-zinc-300 dark:ring-zinc-600"
                              x-bind:style="'background:' + (hex || 'transparent')"></span>
                        <input id="color" name="color" type="text" required maxlength="100"
                               x-model="f.color" placeholder="e.g. Pearl White"
                               class="{{ $input }} !h-12 pl-10">
                    </div>
                    <div class="mt-3 flex flex-wrap gap-2">
                        @foreach ($swatches as $name)
                            <button type="button" title="{{ $name }}" aria-label="{{ $name }}"
                                    x-on:click="set('color', '{{ $name }}')"
                                    x-bind:class="(f.color || '').trim().toLowerCase() === '{{ Str::lower($name) }}' ? 'ring-2 ring-red-500 ring-offset-2 ring-offset-white dark:ring-offset-zinc-900' : 'ring-1 ring-zinc-300 hover:ring-zinc-400 dark:ring-zinc-600'"
                                    class="h-7 w-7 rounded-full transition"
                                    style="background: {{ $colorHex[Str::lower($name)] }}"></button>
                        @endforeach
                    </div>
                    @error('color') <p class="{{ $error }}">{{ $message }}</p> @enderror
                </div>
            </div>
        </section>

        {{-- 3 · Specs --}}
        <section class="{{ $card }}">
            <div class="mb-5 flex items-center gap-3">
                <span class="{{ $step }}">3</span>
                <div>
                    <h2 class="{{ $h2 }}">Specs</h2>
                    <p class="text-xs text-zinc-500">Engine, fuel and condition</p>
                </div>
            </div>

            <div>
                <span class="{{ $label }}">Fuel type <span class="text-red-500">*</span></span>
                <div class="grid grid-cols-2 gap-2 sm:grid-cols-4">
                    @foreach ($fuelTypes as $option)
                        @php $ft = $fuelTheme((string) $option); @endphp
                        <label class="relative cursor-pointer">
                            <input type="radio" name="fuel_type" value="{{ $option }}" x-model="f.fuel_type" required class="peer sr-only">
                            <div class="flex flex-col items-center gap-1.5 px-2 py-3 {{ $ft['h']['ic'] }} {{ $tileBase }} {{ $ft['h']['tile'] }} {{ $ft['h']['on'] }}">
                                <svg viewBox="0 0 24 24" class="h-7 w-7" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true">{!! $fuelIcons[$ft['icon']] !!}</svg>
                                <span class="text-sm font-semibold text-zinc-900 dark:text-white">{{ $option }}</span>
                            </div>
                            {!! $chk !!}
                        </label>
                    @endforeach
                </div>
                @error('fuel_type') <p class="{{ $error }}">{{ $message }}</p> @enderror
            </div>

            <div class="mt-5 grid gap-5 sm:grid-cols-2">
                <div>
                    <span class="{{ $label }}">Transmission <span class="text-red-500">*</span></span>
                    <div class="grid gap-2" style="grid-template-columns: repeat({{ max(1, min(3, count($transmissions))) }}, minmax(0, 1fr))">
                        @foreach ($transmissions as $option)
                            @php $h = $hues[['indigo', 'orange', 'teal'][$loop->index % 3]]; @endphp
                            <label class="relative cursor-pointer">
                                <input type="radio" name="transmission" value="{{ $option }}" x-model="f.transmission" required class="peer sr-only">
                                <div class="flex items-center gap-2.5 p-2.5 {{ $tileBase }} {{ $h['tile'] }} {{ $h['on'] }}">
                                    <span class="grid h-8 w-8 shrink-0 place-items-center rounded-lg text-sm font-bold {{ $h['chip'] }}">
                                        {{ Str::upper(Str::substr((string) $option, 0, 1)) }}
                                    </span>
                                    <span class="truncate pr-4 text-sm font-semibold text-zinc-900 dark:text-white">{{ $option }}</span>
                                </div>
                                {!! $chk !!}
                            </label>
                        @endforeach
                    </div>
                    @error('transmission') <p class="{{ $error }}">{{ $message }}</p> @enderror
                </div>

                <div>
                    <span class="{{ $label }}">Condition <span class="text-red-500">*</span></span>
                    <div class="grid gap-1 rounded-xl bg-zinc-100 p-1 dark:bg-zinc-800" style="grid-template-columns: repeat({{ max(1, count($conditions)) }}, minmax(0, 1fr))">
                        @foreach ($conditions as $option)
                            <label class="cursor-pointer">
                                <input type="radio" name="car_condition" value="{{ $option }}" x-model="f.car_condition" required class="peer sr-only">
                                <span class="block rounded-lg py-2.5 text-center text-sm font-semibold text-zinc-600 transition dark:text-zinc-400
                                             peer-checked:shadow-sm peer-focus-visible:ring-2 peer-focus-visible:ring-red-500/50 {{ $condOn((string) $option) }}">
                                    {{ $option }}
                                </span>
                            </label>
                        @endforeach
                    </div>
                    @error('car_condition') <p class="{{ $error }}">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="mt-5 grid gap-5 sm:grid-cols-2">
                <div>
                    <label for="engine_cc" class="{{ $label }}">Engine size <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <input id="engine_cc" name="engine_cc" type="number" required min="0" max="20000" step="1"
                               x-model="f.engine_cc" class="{{ $input }} pr-12">
                        <span class="pointer-events-none absolute inset-y-0 right-3.5 flex items-center text-xs text-zinc-400">cc</span>
                    </div>
                    <div class="mt-2 flex flex-wrap gap-1.5">
                        <button type="button" x-on:click="set('engine_cc', 0)"
                                x-bind:class="f.engine_cc !== '' && Number(f.engine_cc) === 0 ? 'border-sky-500 bg-sky-500 text-white' : 'border-zinc-200 text-zinc-600 hover:border-zinc-400 dark:border-zinc-700 dark:text-zinc-300'"
                                class="rounded-full border px-2.5 py-1 text-xs font-semibold transition">Electric · 0</button>
                        @foreach ([1000, 1500, 2000, 2500, 3000] as $cc)
                            <button type="button" x-on:click="set('engine_cc', {{ $cc }})"
                                    x-bind:class="f.engine_cc == {{ $cc }} ? 'border-red-500 bg-red-600 text-white' : 'border-zinc-200 text-zinc-600 hover:border-zinc-400 dark:border-zinc-700 dark:text-zinc-300'"
                                    class="rounded-full border px-2.5 py-1 text-xs font-semibold tabular-nums transition">{{ number_format($cc) }}</button>
                        @endforeach
                    </div>
                    @error('engine_cc') <p class="{{ $error }}">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="mileage_km" class="{{ $label }}">Mileage <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <input id="mileage_km" name="mileage_km" type="number" required min="0" max="2000000" step="1"
                               x-model="f.mileage_km" class="{{ $input }} pr-12">
                        <span class="pointer-events-none absolute inset-y-0 right-3.5 flex items-center text-xs text-zinc-400">km</span>
                    </div>
                    <p class="{{ $hint }}">New cars are usually 0 km.</p>
                    @error('mileage_km') <p class="{{ $error }}">{{ $message }}</p> @enderror
                </div>
            </div>
        </section>

        {{-- 4 · Price & stock --}}
        <section class="{{ $card }}">
            <div class="mb-5 flex items-center gap-3">
                <span class="{{ $step }}">4</span>
                <div>
                    <h2 class="{{ $h2 }}">Price &amp; stock</h2>
                    <p class="text-xs text-zinc-500">How much, and how many?</p>
                </div>
            </div>

            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <label for="price" class="{{ $label }}">Price <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <span class="pointer-events-none absolute inset-y-0 left-4 flex items-center text-lg font-semibold text-zinc-400">฿</span>
                        <input id="price" name="price" type="number" required min="0.01" step="0.01"
                               x-model="f.price" placeholder="0.00"
                               class="{{ $input }} !h-14 pl-10 text-2xl font-bold tabular-nums">
                    </div>
                    @error('price') <p class="{{ $error }}">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="stock_qty" class="{{ $label }}">Stock <span class="text-red-500">*</span></label>
                    <div class="flex h-14 items-stretch overflow-hidden rounded-xl border border-zinc-200 bg-zinc-50
                                focus-within:border-red-500 focus-within:ring-2 focus-within:ring-red-500/20
                                dark:border-zinc-700 dark:bg-zinc-800">
                        <button type="button" x-on:click="step(-1)" aria-label="Decrease stock"
                                class="w-14 text-2xl font-semibold text-zinc-500 transition hover:bg-zinc-200 dark:hover:bg-zinc-700">−</button>
                        <input id="stock_qty" name="stock_qty" type="number" required min="0" max="100000" step="1"
                               x-model="f.stock_qty"
                               class="w-full border-0 bg-transparent text-center text-2xl font-bold tabular-nums text-zinc-900
                                      focus:outline-none focus:ring-0 dark:text-white">
                        <button type="button" x-on:click="step(1)" aria-label="Increase stock"
                                class="w-14 text-2xl font-semibold text-zinc-500 transition hover:bg-zinc-200 dark:hover:bg-zinc-700">+</button>
                    </div>
                    <p class="{{ $hint }}">Set to 0 to stop selling this car.</p>
                    @error('stock_qty') <p class="{{ $error }}">{{ $message }}</p> @enderror
                </div>
            </div>
        </section>

        {{-- 5 · Description --}}
        <section class="{{ $card }}">
            <div class="mb-5 flex items-center gap-3">
                <span class="{{ $step }}">5</span>
                <div>
                    <h2 class="{{ $h2 }}">Description</h2>
                    <p class="text-xs text-zinc-500">Highlights, equipment, anything buyers should know</p>
                </div>
            </div>

            <label for="description" class="sr-only">Description</label>
            <textarea id="description" name="description" rows="6" maxlength="5000"
                      x-model="f.description" placeholder="Highlights, condition, equipment…"
                      class="{{ $input }} h-auto py-3 leading-relaxed"></textarea>
            <p class="mt-1.5 text-right text-xs text-zinc-400"><span x-text="f.description.length"></span> / 5000</p>
            @error('description') <p class="{{ $error }}">{{ $message }}</p> @enderror
        </section>
    </div>

    {{-- ================= RIGHT: live showroom preview ================= --}}
    <aside class="space-y-3 lg:sticky lg:top-4">
        <p class="px-1 text-xs font-semibold uppercase tracking-wider text-zinc-400">Live preview</p>

        <div class="overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-lg dark:border-zinc-800 dark:bg-zinc-900">

            {{-- Photo (click to upload) --}}
            <label for="image"
                   class="group relative block aspect-[4/3] cursor-pointer overflow-hidden bg-white">
                <img x-show="preview" x-cloak x-bind:src="preview" alt="New image preview" class="h-full w-full object-contain p-2">

                @if ($imageSrc)
                    <img x-show="showExisting" src="{{ $imageSrc }}" alt="{{ $car->model_name }}" class="h-full w-full object-contain p-2">
                @endif

                <div x-show="showPlaceholder" class="flex h-full flex-col items-center justify-center gap-2 text-zinc-400">
                    <svg viewBox="0 0 120 50" class="h-16 w-36 text-zinc-300" aria-hidden="true">{!! $carSvg('sedan') !!}</svg>
                    <span class="text-sm font-medium">Click to add a photo</span>
                </div>

                <span x-show="f.car_condition" x-cloak x-text="f.car_condition"
                      class="absolute left-3 top-3 rounded-full bg-black/60 px-2.5 py-1 text-[11px] font-semibold text-white backdrop-blur"></span>

                <span class="absolute bottom-3 right-3 rounded-lg bg-black/60 px-3 py-1.5 text-xs font-semibold text-white backdrop-blur transition group-hover:bg-red-600">
                    {{ $imageSrc ? 'Change photo' : 'Upload photo' }}
                </span>
            </label>

            <input id="image" name="image" type="file" accept=".jpg,.jpeg,.png,.webp" class="sr-only" x-on:change="pickFile($event)">

            {{-- Card body --}}
            <div class="space-y-3 p-5">
                <div class="flex flex-wrap items-center gap-1.5">
                    <span class="rounded-full bg-zinc-900 px-2.5 py-1 text-[11px] font-semibold text-white dark:bg-zinc-100 dark:text-zinc-900"
                          x-text="brandName || 'Brand'"></span>
                    <span x-show="catName" x-cloak class="rounded-full bg-sky-100 px-2.5 py-1 text-[11px] font-semibold text-sky-700 dark:bg-sky-950/60 dark:text-sky-300"
                          x-text="catName"></span>
                </div>

                <div>
                    <p class="text-xl font-bold leading-tight"
                       x-bind:class="f.model_name ? 'text-zinc-900 dark:text-white' : 'text-zinc-300 dark:text-zinc-600'"
                       x-text="f.model_name || 'Model name'"></p>

                    <p class="mt-1 flex items-center gap-2 text-sm text-zinc-500">
                        <span x-text="f.model_year || 'Year'"></span>
                        <span class="text-zinc-300 dark:text-zinc-600">·</span>
                        <span class="inline-flex items-center gap-1.5">
                            <span class="h-3 w-3 rounded-full ring-1 ring-zinc-300 dark:ring-zinc-600" x-bind:style="'background:' + (hex || 'transparent')"></span>
                            <span x-text="f.color || 'Color'"></span>
                        </span>
                    </p>
                </div>

                <div class="flex flex-wrap gap-1.5">
                    <span x-show="f.fuel_type" x-cloak x-text="f.fuel_type" class="rounded-lg bg-zinc-100 px-2 py-1 text-xs font-medium text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300"></span>
                    <span x-show="f.transmission" x-cloak x-text="f.transmission" class="rounded-lg bg-zinc-100 px-2 py-1 text-xs font-medium text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300"></span>
                    <span x-show="engineLabel" x-cloak x-text="engineLabel" class="rounded-lg bg-zinc-100 px-2 py-1 text-xs font-medium text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300"></span>
                    <span x-show="f.mileage_km !== ''" x-cloak x-text="Number(f.mileage_km).toLocaleString() + ' km'" class="rounded-lg bg-zinc-100 px-2 py-1 text-xs font-medium text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300"></span>
                </div>

                <div class="flex items-end justify-between gap-3 border-t border-zinc-100 pt-4 dark:border-zinc-800">
                    <p class="text-2xl font-black tabular-nums text-zinc-900 dark:text-white" x-text="priceLabel"></p>
                    <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-[11px] font-semibold" x-bind:class="stock.cls">
                        <span class="h-1.5 w-1.5 rounded-full" x-bind:class="stock.dot"></span>
                        <span x-text="stock.text"></span>
                    </span>
                </div>
            </div>
        </div>

        <div class="px-1">
            <p class="text-xs text-zinc-500">
                <span x-show="fileName" x-cloak>New photo: <span class="font-medium" x-text="fileName"></span> · </span>
                JPG, PNG or WEBP, up to 2 MB.
            </p>
            @error('image') <p class="{{ $error }}">{{ $message }}</p> @enderror

            @if ($imageSrc)
                <label class="mt-3 flex items-center gap-2 text-sm text-zinc-700 dark:text-zinc-300">
                    <input type="checkbox" name="remove_image" value="1" x-model="f.remove_image"
                           class="rounded border-zinc-300 text-red-600 focus:ring-red-500">
                    Remove current photo
                </label>
            @endif
        </div>
    </aside>
</div>

{{-- ================= Sticky action bar ================= --}}
<div class="sticky bottom-0 z-10 -mx-2 mt-8 flex items-center justify-between gap-3 border-t border-zinc-200
            bg-zinc-50/90 px-4 py-3 backdrop-blur sm:-mx-4
            dark:border-zinc-800 dark:bg-zinc-950/90">
    <p class="hidden text-xs text-zinc-500 sm:block">
        Fields marked <span class="text-red-500">*</span> are required.
        {{ $isEdit ? 'Changes are not saved until you press Save.' : 'The car is added when you press Add car.' }}
    </p>

    <div class="ml-auto flex items-center gap-3">
        <a href="{{ route('admin.cars.index') }}"
           class="inline-flex h-11 items-center rounded-xl border border-zinc-300 px-5 text-sm font-semibold text-zinc-700 transition
                  hover:border-zinc-500 dark:border-zinc-700 dark:text-zinc-300">
            Cancel
        </a>

        <button type="submit"
                class="h-11 rounded-xl bg-red-600 px-7 text-sm font-semibold text-white shadow-md shadow-red-600/30 transition hover:bg-red-500
                       focus:outline-none focus:ring-2 focus:ring-red-500/40">
            {{ $isEdit ? 'Save changes' : 'Add car' }}
        </button>
    </div>
</div>