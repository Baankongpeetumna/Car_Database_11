<x-layouts::app :title="'Admin Dashboard'">
    <div class="space-y-6">
        <div>
            <flux:heading size="xl" level="1">
                หน้าผู้ดูแลระบบ
            </flux:heading>

            <flux:text class="mt-2">
                ยินดีต้อนรับ {{ auth()->user()->name }}
            </flux:text>
        </div>

        <div class="rounded-xl border border-zinc-200 p-6 dark:border-zinc-700">
            <flux:heading size="lg">
                ระบบจัดการร้านรถ
            </flux:heading>

            <a href="{{ route('admin.orders.index') }}"
   class="inline-block rounded-lg bg-blue-600 px-4 py-2 text-white">
    จัดการคำสั่งซื้อ
</a>
        </div>

        <flux:button :href="route('dashboard')" wire:navigate>
            กลับ Dashboard
        </flux:button>
    </div>
</x-layouts::app>