@props(['rating', 'size' => 'size-4'])

{{-- ดาว 1-5 สำหรับหน้าร้าน (null = รีวิวเก่าที่ยังไม่มีคะแนน) --}}
@if ($rating)
    <span {{ $attributes->merge(['class' => 'inline-flex items-center gap-0.5']) }} aria-label="{{ $rating }} out of 5 stars" title="{{ $rating }}/5">
        @for ($i = 1; $i <= 5; $i++)
            <svg class="{{ $size }} {{ $i <= $rating ? 'text-sun' : 'text-line' }}" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <path d="M10 1.5l2.6 5.3 5.9.9-4.25 4.1 1 5.85L10 14.9l-5.25 2.75 1-5.85L1.5 7.7l5.9-.9z"/>
            </svg>
        @endfor
    </span>
@else
    <span class="text-xs text-zinc-400">No rating</span>
@endif
