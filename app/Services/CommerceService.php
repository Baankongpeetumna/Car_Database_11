<?php

namespace App\Services;

use App\Models\MembershipTier;
use App\Models\Order;
use App\Models\User;
use App\Support\Money;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CommerceService
{
    // อ่านตะกร้าและคำนวณยอด
    // ถ้า $lock เป็น true ต้องเรียกภายใน transaction ที่ล็อก MEMBER แล้ว
    public function quote(User $member, bool $lock = false): array
    {
        $cartQuery = $member->cart();

        if ($lock) {
            $cartQuery->lockForUpdate();
        }

        $cart = $cartQuery->first();
        $cars = collect();

        if ($cart) {
            $query = $cart->cars()
                ->with('brand')
                ->orderBy('CAR.car_id');

            if ($lock) {
                $query->lockForUpdate();
            }

            $cars = $query->get();
        }

        $tierQuery = $member->tier();

        if ($lock) {
            $tierQuery->lockForUpdate();
        }

        $tier = $tierQuery->first();

        if (!$tier) {
            $this->fail('ไม่พบระดับสมาชิก กรุณาติดต่อผู้ดูแลระบบ');
        }

        $subtotal = 0;
        $items = [];

        foreach ($cars as $car) {
            $quantity = (int) $car->pivot->quantity;

            if ($quantity < 1) {
                $this->fail('จำนวนรถไม่ถูกต้อง กรุณาแก้ไขตะกร้า');
            }

            $price = Money::cents($car->price);

            if (
                $price > 0
                && $quantity > intdiv(Money::MAX_CENTS - $subtotal, $price)
            ) {
                $this->fail('ยอดคำสั่งซื้อเกินขนาดที่ระบบรองรับ');
            }

            $lineTotal = $price * $quantity;
            $subtotal += $lineTotal;

            $items[] = [
                'car' => $car,
                'quantity' => $quantity,
                'price_cents' => $price,
                'line_cents' => $lineTotal,
            ];
        }

        $rate = Money::cents($tier->discount_percent);

        if ($rate > 10000) {
            $this->fail('ส่วนลดระดับสมาชิกต้องอยู่ระหว่าง 0 ถึง 100%');
        }

        $discount = Money::discount(
            $subtotal,
            $tier->discount_percent
        );

        // ตรวจว่ารถ จำนวน ราคา และส่วนลดตรงกับที่ลูกค้าเห็นหรือไม่
        $fingerprint = hash('sha256', json_encode([
            $member->getKey(),
            $tier->getKey(),
            $tier->discount_percent,
            array_map(
                fn ($item) => [
                    $item['car']->car_id,
                    $item['quantity'],
                    $item['price_cents'],
                ],
                $items
            ),
        ], JSON_THROW_ON_ERROR));

        return [
            'cart' => $cart,
            'tier' => $tier,
            'items' => $items,
            'subtotal' => $subtotal,
            'discount' => $discount,
            'total' => $subtotal - $discount,
            'fingerprint' => $fingerprint,
        ];
    }

    // สร้างคำสั่งซื้อ หักสต็อก และล้างตะกร้า
    public function placeOrder(
        User $member,
        array $input,
        array $token
    ): Order {
        return DB::transaction(function () use ($member, $input, $token) {
            // ป้องกันคำขอของสมาชิกคนเดียวกันทำงานชนกัน
            $member = User::whereKey($member->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            // หากส่งคำขอเดิมซ้ำ ให้คืนคำสั่งซื้อเดิม
            $existing = $member->orders()
                ->where('checkout_token', $token['id'])
                ->first();

            if ($existing) {
                return $existing;
            }

            $quote = $this->quote($member, true);

            if ($quote['items'] === []) {
                $this->fail('ตะกร้าว่าง กรุณาเลือกรถก่อนยืนยันซื้อ');
            }

            if (
                !hash_equals(
                    $token['fingerprint'],
                    $quote['fingerprint']
                )
            ) {
                $this->fail(
                    'รถ จำนวน ราคา หรือส่วนลดเปลี่ยนไป '
                    .'กรุณาเปิดหน้า Checkout ใหม่เพื่อตรวจยอด'
                );
            }

            // ตรวจสต็อกทุกคันก่อนบันทึกคำสั่งซื้อ
            foreach ($quote['items'] as $item) {
                $car = $item['car'];

                if ($item['quantity'] > (int) $car->stock_qty) {
                    $this->fail(
                        "{$car->model_name} เหลือ {$car->stock_qty} คัน "
                        .'กรุณาแก้ไขตะกร้า'
                    );
                }
            }

            $order = new Order();

            $order->fill([
                'member_id' => $member->getKey(),
                'status' => 'pending',
                'payment_method' => $input['payment_method'],
                'shipping_address' => $input['shipping_address'],
                'subtotal' => Money::decimal($quote['subtotal']),
                'discount_amount' => Money::decimal($quote['discount']),
                'total_amount' => Money::decimal($quote['total']),
                'points_earned' => 0,
            ]);

            // กำหนดจาก Server โดยตรง ไม่รับรหัสนี้จากการแก้ Model ของผู้ใช้
            $order->checkout_token = $token['id'];
            $order->save();

            foreach ($quote['items'] as $item) {
                $car = $item['car'];

                // เก็บราคาตอนซื้อ
                $order->cars()->attach($car->car_id, [
                    'quantity' => $item['quantity'],
                    'unit_price' => Money::decimal($item['price_cents']),
                ]);

                // หักสต็อก
                $car->stock_qty = (int) $car->stock_qty
                    - $item['quantity'];

                $car->save();
            }

            // ล้างเฉพาะรายการ เก็บตะกร้าใบเดิมไว้
            $quote['cart']->cars()->detach();

            return $order;
        }, 3);
    }

    // Admin เปลี่ยนสถานะคำสั่งซื้อ
    public function changeStatus(Order $order, string $target): Order
    {
        return DB::transaction(function () use ($order, $target) {
            // ล็อก MEMBER ก่อนเหมือนขั้นตอน Checkout
            $member = User::whereKey($order->member_id)
                ->lockForUpdate()
                ->firstOrFail();

            $order = Order::whereKey($order->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            // ส่งสถานะเดิมซ้ำ ไม่เพิ่มคะแนนหรือคืนสต็อกซ้ำ
            if ($order->status === $target) {
                return $order;
            }

            $allowed = [
                'pending' => ['processing', 'completed', 'cancelled'],
                'processing' => ['completed', 'cancelled'],
                'completed' => [],
                'cancelled' => [],
            ];

            if (
                !in_array(
                    $target,
                    $allowed[$order->status] ?? [],
                    true
                )
            ) {
                $this->fail(
                    'เปลี่ยนสถานะนี้ไม่ได้ '
                    .'คำสั่งซื้อที่สำเร็จหรือยกเลิกแล้วถือว่าสิ้นสุด'
                );
            }

            // ยกเลิกก่อนสำเร็จ: คืนสต็อก
            if ($target === 'cancelled') {
                $cars = $order->cars()
                    ->orderBy('CAR.car_id')
                    ->lockForUpdate()
                    ->get();

                foreach ($cars as $car) {
                    $newStock = (int) $car->stock_qty
                        + (int) $car->pivot->quantity;

                    if ($newStock > 4294967295) {
                        $this->fail('จำนวนสต็อกเกินขนาดที่ระบบรองรับ');
                    }

                    $car->stock_qty = $newStock;
                    $car->save();
                }
            }

            // สำเร็จ: ให้คะแนนและปรับ Tier
            if ($target === 'completed') {
                $earned = Money::points(
                    Money::cents($order->total_amount)
                );

                $newPoints = (int) $member->points + $earned;

                if ($newPoints > 4294967295) {
                    $this->fail('คะแนนสะสมเกินขนาดที่ระบบรองรับ');
                }

                $tier = MembershipTier::where(
                    'min_points',
                    '<=',
                    $newPoints
                )
                    ->orderByDesc('min_points')
                    ->orderBy('tier_id')
                    ->lockForUpdate()
                    ->first();

                if (!$tier) {
                    $this->fail('ไม่พบระดับสมาชิกสำหรับคะแนนนี้');
                }

                $member->forceFill([
                    'points' => $newPoints,
                    'tier_id' => $tier->tier_id,
                ])->save();

                $order->points_earned = $earned;
            }

            $order->status = $target;
            $order->save();

            return $order;
        }, 3);
    }

    private function fail(string $message): never
    {
        throw ValidationException::withMessages([
            'commerce' => $message,
        ]);
    }
}