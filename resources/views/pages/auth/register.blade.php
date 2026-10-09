<x-layouts::auth :title="'Create an account'">
    @php
        $starter = \App\Models\MembershipTier::orderBy('min_points')->first();
    @endphp

    <p class="font-display text-xs font-semibold uppercase italic tracking-[0.18em] text-race">Join VELOCE Club</p>
    <h1 class="mt-1 font-display text-4xl font-extrabold uppercase italic leading-none text-ink">Create an account</h1>
    <p class="mt-2 text-sm text-zinc-500">Enter your details below to create your account.</p>

    <form method="POST" action="{{ route('register.store') }}" class="mt-6 space-y-4" novalidate>
        @csrf

        <div class="grid gap-4 sm:grid-cols-2">
            @foreach ([['first_name', 'First name', 'given-name'], ['last_name', 'Last name', 'family-name']] as [$name, $label, $auto])
                <div>
                    <label for="{{ $name }}" class="field-label">{{ $label }} <span class="text-race">*</span></label>
                    <input id="{{ $name }}" type="text" name="{{ $name }}" value="{{ old($name) }}" required maxlength="255" autocomplete="{{ $auto }}" @class(['field', '!border-race' => $errors->has($name)]) @if ($loop->first) autofocus @endif>
                    @error($name) <p class="mt-1.5 text-xs text-race">{{ $message }}</p> @enderror
                </div>
            @endforeach
        </div>

        <div>
            <label for="email" class="field-label">Email address <span class="text-race">*</span></label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required maxlength="255" autocomplete="email" placeholder="email@example.com" @class(['field', '!border-race' => $errors->has('email')])>
            @error('email') <p class="mt-1.5 text-xs text-race">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="phone" class="field-label">Phone number</label>
            <input id="phone" type="tel" name="phone" value="{{ old('phone') }}" maxlength="30" autocomplete="tel" placeholder="08x-xxx-xxxx" @class(['field', '!border-race' => $errors->has('phone')])>
            @error('phone') <p class="mt-1.5 text-xs text-race">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="address" class="field-label">Address <span class="text-xs font-normal text-zinc-400">(default shipping address)</span></label>
            <textarea id="address" name="address" rows="3" maxlength="5000" autocomplete="street-address" @class(['field', '!border-race' => $errors->has('address')])>{{ old('address') }}</textarea>
            @error('address') <p class="mt-1.5 text-xs text-race">{{ $message }}</p> @enderror
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <x-store.password-input name="password" label="Password *" autocomplete="new-password" />
            <x-store.password-input name="password_confirmation" label="Confirm password *" autocomplete="new-password" />
        </div>

        <p class="flex items-start gap-2 rounded-xl bg-sun-soft px-3 py-2.5 text-xs text-ink">
            <x-store.icon name="crown" class="mt-0.5 size-4" />
            New members start at {{ $starter?->tier_name ?? 'Basic' }} and earn points on every completed order.
        </p>

        <button type="submit" class="w-full rounded-xl bg-race px-4 py-3 text-sm font-semibold text-white hover:bg-race-dark" data-test="register-user-button">
            Create account
        </button>
    </form>

    <p class="mt-6 text-center text-sm text-zinc-500">
        Already have an account?
        <a href="{{ route('login') }}" class="font-semibold text-race hover:underline">Log in</a>
    </p>
</x-layouts::auth>
