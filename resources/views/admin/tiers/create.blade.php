<x-layouts::app :title="'Add Tier'">
    <div class="min-h-full bg-zinc-50 dark:bg-zinc-950">
        <div class="relative mx-auto w-full max-w-5xl space-y-6 px-4 pb-10 pt-2">

            {{-- Background: dot grid, red glow (dark), diagonal stripes --}}
            <div class="pointer-events-none absolute inset-x-0 -top-8 h-72 opacity-60 [background-image:radial-gradient(circle,rgba(128,128,128,0.35)_1px,transparent_1px)] [background-size:24px_24px] [mask-image:linear-gradient(to_bottom,black,transparent)]"></div>
            <div class="pointer-events-none absolute inset-x-0 -top-8 hidden h-72 bg-[radial-gradient(ellipse_at_top,rgba(220,38,38,0.16),transparent_70%)] dark:block"></div>
            <div class="pointer-events-none absolute -right-2 top-0 hidden space-y-1.5 md:block" aria-hidden="true">
                <div class="h-2 w-24 -skew-x-[30deg] bg-red-700/80"></div>
                <div class="ml-4 h-2 w-20 -skew-x-[30deg] bg-red-700/60"></div>
                <div class="ml-8 h-2 w-14 -skew-x-[30deg] bg-red-700/40"></div>
            </div>

            @include('commerce.messages')

            {{-- Header --}}
            <header class="relative flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <nav class="flex items-center gap-2 text-sm text-zinc-400" aria-label="Breadcrumb">
                        <a href="{{ route('admin.dashboard') }}" class="hover:text-red-600">Admin</a>
                        <span>/</span>
                        <a href="{{ route('admin.tiers.index') }}" class="hover:text-red-600">Membership Tiers</a>
                        <span>/</span>
                        <span class="text-zinc-600 dark:text-zinc-300">New</span>
                    </nav>

                    <h1 class="font-display mt-1 text-4xl font-black uppercase italic leading-none tracking-tight text-zinc-900 sm:text-5xl dark:text-white">
                        New Tier
                    </h1>
                    <div class="mt-2 h-1 w-14 -skew-x-12 bg-red-600"></div>
                </div>

                <a href="{{ route('admin.tiers.index') }}"
                   class="w-fit rounded-lg border border-zinc-300 px-4 py-2 text-sm font-medium transition hover:border-zinc-400 dark:border-zinc-700">
                    ← All tiers
                </a>
            </header>

            <form method="POST"
                  action="{{ route('admin.tiers.store') }}"
                  class="relative grid items-start gap-5 lg:grid-cols-[1fr_320px]">
                @csrf

                @include('admin.tiers._form')
            </form>
        </div>
    </div>
</x-layouts::app>