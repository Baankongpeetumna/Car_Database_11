{{-- ช่องกรอกที่ใช้ร่วมกันระหว่างหน้าเพิ่มและแก้ไข --}}
@php
    $field = 'w-full rounded-lg border border-zinc-300 bg-white p-3 dark:border-zinc-600 dark:bg-zinc-800';
    $isBaseTier = $tier->exists && $tier->getOriginal('min_points') === 0;
@endphp

<div>
    <label for="tier_name" class="mb-1 block font-semibold">
        Tier Name
    </label>

    <input id="tier_name" name="tier_name" type="text" required maxlength="255"
           value="{{ old('tier_name', $tier->tier_name) }}" class="{{ $field }}">
</div>

<div>
    <label for="min_points" class="mb-1 block font-semibold">
        Minimum Points
    </label>

    @if ($isBaseTier)
        {{-- ระดับเริ่มต้นต้องเป็น 0 เสมอ (server ตรวจซ้ำอีกครั้ง) --}}
        <input type="hidden" name="min_points" value="0">
        <input id="min_points" type="number" value="0" disabled class="{{ $field }} opacity-60">
        <p class="mt-1 text-xs text-zinc-500">
            This is the default tier for new members, so its minimum points must stay 0.
        </p>
    @else
        <input id="min_points" name="min_points" type="number" required min="0" step="1"
               value="{{ old('min_points', $tier->min_points) }}" class="{{ $field }}">
        <p class="mt-1 text-xs text-zinc-500">
            1 point per ฿1,000 of completed orders. Each tier needs a different value.
        </p>
    @endif
</div>

<div>
    <label for="discount_percent" class="mb-1 block font-semibold">
        Discount (%)
    </label>

    <input id="discount_percent" name="discount_percent" type="number" required
           min="0" max="100" step="0.01"
           value="{{ old('discount_percent', $tier->discount_percent ?? 0) }}" class="{{ $field }}">
</div>
