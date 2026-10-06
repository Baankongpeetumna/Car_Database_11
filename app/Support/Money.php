<?php

namespace App\Support;

use InvalidArgumentException;

final class Money
{
    // จำนวนเงินสูงสุดที่ DECIMAL(15,2) รองรับ หน่วยสตางค์
    public const MAX_CENTS = 999999999999999;

    public static function cents(string $amount): int
    {
        if (!preg_match('/^\d{1,13}(?:\.\d{1,2})?$/', $amount)) {
            throw new InvalidArgumentException('จำนวนเงินไม่ถูกต้อง');
        }

        [$whole, $fraction] = array_pad(
            explode('.', $amount, 2),
            2,
            '0'
        );

        return (int) $whole * 100
            + (int) str_pad($fraction, 2, '0');
    }

    // สำหรับบันทึกฐานข้อมูล
    public static function decimal(int $cents): string
    {
        return intdiv($cents, 100).'.'
            .str_pad((string) ($cents % 100), 2, '0', STR_PAD_LEFT);
    }

    // สำหรับแสดงบนหน้าเว็บ
    public static function display(int $cents): string
    {
        return number_format(intdiv($cents, 100)).'.'
            .str_pad((string) ($cents % 100), 2, '0', STR_PAD_LEFT);
    }

    public static function discount(int $subtotal, string $percent): int
    {
        // 3.00% = 300 / 10000
        $rate = self::cents($percent);

        if ($rate > 10000) {
            throw new InvalidArgumentException(
                'ส่วนลดต้องอยู่ระหว่าง 0 ถึง 100%'
            );
        }

        // ปัดเศษส่วนลดให้เป็นสตางค์
        return intdiv($subtotal, 10000) * $rate
            + intdiv(($subtotal % 10000) * $rate + 5000, 10000);
    }

    public static function points(int $netCents): int
    {
        // 1,000 บาท = 100,000 สตางค์ = 1 คะแนน
        return intdiv($netCents, 100000);
    }
}