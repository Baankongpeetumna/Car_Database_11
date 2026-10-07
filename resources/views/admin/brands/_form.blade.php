{{-- ช่องกรอกที่ใช้ร่วมกันระหว่างหน้าเพิ่มและแก้ไข --}}
<div>
    <label for="brand_name" class="mb-1 block font-semibold">
        Brand Name
    </label>

    <input
        id="brand_name"
        name="brand_name"
        type="text"
        required
        maxlength="255"
        value="{{ old('brand_name', $brand->brand_name) }}"
        class="w-full rounded-lg border border-zinc-300 bg-white p-3 dark:border-zinc-600 dark:bg-zinc-800"
    >
</div>

<div>
    <label for="country" class="mb-1 block font-semibold">
        Country
    </label>

    <input
        id="country"
        name="country"
        type="text"
        required
        maxlength="100"
        value="{{ old('country', $brand->country) }}"
        class="w-full rounded-lg border border-zinc-300 bg-white p-3 dark:border-zinc-600 dark:bg-zinc-800"
    >
</div>
