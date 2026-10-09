@props(['name', 'label', 'id' => null, 'autocomplete' => 'current-password', 'required' => true])

@php $id = $id ?? $name; @endphp

<div>
    <label for="{{ $id }}" class="field-label">{{ $label }}</label>
    <div class="relative">
        <input id="{{ $id }}" type="password" name="{{ $name }}" autocomplete="{{ $autocomplete }}" @if ($required) required @endif
               {{ $attributes->merge(['class' => 'field pr-11'.($errors->has($name) ? ' !border-race' : '')]) }}>
        <button type="button" class="absolute inset-y-0 right-0 flex items-center px-3.5 text-zinc-400 hover:text-ink" aria-label="Show or hide password"
                onclick="const i=this.previousElementSibling; i.type = i.type === 'password' ? 'text' : 'password';">
            <x-store.icon name="eye" class="size-4" />
        </button>
    </div>
    @error($name)
        <p class="mt-1.5 text-xs text-race">{{ $message }}</p>
    @enderror
</div>
