{{-- ช่องกรอกที่ใช้ร่วมกันระหว่างหน้าเพิ่มและแก้ไข --}}
<div>
    <label for="category_name" class="mb-1 block font-semibold">
        Category Name
    </label>

    <input
        id="category_name"
        name="category_name"
        type="text"
        required
        maxlength="255"
        value="{{ old('category_name', $category->category_name) }}"
        class="w-full rounded-lg border border-zinc-300 bg-white p-3 dark:border-zinc-600 dark:bg-zinc-800"
    >
</div>
