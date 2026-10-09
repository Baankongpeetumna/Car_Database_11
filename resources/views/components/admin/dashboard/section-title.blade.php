@props(['title'])

{{-- Big italic section title with a short red bar underneath --}}
<h2 class="font-display text-2xl font-black uppercase italic leading-none tracking-tight text-zinc-900 dark:text-white">
    {{ $title }}
</h2>
<div class="mt-2 h-1 w-10 -skew-x-12 bg-red-600"></div>
