{{--
    Defines window.categoryForm(), used by the New / Edit pages as  x-data="categoryForm()".
    Include it just BEFORE the element that uses x-data.
    Params: initialName, original (edit only), selfId (edit only)
--}}
@php
    $__list = \App\Models\Category::withCount('cars')->orderBy('category_id')->get()
        ->map(fn ($c) => [
            'id'    => (int) $c->category_id,
            'name'  => $c->category_name,
            'key'   => mb_strtolower(trim($c->category_name)),
            'count' => (int) $c->cars_count,
            'url'   => \Illuminate\Support\Facades\Route::has('admin.categories.edit')
                        ? route('admin.categories.edit', $c) : '#',
        ])->values();

    $__picks = ['Sedan', 'SUV', 'Hatchback', 'Pickup', 'Coupe', 'Van', 'Electric', 'Convertible', 'Wagon', 'MPV'];
@endphp

<script>
    window.categoryForm = function () {
        return {
            name: @js(old('category_name', $initialName ?? '')),
            original: @js($original ?? null),
            selfId: @js($selfId ?? null),
            list: @js($__list),
            picks: @js($__picks),
            max: 255,

            get key() { return this.name.trim().toLowerCase(); },
            get match() {
                if (this.key === '') return null;
                return this.list.find(c => c.key === this.key && c.id !== this.selfId) || null;
            },
            get unchanged() {
                return this.original !== null && this.name.trim() === String(this.original).trim();
            },
            get similar() {
                if (this.key.length < 2) return [];
                return this.list.filter(c => c.id !== this.selfId && c.key !== this.key
                    && (c.key.includes(this.key) || this.key.includes(c.key)));
            },
            get state() {
                if (this.key === '') return 'empty';
                if (this.name.length > this.max) return 'toolong';
                if (this.match) return 'duplicate';
                if (this.unchanged) return 'unchanged';
                return 'ok';
            },
            get canSave() { return this.state === 'ok'; },
            get hint() {
                return {
                    empty: 'Enter a name to continue',
                    toolong: 'Name is too long',
                    duplicate: 'That name is already taken',
                    unchanged: 'No changes to save yet',
                    ok: 'Ready to save',
                }[this.state];
            },
            has(p) { return this.list.some(c => c.key === p.toLowerCase() && c.id !== this.selfId); },
            get picksAdded() { return this.picks.filter(p => this.has(p)).length; },
            pick(p) { if (!this.has(p)) { this.name = p; } },
        };
    };
</script>