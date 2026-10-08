<x-layouts::app :title="'Edit Car'">
    <div class="min-h-full bg-zinc-50 dark:bg-zinc-950">
        <div class="space-y-6 p-2 sm:p-4">

            @include('commerce.messages')

            {{-- Header --}}
            <div>
                <div class="flex items-center gap-2 text-sm text-zinc-400">
                    <a href="{{ route('admin.dashboard') }}" class="hover:text-red-600">Admin</a>
                    <span>/</span>
                    <a href="{{ route('admin.cars.index') }}" class="hover:text-red-600">Cars</a>
                    <span>/</span>
                    <span class="text-zinc-600 dark:text-zinc-300">Edit #{{ $car->car_id }}</span>
                </div>

                <flux:heading size="xl" level="1" class="font-display mt-2 font-semibold">
                    Edit car
                </flux:heading>

                <flux:text class="mt-1 text-zinc-500">
                    {{ $car->model_name }} · {{ $car->model_year }}
                </flux:text>
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