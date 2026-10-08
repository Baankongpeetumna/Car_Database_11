<x-layouts::app :title="'Admin Dashboard'">
    <div class="space-y-6">
        <div>
            <flux:heading size="xl" level="1">
                Admin Dashboard
            </flux:heading>

            <flux:text class="mt-2">
                Welcome, {{ auth()->user()->name }}
            </flux:text>
        </div>

        <div class="rounded-xl border border-zinc-200 p-6 dark:border-zinc-700">
            <flux:heading size="lg">
                Store Management
            </flux:heading>

            <div class="mt-4 flex flex-wrap gap-3">

                <a href="{{ route('admin.members.index') }}"
                class="inline-block rounded-lg bg-blue-600 px-4 py-2 text-white">
                    Manage Members
                </a>
                <a href="{{ route('admin.orders.index') }}"
                   class="inline-block rounded-lg bg-blue-600 px-4 py-2 text-white">
                    Manage Orders
                </a>

                {{-- เมนูจัดการข้อมูลร้าน (routes/admin-catalog.php) --}}
                <a href="{{ route('admin.brands.index') }}"
                   class="inline-block rounded-lg bg-blue-600 px-4 py-2 text-white">
                    Manage Brands
                </a>

                <a href="{{ route('admin.categories.index') }}"
                   class="inline-block rounded-lg bg-blue-600 px-4 py-2 text-white">
                    Manage Categories
                </a>

                <a href="{{ route('admin.cars.index') }}"
                   class="inline-block rounded-lg bg-blue-600 px-4 py-2 text-white">
                    Manage Cars
                </a>

                <a href="{{ route('admin.tiers.index') }}"
                   class="inline-block rounded-lg bg-blue-600 px-4 py-2 text-white">
                    Manage Membership Tiers
                </a>

                <a href="{{ route('admin.reviews.index') }}"
                   class="inline-block rounded-lg bg-blue-600 px-4 py-2 text-white">
                    Manage Reviews
                </a>

                <a href="{{ route('admin.logs.index') }}"
                   class="inline-block rounded-lg bg-zinc-700 px-4 py-2 text-white">
                    Activity Log
                </a>
            </div>
        </div>

        <flux:button :href="route('dashboard')" wire:navigate>
            Back to Dashboard
        </flux:button>
    </div>
</x-layouts::app>
