<x-layouts::auth :title="'Log in'">
    <p class="font-display text-xs font-semibold uppercase italic tracking-[0.18em] text-race">Welcome back</p>
    <h1 class="mt-1 font-display text-4xl font-extrabold uppercase italic leading-none text-ink">Log in</h1>
    <p class="mt-2 text-sm text-zinc-500">Enter your email and password below to log in.</p>

    @if (session('status'))
        <p class="mt-5 rounded-xl bg-emerald-50 px-4 py-3 text-sm text-emerald-700">{{ session('status') }}</p>
    @endif

    @if ($errors->any())
        <div role="alert" class="mt-5 flex items-start gap-2 rounded-xl border border-race/30 bg-race-soft px-4 py-3 text-sm text-race-dark">
            <x-store.icon name="alert" class="mt-0.5 size-4 text-race" />
            <p>{{ $errors->first() === trans('auth.failed') ? 'Incorrect email or password.' : $errors->first() }}</p>
        </div>
    @endif

    <form method="POST" action="{{ route('login.store') }}" class="mt-6 space-y-5">
        @csrf

        <div>
            <label for="email" class="field-label">Email address</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="email" placeholder="email@example.com" class="field">
        </div>

        <div>
            <x-store.password-input name="password" label="Password" autocomplete="current-password" />
            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}" class="mt-2 inline-block text-xs font-semibold text-race hover:underline">Forgot your password?</a>
            @endif
        </div>

        <label class="flex cursor-pointer items-center gap-2 text-sm text-zinc-600">
            <input type="checkbox" name="remember" value="1" @checked(old('remember')) class="size-4 accent-race">
            Remember me
        </label>

        <button type="submit" class="w-full rounded-xl bg-race px-4 py-3 text-sm font-semibold text-white hover:bg-race-dark" data-test="login-button">
            Log in
        </button>
    </form>

    <p class="mt-6 text-center text-sm text-zinc-500">
        Don't have an account?
        <a href="{{ route('register') }}" class="font-semibold text-race hover:underline">Sign up</a>
    </p>
</x-layouts::auth>
