<x-layouts::app :title="'Edit Car'">
    <div class="min-h-full bg-zinc-50 dark:bg-zinc-950">
        <div class="mx-auto max-w-7xl space-y-5 p-2 sm:p-4">

            @include('commerce.messages')

            {{-- Header: no card --}}
            <div class="flex flex-col gap-4 px-1 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <div class="flex items-center gap-2 text-sm text-zinc-400">
                        <a href="{{ route('admin.dashboard') }}" class="hover:text-red-600">Admin</a>
                        <span>/</span>
                        <a href="{{ route('admin.cars.index') }}" class="hover:text-red-600">Cars</a>
                        <span>/</span>
                        <span class="text-zinc-600 dark:text-zinc-300">Edit</span>
                    </div>

                    <h1 class="font-display mt-1 text-4xl font-black uppercase italic leading-none tracking-tight
                               text-zinc-900 sm:text-5xl dark:text-white">
                        Edit car
                    </h1>
                    <div class="mt-2 h-1 w-14 -skew-x-12 bg-red-600"></div>

                    <p class="mt-2 text-sm text-zinc-500">{{ $car->model_name }} · {{ $car->model_year }}</p>
                </div>

                <a href="{{ route('products.show', $car) }}"
                   class="inline-flex h-10 items-center justify-center rounded-xl border border-zinc-300 px-4 text-sm font-semibold text-zinc-700 transition
                          hover:border-zinc-500 dark:border-zinc-700 dark:text-zinc-300">
                    View in store
                </a>
            </div>

            <form method="POST"
                  action="{{ route('admin.cars.update', $car) }}"
                  enctype="multipart/form-data">
                @csrf
                @method('PUT')

                @include('admin.cars._form')
            </form>

        </div>
    </div>
</x-layouts::app>