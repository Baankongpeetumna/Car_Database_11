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
            text: 'Profile updated.',
        );
    }
}; ?>

<section class="w-full">
    @include('partials.settings-heading')

    <flux:heading level="2" class="sr-only">
        Profile
    </flux:heading>

    <x-pages::settings.layout
        heading="Profile"
        subheading="Update your personal information"
    >         @php
            $member = auth()->user();
            $membershipTier = $member->tier;
        @endphp

        <div class="my-6 rounded-xl border border-zinc-200 p-5 dark:border-zinc-700">
            <h3 class="text-lg font-semibold text-zinc-900 dark:text-white">
                Membership
            </h3>

            <dl class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-3">
                <div>
                    <dt class="text-sm text-zinc-500 dark:text-zinc-400">
                        Role
                    </dt>
                    <dd class="mt-1 font-semibold text-zinc-900 dark:text-white">
                        {{ $member->isAdmin() ? 'Administrator' : 'Member' }}
                    </dd>
                </div>

                <div>
                    <dt class="text-sm text-zinc-500 dark:text-zinc-400">
                        Membership tier
                    </dt>
                    <dd class="mt-1 font-semibold text-zinc-900 dark:text-white">
                        {{ $membershipTier?->tier_name ?? 'No tier yet' }}
                    </dd>
                </div>

                <div>
                    <dt class="text-sm text-zinc-500 dark:text-zinc-400">
                        Points
                    </dt>
                    <dd class="mt-1 font-semibold text-zinc-900 dark:text-white">
                        {{ number_format($member->points) }} points
                    </dd>
                </div>
            </dl>

            @if ($membershipTier)
                <p class="mt-4 text-sm text-zinc-600 dark:text-zinc-300">
                    Tier discount:
                    {{ number_format((float) $membershipTier->discount_percent, 2) }}%
                </p>
            @endif

            <a href="{{ route('membership.index') }}" class="mt-3 inline-block text-sm text-blue-600">
                See all tiers and your progress →
            </a>
        </div>
        <form
            wire:submit="updateProfileInformation"
            class="my-6 w-full space-y-6"
        >
            <flux:input
                wire:model="first_name"
                label="First name"
                type="text"
                required
                autocomplete="given-name"
            />

            <flux:input
                wire:model="last_name"
                label="Last name"
                type="text"
                required
                autocomplete="family-name"
            />

            <flux:input
                wire:model="email"
                label="Email"
                type="email"
                required
                autocomplete="email"
            />

            <flux:input
                wire:model="phone"
                label="Phone number"
                type="tel"
                autocomplete="tel"
            />

            <flux:textarea
                wire:model="address"
                label="Address"
                rows="3"
            />

            <flux:button
                variant="primary"
                type="submit"
                data-test="update-profile-button"
            >
                Save
            </flux:button>
        </form>
    </x-pages::settings.layout>
</section>