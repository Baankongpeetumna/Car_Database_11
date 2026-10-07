{{-- แสดงดาว 1-5 จาก $rating (null = รีวิวเก่าที่ยังไม่มีคะแนน) --}}
@if ($rating)
    <span class="text-amber-500" aria-label="{{ $rating }} out of 5 stars" title="{{ $rating }}/5">
        {{ str_repeat('★', $rating) }}<span class="text-zinc-300 dark:text-zinc-600">{{ str_repeat('★', 5 - $rating) }}</span>
    </span>
@else
    <span class="text-xs text-zinc-400">No rating</span>
@endif
