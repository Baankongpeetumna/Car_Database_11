<x-layouts::app :title="'Add Car'">
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
                    <span class="text-zinc-600 dark:text-zinc-300">Add new car</span>
                </div>

                <flux:heading size="xl" level="1" class="font-display mt-2 font-semibold">
                    Add new car
                </flux:heading>

                <flux:text class="mt-1 text-zinc-500">
                    Fill in the details to add a car to your showroom
                </flux:text>
            </div>

            <form method="POST"
                  action="{{ route('admin.cars.store') }}"
                  enctype="multipart/form-data">
                @csrf

                @include('admin.cars._form')
            </form>

        </div>
    </div>
</x-layouts::app>