<x-layouts::auth :title="__('Register')">
    <div class="flex flex-col gap-6">
        <x-auth-header
            title="Create an account"
            description="Enter your details below to create your account"
        />

        <x-auth-session-status
            class="text-center"
            :status="session('status')"
        />

        <form
            method="POST"
            action="{{ route('register.store') }}"
            class="flex flex-col gap-6"
        >
            @csrf

            <flux:input
                name="first_name"
                label="First name"
                :value="old('first_name')"
                type="text"
                required
                autofocus
                autocomplete="given-name"
                maxlength="255"
            />

            <flux:input
                name="last_name"
                label="Last name"
                :value="old('last_name')"
                type="text"
                required
                autocomplete="family-name"
                maxlength="255"
            />

            <flux:input
                name="email"
                label="Email address"
                :value="old('email')"
                type="email"
                required
                autocomplete="email"
                maxlength="255"
            />

            <flux:input
                name="phone"
                label="Phone number"
                :value="old('phone')"
                type="tel"
                autocomplete="tel"
                maxlength="30"
            />

            <flux:textarea
                name="address"
                label="Address"
                rows="3"
                maxlength="5000"
            >{{ old('address') }}</flux:textarea>

            <flux:input
                name="password"
                label="Password"
                type="password"
                required
                autocomplete="new-password"
                viewable
            />

            <flux:input
                name="password_confirmation"
                label="Confirm password"
                type="password"
                required
                autocomplete="new-password"
                viewable
            />

            <flux:button
                type="submit"
                variant="primary"
                class="w-full"
                data-test="register-user-button"
            >
                Create account
            </flux:button>
        </form>

        <div class="text-center text-sm">
            Already have an account?
            <flux:link :href="route('login')" wire:navigate>
                Log in
            </flux:link>
        </div>
    </div>
</x-layouts::auth>