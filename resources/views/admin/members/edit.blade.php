@php
    use App\Support\TierVisual;
    use Illuminate\Support\Str;

    $isSelf = (int) $member->member_id === (int) auth()->id();

    $fullName = trim(
        ($member->first_name ?? '').' '.($member->last_name ?? '')
    ) ?: 'Member';

    $initial = Str::upper(Str::substr($fullName, 0, 1));

    $roleNow = old('role', $member->role);
    $tierNowId = (string) old('tier_id', $member->tier_id);

    $selectedTier = $tiers->first(
        fn ($tier) => (string) $tier->tier_id === $tierNowId
    ) ?? $member->tier;

    $input = fn (string $name) =>
        'w-full rounded-xl border bg-white px-3.5 py-3 text-sm text-zinc-900 '
        .'placeholder:text-zinc-400 focus:border-red-500 focus:outline-none '
        .'focus:ring-2 focus:ring-red-500/20 dark:bg-zinc-800 dark:text-white '
        .($errors->has($name)
            ? 'border-red-500'
            : 'border-zinc-300 dark:border-zinc-700');

    $label = 'mb-1.5 block text-sm font-medium text-zinc-600 dark:text-zinc-300';

    $tierData = $tiers->map(fn ($tier) => [
        'id' => $tier->tier_id,
        'name' => $tier->tier_name,
        'min' => (int) $tier->min_points,
        'color' => $tier->color_hex,
    ])->values();
@endphp

<x-layouts::app :title="'Edit Member'">
    @include('partials.tier-colors')

    <div class="min-h-full bg-zinc-50 dark:bg-zinc-950">
        <div class="mx-auto max-w-5xl space-y-5 p-2 sm:p-4">

            @include('commerce.messages')

            <header class="px-1">
                <nav class="flex items-center gap-2 text-xs text-zinc-500"
                     aria-label="Breadcrumb">
                    <a href="{{ route('admin.dashboard') }}"
                       class="transition hover:text-red-600">
                        Admin
                    </a>
                    <span>/</span>
                    <a href="{{ route('admin.members.index') }}"
                       class="transition hover:text-red-600">
                        Members
                    </a>
                    <span>/</span>
                    <span class="text-zinc-900 dark:text-white">Edit</span>
                </nav>

                <div class="mt-4 flex items-center gap-4">
                    <div id="member-tier-avatar"
                         style="{{ TierVisual::style($selectedTier) }}"
                         class="tier-avatar grid h-16 w-16 shrink-0 place-items-center rounded-2xl text-2xl font-bold">
                        {{ $initial }}
                    </div>

                    <div class="min-w-0 flex-1">
                        <h1 class="truncate text-2xl font-bold text-zinc-900 sm:text-3xl dark:text-white">
                            {{ $fullName }}
                        </h1>
                        <p class="truncate text-sm text-zinc-500">
                            {{ $member->email }}
                        </p>

                        <span id="member-tier-badge"
                              style="{{ TierVisual::style($selectedTier) }}"
                              class="tier-badge mt-2 inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold">
                            <span class="tier-dot h-2 w-2 rounded-full"></span>
                            <span id="member-tier-name">
                                {{ $selectedTier?->tier_name ?? 'No tier' }}
                            </span>
                        </span>
                    </div>

                    <a href="{{ route('admin.members.edit', $member) }}"
                       class="hidden shrink-0 text-xs font-medium text-zinc-500 transition hover:text-red-600 sm:block">
                        Reload latest data
                    </a>
                </div>
            </header>

            <form id="member-edit-form"
                  method="POST"
                  action="{{ route('admin.members.update', $member) }}"
                  class="space-y-5">
                @csrf
                @method('PATCH')

                <input type="hidden"
                       name="member_version"
                       value="{{ old('member_version', $version) }}">

                <div class="grid gap-5 lg:grid-cols-5">
                    <section class="space-y-5 rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900 lg:col-span-3">
                        <h2 class="text-base font-semibold text-zinc-900 dark:text-white">
                            Personal info
                        </h2>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label for="first_name" class="{{ $label }}">
                                    First name
                                </label>
                                <input id="first_name"
                                       name="first_name"
                                       type="text"
                                       required
                                       maxlength="255"
                                       value="{{ old('first_name', $member->first_name) }}"
                                       class="{{ $input('first_name') }}">
                                @error('first_name')
                                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="last_name" class="{{ $label }}">
                                    Last name
                                </label>
                                <input id="last_name"
                                       name="last_name"
                                       type="text"
                                       required
                                       maxlength="255"
                                       value="{{ old('last_name', $member->last_name) }}"
                                       class="{{ $input('last_name') }}">
                                @error('last_name')
                                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="border-t border-zinc-100 pt-5 dark:border-zinc-800">
                            <div class="grid gap-4 sm:grid-cols-2">
                                <div>
                                    <label for="email" class="{{ $label }}">Email</label>
                                    <input id="email"
                                           name="email"
                                           type="email"
                                           required
                                           maxlength="255"
                                           value="{{ old('email', $member->email) }}"
                                           class="{{ $input('email') }}">
                                    @error('email')
                                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="phone" class="{{ $label }}">Phone</label>
                                    <input id="phone"
                                           name="phone"
                                           type="tel"
                                           maxlength="30"
                                           value="{{ old('phone', $member->phone) }}"
                                           class="{{ $input('phone') }}">
                                    @error('phone')
                                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <div class="mt-4">
                                <label for="address" class="{{ $label }}">Address</label>
                                <textarea id="address"
                                          name="address"
                                          rows="3"
                                          maxlength="5000"
                                          class="{{ $input('address') }}">{{ old('address', $member->address) }}</textarea>
                                @error('address')
                                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </section>

                    <section class="space-y-5 rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900 lg:col-span-2">
                        <div>
                            <h2 class="text-base font-semibold text-zinc-900 dark:text-white">
                                Access
                            </h2>

                            @if ($isSelf)
                                <input type="hidden" name="role" value="admin">

                                <p class="mt-3 text-sm text-zinc-600 dark:text-zinc-300">
                                    <span class="rounded-full bg-red-100 px-2.5 py-1 text-xs font-semibold text-red-700 dark:bg-red-950/60 dark:text-red-300">
                                        Admin
                                    </span>
                                    <span class="ml-1">This is your account</span>
                                </p>

                                <p class="mt-2 text-xs text-zinc-500">
                                    You can change other accounts' roles from the Members list.
                                </p>
                            @else
                                <div class="mt-3 grid grid-cols-2 gap-1 rounded-xl bg-zinc-100 p-1 dark:bg-zinc-800">
                                    <label class="cursor-pointer">
                                        <input type="radio"
                                               name="role"
                                               value="member"
                                               class="peer sr-only"
                                               @checked($roleNow === 'member')>
                                        <span class="block rounded-lg py-2 text-center text-sm font-semibold text-zinc-600 transition peer-checked:bg-sky-600 peer-checked:text-white peer-checked:shadow-sm peer-focus-visible:ring-2 peer-focus-visible:ring-sky-500/50 dark:text-zinc-400">
                                            Member
                                        </span>
                                    </label>

                                    <label class="cursor-pointer">
                                        <input type="radio"
                                               name="role"
                                               value="admin"
                                               class="peer sr-only"
                                               @checked($roleNow === 'admin')>
                                        <span class="block rounded-lg py-2 text-center text-sm font-semibold text-zinc-600 transition peer-checked:bg-red-600 peer-checked:text-white peer-checked:shadow-sm peer-focus-visible:ring-2 peer-focus-visible:ring-red-500/50 dark:text-zinc-400">
                                            Admin
                                        </span>
                                    </label>
                                </div>

                                <p class="mt-2 text-xs text-zinc-500">
                                    Admins can manage the store and members.
                                </p>
                            @endif

                            @error('role')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="border-t border-zinc-100 pt-5 dark:border-zinc-800">
                            <h2 class="text-base font-semibold text-zinc-900 dark:text-white">
                                Membership tier &amp; points
                            </h2>

                            <div class="mt-3 grid grid-cols-2 gap-2">
                                @foreach ($tiers as $tier)
                                    <label style="{{ TierVisual::style($tier) }}"
                                           class="cursor-pointer">
                                        <input type="radio"
                                               name="tier_id"
                                               value="{{ $tier->tier_id }}"
                                               class="peer sr-only"
                                               required
                                               @checked($tierNowId === (string) $tier->tier_id)>

                                        <div class="tier-choice h-full rounded-xl border border-zinc-200 p-3 transition hover:border-zinc-400 peer-focus-visible:ring-2 peer-focus-visible:ring-red-500/50 dark:border-zinc-700 dark:hover:border-zinc-500">
                                            <div class="flex items-center gap-1.5 text-sm font-semibold text-zinc-900 dark:text-white">
                                                <span class="tier-dot h-2 w-2 shrink-0 rounded-full border border-black/10 dark:border-white/20"></span>
                                                {{ $tier->tier_name }}
                                            </div>

                                            <p class="mt-1 text-xs text-zinc-500">
                                                ≥ {{ number_format($tier->min_points) }} pts
                                            </p>

                                            <p class="text-xs text-zinc-500">
                                                {{ rtrim(rtrim(number_format((float) $tier->discount_percent, 2), '0'), '.') }}% discount
                                            </p>
                                        </div>
                                    </label>
                                @endforeach
                            </div>

                            @error('tier_id')
                                <p class="mt-2 text-xs text-red-500">{{ $message }}</p>
                            @enderror

                            <div class="mt-4">
                                <label for="points" class="{{ $label }}">Points</label>
                                <input id="points"
                                       name="points"
                                       type="number"
                                       inputmode="numeric"
                                       min="0"
                                       max="4294967295"
                                       step="1"
                                       required
                                       value="{{ old('points', $member->points) }}"
                                       class="{{ $input('points') }} text-lg font-semibold">

                                @error('points')
                                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                @enderror

                                <p id="points-hint" class="mt-2 text-xs text-zinc-500"></p>
                            </div>

                            <p class="mt-3 text-xs text-zinc-500">
                                Picking a tier sets points to that tier's minimum.
                                Typing points updates the tier automatically.
                                Point changes are recorded in the Activity Log and
                                don't affect points saved on past orders.
                            </p>
                        </div>
                    </section>
                </div>

                <div class="sticky bottom-0 -mx-2 flex items-center justify-end gap-3 border-t border-zinc-200 bg-zinc-50/90 px-2 py-3 backdrop-blur dark:border-zinc-800 dark:bg-zinc-950/90 sm:-mx-4 sm:px-4">
                    <a href="{{ route('admin.members.index') }}"
                       class="inline-flex h-11 items-center rounded-xl border border-zinc-300 px-5 text-sm font-semibold text-zinc-700 transition hover:border-zinc-500 dark:border-zinc-700 dark:text-zinc-300">
                        Cancel
                    </a>

                    <button type="submit"
                            class="h-11 rounded-xl bg-red-600 px-7 text-sm font-semibold text-white transition hover:bg-red-500 focus:outline-none focus:ring-2 focus:ring-red-500/40">
                        Save changes
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        (() => {
            const form = document.getElementById('member-edit-form');

            if (!form || form.dataset.tierReady === 'true') return;
            form.dataset.tierReady = 'true';

            const tiers = @json($tierData);
            tiers.sort((a, b) => a.min - b.min);

            const points = form.querySelector('#points');
            const hint = form.querySelector('#points-hint');
            const radios = [...form.querySelectorAll('input[name="tier_id"]')];

            const avatar = document.getElementById('member-tier-avatar');
            const badge = document.getElementById('member-tier-badge');
            const tierName = document.getElementById('member-tier-name');

            const fmt = number => Number(number).toLocaleString();

            function contrastColor(hex) {
                const rgb = [1, 3, 5].map(index => {
                    const value = parseInt(hex.slice(index, index + 2), 16) / 255;

                    return value <= 0.04045
                        ? value / 12.92
                        : Math.pow((value + 0.055) / 1.055, 2.4);
                });

                const luminance =
                    0.2126 * rgb[0] +
                    0.7152 * rgb[1] +
                    0.0722 * rgb[2];

                return luminance > 0.179 ? '#18181B' : '#FFFFFF';
            }

            function showTier(tier) {
                if (!tier) return;

                const color = /^#[0-9a-f]{6}$/i.test(tier.color)
                    ? tier.color
                    : '#71717A';

                const ink = contrastColor(color);

                [avatar, badge].forEach(element => {
                    if (!element) return;

                    element.style.setProperty('--tier-color', color);
                    element.style.setProperty('--tier-ink', ink);
                });

                if (tierName) tierName.textContent = tier.name;
            }

            function tierFor(value) {
                return tiers.reduce((best, tier) => {
                    return value >= tier.min && (!best || tier.min > best.min)
                        ? tier
                        : best;
                }, null);
            }

            function update(selectRadio) {
                const value = points.value.trim();
                const totalPoints = Number(value);

                if (
                    value === '' ||
                    !Number.isInteger(totalPoints) ||
                    totalPoints < 0 ||
                    totalPoints > 4294967295
                ) {
                    hint.textContent = 'Enter a whole number from 0 to 4,294,967,295.';
                    return;
                }

                const tier = tierFor(totalPoints);

                if (!tier) {
                    hint.textContent = 'Points are below every tier minimum.';
                    return;
                }

                if (selectRadio) {
                    radios.forEach(radio => {
                        radio.checked = String(radio.value) === String(tier.id);
                    });

                    showTier(tier);
                }

                const next = tiers.find(item => item.min > tier.min);

                hint.textContent = next
                    ? `${fmt(totalPoints)} pts · ${tier.name} tier · ${fmt(next.min - totalPoints)} more pts to ${next.name}`
                    : `${fmt(totalPoints)} pts · ${tier.name} tier (highest)`;
            }

            points.addEventListener('input', () => update(true));

            radios.forEach(radio => {
                radio.addEventListener('change', () => {
                    if (!radio.checked) return;

                    const tier = tiers.find(
                        item => String(item.id) === String(radio.value)
                    );

                    if (!tier) return;

                    points.value = tier.min;
                    showTier(tier);
                    update(false);
                });
            });

            const checked = radios.find(radio => radio.checked);
            const selected = tiers.find(
                tier => String(tier.id) === String(checked?.value)
            );

            showTier(selected);
            update(false);
        })();
    </script>
</x-layouts::app>