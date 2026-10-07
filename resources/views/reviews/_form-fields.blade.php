{{-- ช่องให้คะแนนและเขียนรีวิว ใช้ทั้งฟอร์มเขียนใหม่และฟอร์มแก้ไข --}}
@php
    $currentRating = (int) old('rating', $review?->rating);
@endphp

<fieldset>
    <legend class="mb-1 block font-semibold">Rating</legend>

    <div class="flex flex-wrap gap-4">
        @for ($i = 5; $i >= 1; $i--)
            <label class="flex items-center gap-1">
                <input type="radio" name="rating" value="{{ $i }}" required @checked($currentRating === $i)>
                <span class="text-amber-500">{{ str_repeat('★', $i) }}</span>
                <span class="text-sm text-zinc-500">{{ $i }}</span>
            </label>
        @endfor
    </div>
</fieldset>

<div>
    <label for="comment" class="mb-1 block font-semibold">Your review</label>

    <textarea id="comment" name="comment" rows="4" required maxlength="2000"
              class="w-full rounded-lg border border-zinc-300 bg-white p-3 dark:border-zinc-600 dark:bg-zinc-800">{{ old('comment', $review?->comment) }}</textarea>
</div>
