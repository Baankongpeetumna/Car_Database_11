@props(['count' => 0])

<div class="rounded-xl border border-amber-200 bg-amber-50 px-5 py-4 dark:border-amber-900/50 dark:bg-amber-950/20">
    <p class="flex items-center gap-2 text-sm font-medium text-amber-800 dark:text-amber-300">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
             stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="shrink-0" aria-hidden="true">
            <circle cx="12" cy="12" r="10"/>
            <line x1="12" x2="12" y1="8" y2="12"/>
            <line x1="12" x2="12.01" y1="16" y2="16"/>
        </svg>

        <span>{{ $count }} {{ \Illuminate\Support\Str::plural('order', $count) }} pending · Verify the payment method before changing an order's status</span>
    </p>
</div>