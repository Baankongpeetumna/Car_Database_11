<x-layouts::app :title="'Edit Member'">
    @include('commerce.messages')

    @php
        $field = 'mt-1 w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 dark:border-zinc-600 dark:bg-zinc-800';
        $isSelf = (int) $member->member_id === (int) auth()->id();
    @endphp

    <h1 class="mb-4 text-2xl font-semibold">
        แก้ไขสมาชิก #{{ $member->member_id }}
    </h1>

    <div class="mb-6 flex flex-wrap gap-4">
        <a href="{{ route('admin.members.index') }}"
           class="text-blue-600">
            ← รายการสมาชิก
        </a>

        <a href="{{ route('admin.members.edit', $member) }}"
           class="text-blue-600">
            เปิดข้อมูลล่าสุด
        </a>
    </div>

    <form method="POST"
          action="{{ route('admin.members.update', $member) }}"
          class="max-w-2xl space-y-5">
        @csrf
        @method('PATCH')

        <input type="hidden"
               name="member_version"
               value="{{ old('member_version', $version) }}">

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label for="first_name" class="text-sm">ชื่อ</label>
                <input id="first_name"
                       name="first_name"
                       type="text"
                       value="{{ old('first_name', $member->first_name) }}"
                       maxlength="255"
                       required
                       class="{{ $field }}">
            </div>

            <div>
                <label for="last_name" class="text-sm">นามสกุล</label>
                <input id="last_name"
                       name="last_name"
                       type="text"
                       value="{{ old('last_name', $member->last_name) }}"
                       maxlength="255"
                       required
                       class="{{ $field }}">
            </div>
        </div>

        <div>
            <label for="email" class="text-sm">อีเมล</label>
            <input id="email"
                   name="email"
                   type="email"
                   value="{{ old('email', $member->email) }}"
                   maxlength="255"
                   required
                   class="{{ $field }}">
        </div>

        <div>
            <label for="phone" class="text-sm">เบอร์โทรศัพท์</label>
            <input id="phone"
                   name="phone"
                   type="tel"
                   value="{{ old('phone', $member->phone) }}"
                   maxlength="30"
                   class="{{ $field }}">
        </div>

        <div>
            <label for="address" class="text-sm">ที่อยู่</label>
            <textarea id="address"
                      name="address"
                      rows="3"
                      maxlength="5000"
                      class="{{ $field }}">{{ old('address', $member->address) }}</textarea>
        </div>

        <div class="rounded-xl border border-zinc-200 p-4 dark:border-zinc-700">
            <h2 class="mb-4 font-semibold">สิทธิ์การใช้งาน</h2>

            @if ($isSelf)
                <input type="hidden" name="role" value="admin">

                <p class="text-sm">
                    Role: Admin — บัญชีที่กำลังใช้งาน
                </p>

                <p class="mt-1 text-sm text-zinc-500">
                    เปลี่ยนสิทธิ์ของบัญชีอื่นได้จากหน้ารายการสมาชิก
                </p>
            @else
                <label for="role" class="text-sm">Role</label>

                <select id="role"
                        name="role"
                        required
                        class="{{ $field }}">
                    <option value="member"
                            @selected(old('role', $member->role) === 'member')>
                        Member
                    </option>

                    <option value="admin"
                            @selected(old('role', $member->role) === 'admin')>
                        Admin
                    </option>
                </select>

                <p class="mt-2 text-sm text-zinc-500">
                    Admin สามารถเข้าหน้าจัดการร้านและจัดการสมาชิกได้
                </p>
            @endif
        </div>

        <div class="space-y-4 rounded-xl border border-zinc-200 p-4 dark:border-zinc-700">
            <h2 class="font-semibold">ระดับสมาชิกและคะแนน</h2>

            <div>
                <label for="tier_id" class="text-sm">Tier</label>

                <select id="tier_id"
                        name="tier_id"
                        required
                        class="{{ $field }}"
                        onchange="document.getElementById('points').value = this.options[this.selectedIndex].dataset.minPoints;">
                    @foreach ($tiers as $tier)
                        <option value="{{ $tier->tier_id }}"
                                data-min-points="{{ $tier->min_points }}"
                                @selected((string) old('tier_id', $member->tier_id) === (string) $tier->tier_id)>
                            {{ $tier->tier_name }}
                            — ขั้นต่ำ {{ number_format($tier->min_points) }} คะแนน
                            — ส่วนลด {{ $tier->discount_percent }}%
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="points" class="text-sm">คะแนนสะสม</label>

                <input id="points"
                       name="points"
                       type="number"
                       min="0"
                       max="4294967295"
                       step="1"
                       required
                       value="{{ old('points', $member->points) }}"
                       class="{{ $field }}">
            </div>

            <p class="text-sm text-zinc-500">
                เมื่อเลือก Tier ใหม่ คะแนนจะเปลี่ยนเป็นคะแนนขั้นต่ำของระดับนั้น
                สามารถปรับคะแนนเพิ่มเติมได้ แต่คะแนนต้องตรงกับ Tier ที่เลือก
            </p>

            <p class="text-sm text-zinc-500">
                การแก้คะแนนนี้จะบันทึกใน Activity Log
                และไม่เปลี่ยนคะแนนที่บันทึกไว้ในคำสั่งซื้อเดิม
            </p>
        </div>

        <div class="flex gap-3">
            <button type="submit"
                    class="rounded-lg bg-blue-600 px-5 py-2 text-white">
                บันทึกข้อมูล
            </button>

            <a href="{{ route('admin.members.index') }}"
               class="rounded-lg border border-zinc-300 px-5 py-2 dark:border-zinc-600">
                ยกเลิก
            </a>
        </div>
    </form>
</x-layouts::app>