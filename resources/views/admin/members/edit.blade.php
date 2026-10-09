@php
    use Illuminate\Support\Str;

    $isSelf    = (int) $member->member_id === (int) auth()->id();
    $fullName  = trim(($member->first_name ?? '').' '.($member->last_name ?? '')) ?: ($member->name ?? 'Member');
    $initial   = Str::upper(Str::substr($fullName, 0, 1));
    $roleNow   = old('role', $member->role);
    $tierNowId = (string) old('tier_id', $member->tier_id);

    // Same tier colors as the Members list (full class names so Tailwind keeps them)
    $tierTheme = [
        'basic'    => ['avatar' => 'bg-zinc-600',   'dot' => 'bg-zinc-400',   'sel' => 'peer-checked:border-zinc-500 peer-checked:bg-zinc-500/10 peer-checked:ring-2 peer-checked:ring-zinc-500/30'],
        'silver'   => ['avatar' => 'bg-slate-500',  'dot' => 'bg-slate-400',  'sel' => 'peer-checked:border-slate-400 peer-checked:bg-slate-500/10 peer-checked:ring-2 peer-checked:ring-slate-400/30'],
        'gold'     => ['avatar' => 'bg-amber-500',  'dot' => 'bg-amber-500',  'sel' => 'peer-checked:border-amber-500 peer-checked:bg-amber-500/10 peer-checked:ring-2 peer-checked:ring-amber-500/30'],
        'platinum' => ['avatar' => 'bg-violet-600', 'dot' => 'bg-violet-500', 'sel' => 'peer-checked:border-violet-500 peer-checked:bg-violet-500/10 peer-checked:ring-2 peer-checked:ring-violet-500/30'],
    ];
    $themeOf   = fn ($name) => $tierTheme[Str::lower((string) $name)] ?? $tierTheme['basic'];
    $curTier   = $member->tier?->tier_name ?? $member->tier?->name ?? 'Basic';

    $input = fn (string $name) => 'h-11 w-full rounded-xl border bg-white px-3.5 text-sm text-zinc-900 placeholder:text-zinc-400 '
           .'focus:border-red-500 focus:outline-none focus:ring-2 focus:ring-red-500/20 dark:bg-zinc-800 dark:text-white '
           .($errors->has($name) ? 'border-red-500' : 'border-zinc-300 dark:border-zinc-700');
    $label     = 'mb-1.5 block text-sm font-medium text-zinc-600 dark:text-zinc-300';

    $tierData  = $tiers->map(fn ($t) => [
        'id'       => $t->tier_id,
        'name'     => $t->tier_name ?? $t->name,
        'min'      => (int) $t->min_points,
    ])->values();
@endphp

<x-layouts::app :title="'Edit Member'">
    <div class="min-h-full bg-zinc-50 dark:bg-zinc-950">
        <div class="mx-auto max-w-5xl space-y-5 p-2 sm:p-4">

            @include('commerce.messages')

            @error('member')
                <div class="rounded-xl border border-red-300 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-900 dark:bg-red-950/40 dark:text-red-300">
                    {{ $message }}
                </div>
            @enderror

            {{-- Header: no card --}}
            <header class="px-1">
                <nav class="flex items-center gap-2 text-xs text-zinc-500" aria-label="Breadcrumb">
                    <a href="{{ route('admin.dashboard') }}" class="transition hover:text-red-600">Admin</a>
                    <span>/</span>
                    <a href="{{ route('admin.members.index') }}" class="transition hover:text-red-600">Members</a>
                    <span>/</span>
                    <span class="text-zinc-900 dark:text-white">Edit</span>
                </nav>

                <div class="mt-4 flex items-center gap-4">
                    <div class="grid h-16 w-16 shrink-0 place-items-center rounded-2xl text-2xl font-bold text-white {{ $themeOf($curTier)['avatar'] }}">
                        {{ $initial }}
                    </div>
                    <div class="min-w-0 flex-1">
                        <h1 class="truncate text-2xl font-bold text-zinc-900 dark:text-white sm:text-3xl">{{ $fullName }}</h1>
                        <p class="truncate text-sm text-zinc-500">{{ $member->email }}</p>
                    </div>
                    <a href="{{ route('admin.members.edit', $member) }}"
                       class="hidden shrink-0 text-xs font-medium text-zinc-500 transition hover:text-red-600 sm:block">
                        Reload latest data
                    </a>
                </div>
            </header>

            <form method="POST" action="{{ route('admin.members.update', $member) }}" class="space-y-5">
                @csrf
                @method('PATCH')

                <input type="hidden" name="member_version" value="{{ old('member_version', $version) }}">

                <div class="grid gap-5 lg:grid-cols-5">

                    {{-- LEFT: profile --}}
                    <section class="space-y-5 rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900 lg:col-span-3">
                        <h2 class="text-base font-semibold text-zinc-900 dark:text-white">Personal info</h2>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label for="first_name" class="{{ $label }}">First name</label>
                                <input id="first_name" name="first_name" type="text" required maxlength="255"
                                       value="{{ old('first_name', $member->first_name) }}" class="{{ $input('first_name') }}">
                                @error('first_name') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="last_name" class="{{ $label }}">Last name</label>
                                <input id="last_name" name="last_name" type="text" required maxlength="255"
                                       value="{{ old('last_name', $member->last_name) }}" class="{{ $input('last_name') }}">
                                @error('last_name') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div class="border-t border-zinc-100 pt-5 dark:border-zinc-800">
                            <div class="grid gap-4 sm:grid-cols-2">
                                <div>
                                    <label for="email" class="{{ $label }}">Email</label>
                                    <input id="email" name="email" type="email" required maxlength="255"
                                           value="{{ old('email', $member->email) }}" class="{{ $input('email') }}">
                                    @error('email') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label for="phone" class="{{ $label }}">Phone</label>
                                    <input id="phone" name="phone" type="tel" maxlength="30"
                                           value="{{ old('phone', $member->phone) }}" class="{{ $input('phone') }}">
                                    @error('phone') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                                </div>
                            </div>

                            <div class="mt-4">
                                <label for="address" class="{{ $label }}">Address</label>
                                <textarea id="address" name="address" rows="3" maxlength="5000"
                                          class="{{ $input('address') }} h-auto py-2.5">{{ old('address', $member->address) }}</textarea>
                                @error('address') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </section>

                    {{-- RIGHT: access + membership --}}
                    <section class="space-y-5 rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900 lg:col-span-2">

                        {{-- Role --}}
                        <div>
                            <h2 class="text-base font-semibold text-zinc-900 dark:text-white">Access</h2>

                            @if ($isSelf)
                                <input type="hidden" name="role" value="admin">
                                <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-300">
                                    <span class="rounded-full bg-red-100 px-2.5 py-1 text-xs font-semibold text-red-700 dark:bg-red-950/60 dark:text-red-300">Admin</span>
                                    <span class="ml-1">This is your account</span>
                                </p>
                                <p class="mt-1.5 text-xs text-zinc-500">You can change other accounts' roles from the Members list.</p>
                            @else
                                <div class="mt-3 grid grid-cols-2 gap-1 rounded-xl bg-zinc-100 p-1 dark:bg-zinc-800">
                                    <label class="cursor-pointer">
                                        <input type="radio" name="role" value="member" class="peer sr-only" @checked($roleNow === 'member')>
                                        <span class="block rounded-lg py-2 text-center text-sm font-semibold text-zinc-600 transition
                                                     peer-checked:bg-sky-600 peer-checked:text-white peer-checked:shadow-sm
                                                     peer-focus-visible:ring-2 peer-focus-visible:ring-sky-500/50 dark:text-zinc-400">Member</span>
                                    </label>
                                    <label class="cursor-pointer">
                                        <input type="radio" name="role" value="admin" class="peer sr-only" @checked($roleNow === 'admin')>
                                        <span class="block rounded-lg py-2 text-center text-sm font-semibold text-zinc-600 transition
                                                     peer-checked:bg-red-600 peer-checked:text-white peer-checked:shadow-sm
                                                     peer-focus-visible:ring-2 peer-focus-visible:ring-red-500/50 dark:text-zinc-400">Admin</span>
                                    </label>
                                </div>
                                <p class="mt-2 text-xs text-zinc-500">Admins can manage the store and members.</p>
                                @error('role') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                            @endif
                        </div>

                        {{-- Tier + points --}}
                        <div class="border-t border-zinc-100 pt-5 dark:border-zinc-800">
                            <h2 class="text-base font-semibold text-zinc-900 dark:text-white">Membership tier &amp; points</h2>

                            <div class="mt-3 grid grid-cols-2 gap-2">
                                @foreach ($tiers as $t)
                                    @php
                                        $tname = $t->tier_name ?? $t->name;
                                        $th    = $themeOf($tname);
                                    @endphp
                                    <label class="cursor-pointer">
                                        <input type="radio" name="tier_id" value="{{ $t->tier_id }}" class="peer sr-only"
                                               @checked($tierNowId === (string) $t->tier_id)>
                                        <div class="h-full rounded-xl border border-zinc-200 p-3 transition hover:border-zinc-400
                                                    peer-focus-visible:ring-2 peer-focus-visible:ring-red-500/50
                                                    dark:border-zinc-700 dark:hover:border-zinc-500 {{ $th['sel'] }}">
                                            <div class="flex items-center gap-1.5 text-sm font-semibold text-zinc-900 dark:text-white">
                                                <span class="h-2 w-2 rounded-full {{ $th['dot'] }}"></span>{{ $tname }}
                                            </div>
                                            <p class="mt-1 text-xs text-zinc-500">≥ {{ number_format($t->min_points) }} pts</p>
                                            <p class="text-xs text-zinc-500">{{ rtrim(rtrim(number_format((float) $t->discount_percent, 2), '0'), '.') }}% discount</p>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                            @error('tier_id') <p class="mt-2 text-xs text-red-500">{{ $message }}</p> @enderror

                            <div class="mt-4">
                                <label for="points" class="{{ $label }}">Points</label>
                                <input id="points" name="points" type="number" inputmode="numeric" min="0" max="4294967295" step="1" required
                                       value="{{ old('points', $member->points) }}"
                                       class="{{ $input('points') }} text-lg font-semibold">
                                @error('points') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                                <p id="points-hint" class="mt-2 text-xs text-zinc-500"></p>
                            </div>

                            <p class="mt-3 text-xs text-zinc-500">
                                Picking a tier sets points to that tier's minimum. Typing points updates the tier automatically.
                                Point changes are recorded in the Activity Log and don't affect points saved on past orders.
                            </p>
                        </div>
                    </section>
                </div>

                {{-- Action bar --}}
                <div class="sticky bottom-0 -mx-2 flex items-center justify-end gap-3 border-t border-zinc-200 bg-zinc-50/90 px-2 py-3 backdrop-blur
                            dark:border-zinc-800 dark:bg-zinc-950/90 sm:-mx-4 sm:px-4">
                    <a href="{{ route('admin.members.index') }}"
                       class="inline-flex h-11 items-center rounded-xl border border-zinc-300 px-5 text-sm font-semibold text-zinc-700
                              transition hover:border-zinc-500 dark:border-zinc-700 dark:text-zinc-300">
                        Cancel
                    </a>
                    <button type="submit"
                            class="h-11 rounded-xl bg-red-600 px-7 text-sm font-semibold text-white transition hover:bg-red-500
                                   focus:outline-none focus:ring-2 focus:ring-red-500/40">
                        Save changes
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        (() => {
            const tiers  = @json($tierData).sort((a, b) => a.min - b.min);
            const points = document.getElementById('points');
            const hint   = document.getElementById('points-hint');
            const radios = document.querySelectorAll('input[name="tier_id"]');
            const fmt    = n => Number(n).toLocaleString();

            // Same rule as the server: highest min_points <= points
            const tierFor = p => tiers.reduce((best, t) => (p >= t.min && (!best || t.min > best.min)) ? t : best, null);

            function update(selectRadio) {
                const p = parseInt(points.value || '0', 10) || 0;
                const t = tierFor(p);

                if (selectRadio && t) {
                    radios.forEach(r => r.checked = String(r.value) === String(t.id));
                }

                if (!t) {
                    hint.textContent = 'Points are below every tier minimum';
                    return;
                }
                const next = tiers.find(x => x.min > t.min);
                hint.textContent = next
                    ? `${fmt(p)} pts · ${t.name} tier · ${fmt(next.min - p)} more pts to ${next.name}`
                    : `${fmt(p)} pts · ${t.name} tier (highest)`;
            }

            points.addEventListener('input', () => update(true));

            radios.forEach(r => r.addEventListener('change', () => {
                const t = tiers.find(x => String(x.id) === r.value);
                if (t) { points.value = t.min; update(false); }
            }));

            update(false);
        })();
    </script>
</x-layouts::app>