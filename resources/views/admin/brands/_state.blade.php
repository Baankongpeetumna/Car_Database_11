{{--
    Defines window.brandForm(), used by the Add / Edit brand pages as  x-data="brandForm()".
    Include it just BEFORE the element that uses x-data.
    Params: initialName, initialCountry, original, originalCountry, selfId, nextId, models
--}}
@php
    $__brands = \App\Models\Brand::withCount('cars')->orderBy('brand_id')->get()
        ->map(fn ($b) => [
            'id'      => (int) $b->brand_id,
            'name'    => $b->brand_name,
            'key'     => mb_strtolower(trim($b->brand_name)),
            'country' => (string) $b->country,
            'count'   => (int) $b->cars_count,
            'url'     => \Illuminate\Support\Facades\Route::has('admin.brands.edit')
                            ? route('admin.brands.edit', $b) : '#',
        ])->values();

    $__makes = [
        ['n' => 'Toyota', 'c' => 'Japan'],   ['n' => 'Honda', 'c' => 'Japan'],
        ['n' => 'Mazda', 'c' => 'Japan'],    ['n' => 'Nissan', 'c' => 'Japan'],
        ['n' => 'BMW', 'c' => 'Germany'],    ['n' => 'Mercedes-Benz', 'c' => 'Germany'],
        ['n' => 'Audi', 'c' => 'Germany'],   ['n' => 'Volkswagen', 'c' => 'Germany'],
        ['n' => 'Ford', 'c' => 'USA'],       ['n' => 'Tesla', 'c' => 'USA'],
        ['n' => 'Hyundai', 'c' => 'South Korea'], ['n' => 'Kia', 'c' => 'South Korea'],
    ];

    $__countryPool = ['Japan', 'Germany', 'USA', 'South Korea', 'Italy', 'United Kingdom', 'France', 'China', 'Sweden'];
@endphp

<script>
    window.brandForm = function () {
        const codes = {
            'japan': 'JP', 'germany': 'DE', 'usa': 'US', 'united states': 'US', 'south korea': 'KR', 'korea': 'KR',
            'italy': 'IT', 'united kingdom': 'GB', 'uk': 'GB', 'france': 'FR', 'china': 'CN', 'sweden': 'SE',
            'india': 'IN', 'thailand': 'TH', 'spain': 'ES', 'czechia': 'CZ', 'malaysia': 'MY',
        };

        return {
            name: @js(old('brand_name', $initialName ?? '')),
            country: @js(old('country', $initialCountry ?? '')),
            original: @js($original ?? null),
            originalCountry: @js($originalCountry ?? null),
            selfId: @js($selfId ?? null),
            nextId: @js($nextId ?? null),
            models: @js((int) ($models ?? 0)),
            brands: @js($__brands),
            makes: @js($__makes),
            pool: @js($__countryPool),

            get key() { return this.name.trim().toLowerCase(); },
            get ckey() { return this.country.trim().toLowerCase(); },

            get match() {
                if (this.key === '') return null;
                return this.brands.find(b => b.key === this.key && b.id !== this.selfId) || null;
            },
            get changed() {
                if (this.original === null) return true;
                return this.name.trim() !== String(this.original).trim()
                    || this.country.trim() !== String(this.originalCountry ?? '').trim();
            },
            get state() {
                if (this.key === '') return 'empty';
                if (this.name.length > 255) return 'toolong';
                if (this.match) return 'duplicate';
                if (this.ckey === '') return 'nocountry';
                if (this.country.length > 100) return 'countrylong';
                if (!this.changed) return 'unchanged';
                return 'ok';
            },
            get canSave() { return this.state === 'ok'; },
            get hint() {
                return {
                    empty: 'Enter a brand name to continue',
                    toolong: 'Brand name is too long',
                    duplicate: 'That brand already exists',
                    nocountry: 'Add the brand\'s country',
                    countrylong: 'Country is too long',
                    unchanged: 'No changes to save yet',
                    ok: 'Ready to save',
                }[this.state];
            },

            // brands already in the typed country (not counting this one)
            get sameCountry() {
                if (this.ckey === '') return [];
                return this.brands.filter(b => b.country.trim().toLowerCase() === this.ckey && b.id !== this.selfId);
            },
            get isNewCountry() { return this.ckey !== '' && this.sameCountry.length === 0; },

            // chips: countries that exist (with counts) first, then common ones not yet used
            get countryChips() {
                const seen = {};
                this.brands.forEach(b => {
                    const k = b.country.trim().toLowerCase();
                    if (!k) return;
                    seen[k] = seen[k] || { name: b.country.trim(), count: 0 };
                    seen[k].count++;
                });
                const existing = Object.values(seen).sort((a, b) => b.count - a.count || a.name.localeCompare(b.name));
                const extra = this.pool.filter(p => !seen[p.toLowerCase()]).map(p => ({ name: p, count: 0 }));
                return existing.concat(extra);
            },

            code(c) {
                const k = String(c || '').trim().toLowerCase();
                if (k === '') return '--';
                return codes[k] || k.replace(/[^a-z]/g, '').slice(0, 2).toUpperCase() || '--';
            },

            has(m) { return this.brands.some(b => b.key === m.n.toLowerCase() && b.id !== this.selfId); },
            get makesAdded() { return this.makes.filter(m => this.has(m)).length; },
            pickMake(m) { if (!this.has(m)) { this.name = m.n; this.country = m.c; } },

            get ticks() { return Array.from({ length: Math.min(this.models, 8) }, (_, i) => i); },
            pad(n) { return String(n).padStart(2, '0'); },
        };
    };
</script>