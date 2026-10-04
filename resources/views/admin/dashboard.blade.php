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

            <flux:text class="mt-2">
                หน้านี้อนุญาตเฉพาะบัญชีที่มีสิทธิ์ admin
                ต่อไปสามารถเพิ่มระบบจัดการแบรนด์ หมวดหมู่ และรถได้
            </flux:text>
        </div>

        <flux:button :href="route('dashboard')" wire:navigate>
            กลับ Dashboard
        </flux:button>
    </div>
</x-layouts::app>