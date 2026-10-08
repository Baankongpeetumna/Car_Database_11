<?php

use App\Concerns\PasswordValidationRules;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Actions\DisableTwoFactorAuthentication;
use Laravel\Fortify\Features;
use Laravel\Fortify\Fortify;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts::store'), Title('Security')] class extends Component {
    use PasswordValidationRules;

    public string $current_password = '';
    public string $password = '';
    public string $password_confirmation = '';



    /**
     * Mount the component.
     */
    public function mount(DisableTwoFactorAuthentication $disableTwoFactorAuthentication): void
    {

    }

    /**
     * Update the password for the currently authenticated user.
     */
    public function updatePassword(): void
    {
        try {
            $validated = $this->validate([
                'current_password' => $this->currentPasswordRules(),
                'password' => $this->passwordRules(),
            ]);
        } catch (ValidationException $e) {
            $this->reset('current_password', 'password', 'password_confirmation');

            throw $e;
        }

        Auth::user()->update([
            'password' => $validated['password'],
        ]);

        $this->reset('current_password', 'password', 'password_confirmation');

        Flux::toast(variant: 'success', text: __('Password updated.'));
    }


}; ?>

<div>
    <x-store.account-shell active="security" heading="Update password" subheading="Ensure your account is using a long, random password to stay secure.">
        <form wire:submit="updatePassword" class="max-w-xl space-y-5 rounded-3xl border border-line bg-white p-6">
            @foreach ([['current_password', 'Current password', 'current-password'], ['password', 'New password', 'new-password'], ['password_confirmation', 'Confirm password', 'new-password']] as [$name, $label, $auto])
                <div>
                    <label for="{{ $name }}" class="field-label">{{ $label }}</label>
                    <div class="relative">
                        <input id="{{ $name }}" type="password" wire:model="{{ $name }}" required autocomplete="{{ $auto }}" @class(['field pr-11', '!border-race' => $errors->has($name)])>
                        <button type="button" class="absolute inset-y-0 right-0 flex items-center px-3.5 text-zinc-400 hover:text-ink" aria-label="Show or hide password"
                                onclick="const i=this.previousElementSibling; i.type = i.type === 'password' ? 'text' : 'password';">
                            <x-store.icon name="eye" class="size-4" />
                        </button>
                    </div>
                    @error($name) <p class="mt-1.5 text-xs text-race">{{ $message }}</p> @enderror
                </div>
            @endforeach

            <div class="flex justify-end border-t border-line pt-5">
                <button type="submit" class="rounded-xl bg-race px-5 py-2.5 text-sm font-semibold text-white hover:bg-race-dark" data-test="update-password-button">Save</button>
            </div>
        </form>
    </x-store.account-shell>
</div>
