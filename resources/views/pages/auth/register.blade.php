<x-layouts::auth :title="__('Register')">
    <div class="flex flex-col gap-6">
        <x-auth-header
            title="สมัครสมาชิก"
            description="กรอกข้อมูลเพื่อสร้างบัญชีของคุณ"
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
                label="ชื่อ"
                :value="old('first_name')"
                type="text"
                required
                autofocus
                autocomplete="given-name"
                maxlength="255"
            />

            <flux:input
                name="last_name"
                label="นามสกุล"
                :value="old('last_name')"
                type="text"
                required
                autocomplete="family-name"
                maxlength="255"
            />

            <flux:input
                name="email"
                label="อีเมล"
                :value="old('email')"
                type="email"
                required
                autocomplete="email"
                maxlength="255"
            />

            <flux:input
                name="phone"
                label="เบอร์โทรศัพท์"
                :value="old('phone')"
                type="tel"
                autocomplete="tel"
                maxlength="30"
            />

            <flux:textarea
                name="address"
                label="ที่อยู่"
                rows="3"
                maxlength="5000"
            >{{ old('address') }}</flux:textarea>

            <flux:input
                name="password"
                label="รหัสผ่าน"
                type="password"
                required
                autocomplete="new-password"
                viewable
            />

            <flux:input
                name="password_confirmation"
                label="ยืนยันรหัสผ่าน"
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
                สมัครสมาชิก
            </flux:button>
        </form>

        <div class="text-center text-sm">
            มีบัญชีแล้ว?
            <flux:link :href="route('login')" wire:navigate>
                เข้าสู่ระบบ
            </flux:link>
        </div>
    </div>
</x-layouts::auth>