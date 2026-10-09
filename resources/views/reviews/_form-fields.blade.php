{{-- ช่องให้คะแนนและเขียนรีวิว ใช้ทั้งฟอร์มเขียนใหม่และฟอร์มแก้ไข --}}
@php
    $currentRating = (int) old('rating', $review?->rating);
@endphp

<fieldset>
    <legend class="field-label">Rating</legend>

    {{-- เรียง 5 → 1 แล้วกลับด้วย flex-row-reverse เพื่อให้ hover ดาวทางซ้ายติดสีตามได้ด้วย CSS --}}
    <div class="flex w-fit flex-row-reverse justify-end gap-1">
        @for ($i = 5; $i >= 1; $i--)
            <label class="group/star cursor-pointer" title="{{ $i }} {{ Str::plural('star', $i) }}">
                <input type="radio" name="rating" value="{{ $i }}" required @checked($currentRating === $i) class="peer sr-only">
                <span class="sr-only">{{ $i }} {{ Str::plural('star', $i) }}</span>
                <svg class="size-8 text-line transition peer-checked:text-sun peer-focus-visible:ring-2 peer-focus-visible:ring-race/40 rounded" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path d="M10 1.5l2.6 5.3 5.9.9-4.25 4.1 1 5.85L10 14.9l-5.25 2.75 1-5.85L1.5 7.7l5.9-.9z"/>
                </svg>
            </label>
        @endfor
    </div>
    @error('rating')
        <p class="mt-1.5 text-xs text-race">{{ $message }}</p>
    @enderror
</fieldset>

<div>
    <label for="comment" class="field-label">Your review</label>
    <textarea id="comment" name="comment" rows="4" required maxlength="2000" placeholder="How was the car? Share your experience."
              @class(['field', '!border-race' => $errors->has('comment')])>{{ old('comment', $review?->comment) }}</textarea>
    @error('comment')
        <p class="mt-1.5 text-xs text-race">{{ $message }}</p>
    @enderror
</div>

@once
    @push('scripts')
        <script>
            // ไฮไลต์ดาวตั้งแต่ดาวที่ 1 ถึงดาวที่เลือก
            document.querySelectorAll('input[name=rating]').forEach((input) => {
                const paint = () => {
                    const chosen = Number(document.querySelector('input[name=rating]:checked')?.value || 0);
                    document.querySelectorAll('input[name=rating]').forEach((r) => {
                        r.nextElementSibling.nextElementSibling.classList.toggle('!text-sun', Number(r.value) <= chosen);
                    });
                };
                input.addEventListener('change', paint);
                paint();
            });
        </script>
    @endpush
@endonce
