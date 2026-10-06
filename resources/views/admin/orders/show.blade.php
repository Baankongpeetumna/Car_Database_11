@extends('layouts.public')

@section('content')
    @include('commerce.messages')

    <a href="{{ route('admin.orders.index') }}"
       class="mb-4 inline-block text-blue-600">
        ← รายการคำสั่งซื้อทั้งหมด
    </a>

    <div class="mb-4 rounded-xl border border-zinc-200 p-4 dark:border-zinc-700">
        <p>
            สมาชิก:
            {{ $order->member?->name }}
            · {{ $order->member?->email }}
        </p>

        <p>
            โทรศัพท์:
            {{ $order->member?->phone ?? '-' }}
        </p>

        <p>
            คะแนนปัจจุบัน:
            {{ number_format($order->member?->points ?? 0) }}

            · ระดับปัจจุบัน:
            {{ $order->member?->tier?->tier_name ?? '-' }}
        </p>
    </div>

    @include('commerce.order-details')

    @if (in_array($order->status, ['pending', 'processing'], true))
        <form method="POST"
              action="{{ route('admin.orders.update', $order) }}"
              class="space-y-4 rounded-xl border border-zinc-200 p-5 dark:border-zinc-700">
            @csrf
            @method('PATCH')

            <label for="status" class="block font-semibold">
                เปลี่ยนสถานะคำสั่งซื้อ
            </label>

            <select
                id="status"
                name="status"
                required
                class="w-full rounded-lg border border-zinc-300 bg-white p-3 dark:border-zinc-600 dark:bg-zinc-800"
            >
                <option value="">เลือกสถานะใหม่</option>

                @if ($order->status === 'pending')
                    <option value="processing">
                        กำลังดำเนินการ
                    </option>
                @endif

                <option value="completed">
                    สำเร็จ — เพิ่มคะแนนและปรับระดับสมาชิก
                </option>

                <option value="cancelled">
                    ยกเลิก — คืนสต็อก
                </option>
            </select>

            <p class="text-sm text-zinc-500">
                เลือกสำเร็จหลังตรวจการชำระเงินและส่งมอบรถแล้ว
                สถานะสำเร็จและยกเลิกถือว่าสิ้นสุด
            </p>

            <button type="submit"
                    class="rounded-lg bg-blue-600 px-5 py-2 text-white">
                บันทึกสถานะ
            </button>
        </form>
    @else
        <p class="rounded-xl border p-4">
            คำสั่งซื้อสิ้นสุดแล้ว ไม่สามารถเปลี่ยนสถานะได้
        </p>
    @endif
@endsection