{{-- หน้าแบบ @extends('layouts.public') ใช้ layout หน้าร้านเดียวกับ layouts/store --}}
<x-layouts::store :title="$title ?? null">
    {{-- หน้าที่ต้องการเต็มความกว้าง (เช่น หน้าแรก) ใช้ section "bleed" --}}
    @hasSection('bleed')
        @yield('bleed')
    @else
        <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            @yield('content')
        </div>
    @endif
</x-layouts::store>
