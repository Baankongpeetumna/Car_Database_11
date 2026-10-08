<x-layouts::app :title="'Manage Members'">
    @include('commerce.messages')

    @php
        $field = 'rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800';
    @endphp

    <div class="mb-6">
        <h1 class="text-2xl font-semibold">จัดการสมาชิก</h1>

        <p class="mt-2 text-sm text-zinc-500">
            พบทั้งหมด {{ $members->total() }} บัญชี
        </p>
    </div>

    <a href="{{ route('admin.dashboard') }}" class="text-blue-600">
        ← Admin Dashboard
    </a>

    <form method="GET"
          action="{{ route('admin.members.index') }}"
          class="my-5 flex flex-wrap gap-3">

        <input type="search"
               name="q"
               value="{{ request('q') }}"
               placeholder="ค้นหาชื่อ นามสกุล หรืออีเมล"
               aria-label="ค้นหาสมาชิก"
               class="{{ $field }} min-w-64 flex-1">

        <select name="role"
                aria-label="กรองสิทธิ์"
                class="{{ $field }}">
            <option value="">ทุกสิทธิ์</option>
            <option value="member" @selected(request('role') === 'member')>
                Member
            </option>
            <option value="admin" @selected(request('role') === 'admin')>
                Admin
            </option>
        </select>

        <select name="tier_id"
                aria-label="กรองระดับสมาชิก"
                class="{{ $field }}">
            <option value="">ทุกระดับสมาชิก</option>

            @foreach ($tiers as $tier)
                <option value="{{ $tier->tier_id }}"
                        @selected((string) request('tier_id') === (string) $tier->tier_id)>
                    {{ $tier->tier_name }}
                </option>
            @endforeach
        </select>

        <button type="submit"
                class="rounded-lg bg-blue-600 px-4 py-2 text-white">
            ค้นหา
        </button>

        <a href="{{ route('admin.members.index') }}"
           class="{{ $field }}">
            ล้าง
        </a>
    </form>

    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead>
                <tr>
                    <th class="p-3">ID</th>
                    <th class="p-3">สมาชิก</th>
                    <th class="p-3">Role</th>
                    <th class="p-3">Tier</th>
                    <th class="p-3">คะแนน</th>
                    <th class="p-3">คำสั่งซื้อ</th>
                    <th class="p-3">จัดการ</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($members as $member)
                    <tr class="border-t border-zinc-200 dark:border-zinc-700">
                        <td class="p-3">
                            {{ $member->member_id }}
                        </td>

                        <td class="p-3">
                            <div class="font-medium">
                                {{ $member->name }}

                                @if ((int) $member->member_id === (int) auth()->id())
                                    <span class="text-xs text-blue-600">
                                        (คุณ)
                                    </span>
                                @endif
                            </div>

                            <div class="text-zinc-500">
                                {{ $member->email }}
                            </div>

                            <div class="text-zinc-500">
                                {{ $member->phone ?: '-' }}
                            </div>
                        </td>

                        <td class="p-3">
                            <span class="{{ $member->isAdmin() ? 'text-red-600' : 'text-zinc-500' }}">
                                {{ ucfirst($member->role) }}
                            </span>
                        </td>

                        <td class="p-3">
                            {{ $member->tier?->tier_name ?? '-' }}
                        </td>

                        <td class="p-3">
                            {{ number_format($member->points) }}
                        </td>

                        <td class="p-3">
                            {{ number_format($member->orders_count) }}
                        </td>

                        <td class="p-3">
                            <div class="flex flex-wrap items-center gap-3">
                                <a href="{{ route('admin.members.edit', $member) }}"
                                   class="text-blue-600">
                                    แก้ไข
                                </a>

                                @if ((int) $member->member_id !== (int) auth()->id() && $member->orders_count === 0)
                                    <form method="POST"
                                          action="{{ route('admin.members.destroy', $member) }}"
                                          onsubmit="return confirm('ยืนยันลบบัญชีนี้? ตะกร้าและรีวิวของบัญชีจะถูกลบด้วย');">
                                        @csrf
                                        @method('DELETE')

                                        <button type="submit" class="text-red-600">
                                            ลบ
                                        </button>
                                    </form>
                                @elseif ($member->orders_count > 0)
                                    <span class="text-xs text-zinc-500">
                                        มีประวัติซื้อ ลบไม่ได้
                                    </span>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7"
                            class="p-6 text-center text-zinc-500">
                            ไม่พบสมาชิก
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">
        {{ $members->links() }}
    </div>
</x-layouts::app>