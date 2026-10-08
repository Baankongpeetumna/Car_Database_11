<?php

use App\Models\User;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts::store'), Title('Profile')] class extends Component {
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

<div>
    <x-store.account-shell active="profile" heading="Profile" subheading="Update your personal information. Your address is used as the default shipping address.">
        <form wire:submit="updateProfileInformation" class="rounded-3xl border border-line bg-white p-6">
            <div class="grid gap-5 sm:grid-cols-2">
                @foreach ([['first_name', 'First name', 'text', 'given-name'], ['last_name', 'Last name', 'text', 'family-name'], ['email', 'Email address', 'email', 'email'], ['phone', 'Phone number', 'tel', 'tel']] as [$name, $label, $type, $auto])
                    <div>
                        <label for="{{ $name }}" class="field-label">{{ $label }}</label>
                        <input id="{{ $name }}" type="{{ $type }}" wire:model="{{ $name }}" autocomplete="{{ $auto }}" @if ($name !== 'phone') required @endif @class(['field', '!border-race' => $errors->has($name)])>
                        @error($name) <p class="mt-1.5 text-xs text-race">{{ $message }}</p> @enderror
                    </div>
                @endforeach

                <div class="sm:col-span-2">
                    <label for="address" class="field-label">Address <span class="text-xs font-normal text-zinc-400">(default shipping address)</span></label>
                    <textarea id="address" wire:model="address" rows="3" autocomplete="street-address" @class(['field', '!border-race' => $errors->has('address')])></textarea>
                    @error('address') <p class="mt-1.5 text-xs text-race">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="mt-6 flex flex-wrap items-center justify-between gap-3 border-t border-line pt-5">
                <p class="text-xs text-zinc-500">Member since {{ auth()->user()->created_at?->format('d/m/Y') ?? '-' }}</p>
                <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-race px-5 py-2.5 text-sm font-semibold text-white hover:bg-race-dark disabled:opacity-60" wire:loading.attr="disabled" data-test="update-profile-button">
                    <span wire:loading.remove wire:target="updateProfileInformation">Save</span>
                    <span wire:loading wire:target="updateProfileInformation">Saving...</span>
                </button>
            </div>
        </form>
    </x-store.account-shell>
</div>
