<?php

use App\Models\User;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Profile settings')] class extends Component {
    public string $first_name = '';
    public string $last_name = '';
    public string $email = '';
    public string $phone = '';
    public string $address = '';

    public function mount(): void
    {
        $user = Auth::user();

        $this->first_name = $user->first_name;
        $this->last_name = $user->last_name;
        $this->email = $user->email;
        $this->phone = $user->phone ?? '';
        $this->address = $user->address ?? '';
    }

    public function updateProfileInformation(): void
    {
        /** @var User $user */
        $user = Auth::user();

        $this->email = strtolower(trim($this->email));

        $validated = $this->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('MEMBER', 'email')
                    ->ignore($user->getKey(), 'member_id'),
            ],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:5000'],
        ]);

        $validated['phone'] = $validated['phone'] === ''
            ? null
            : $validated['phone'];

        $validated['address'] = $validated['address'] === ''
            ? null
            : $validated['address'];

        $user->fill($validated);
        $user->save();

        Flux::toast(
            variant: 'success',
            text: 'บันทึกข้อมูลโปรไฟล์แล้ว',
        );
    }
}; ?>

<section class="w-full">
    @include('partials.settings-heading')

    <flux:heading level="2" class="sr-only">
        ข้อมูลโปรไฟล์
    </flux:heading>

    <x-pages::settings.layout
        heading="ข้อมูลโปรไฟล์"
        subheading="แก้ไขข้อมูลส่วนตัวของคุณ"
    >         @php
            $member = auth()->user();
            $membershipTier = $member->tier;
        @endphp

        <div class="my-6 rounded-xl border border-zinc-200 p-5 dark:border-zinc-700">
            <h3 class="text-lg font-semibold text-zinc-900 dark:text-white">
                ข้อมูลสมาชิก
            </h3>

            <dl class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-3">
                <div>
                    <dt class="text-sm text-zinc-500 dark:text-zinc-400">
                        สิทธิ์ผู้ใช้งาน
                    </dt>
                    <dd class="mt-1 font-semibold text-zinc-900 dark:text-white">
                        {{ $member->isAdmin() ? 'ผู้ดูแลระบบ' : 'สมาชิก' }}
                    </dd>
                </div>

                <div>
                    <dt class="text-sm text-zinc-500 dark:text-zinc-400">
                        ระดับสมาชิก
                    </dt>
                    <dd class="mt-1 font-semibold text-zinc-900 dark:text-white">
                        {{ $membershipTier?->tier_name ?? 'ยังไม่มีระดับสมาชิก' }}
                    </dd>
                </div>

                <div>
                    <dt class="text-sm text-zinc-500 dark:text-zinc-400">
                        คะแนนสะสม
                    </dt>
                    <dd class="mt-1 font-semibold text-zinc-900 dark:text-white">
                        {{ number_format($member->points) }} คะแนน
                    </dd>
                </div>
            </dl>

            @if ($membershipTier)
                <p class="mt-4 text-sm text-zinc-600 dark:text-zinc-300">
                    ส่วนลดตามระดับสมาชิก
                    {{ number_format((float) $membershipTier->discount_percent, 2) }}%
                </p>
            @endif
        </div>
        <form
            wire:submit="updateProfileInformation"
            class="my-6 w-full space-y-6"
        >
            <flux:input
                wire:model="first_name"
                label="ชื่อ"
                type="text"
                required
                autocomplete="given-name"
            />

            <flux:input
                wire:model="last_name"
                label="นามสกุล"
                type="text"
                required
                autocomplete="family-name"
            />

            <flux:input
                wire:model="email"
                label="อีเมล"
                type="email"
                required
                autocomplete="email"
            />

            <flux:input
                wire:model="phone"
                label="เบอร์โทรศัพท์"
                type="tel"
                autocomplete="tel"
            />

            <flux:textarea
                wire:model="address"
                label="ที่อยู่"
                rows="3"
            />

            <flux:button
                variant="primary"
                type="submit"
                data-test="update-profile-button"
            >
                บันทึกข้อมูล
            </flux:button>
        </form>
    </x-pages::settings.layout>
</section>