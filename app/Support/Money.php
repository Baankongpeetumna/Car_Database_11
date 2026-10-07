<?php

namespace App\Support;

use InvalidArgumentException;

final class Money
{
    
    public const MAX_CENTS = 999999999999999;

    public static function cents(string $amount): int
    {
        if (!preg_match('/^\d{1,13}(?:\.\d{1,2})?$/', $amount)) {
            throw new InvalidArgumentException('Invalid amount.');
        }

        [$whole, $fraction] = array_pad(
            explode('.', $amount, 2),
            2,
            '0'
        );

        return (int) $whole * 100
            + (int) str_pad($fraction, 2, '0');
    }

    
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
                'Discount must be between 0 and 100%.'
            );
        }

        return intdiv($subtotal, 10000) * $rate
            + intdiv(($subtotal % 10000) * $rate + 5000, 10000);
    }

    public static function points(int $netCents): int
    {

        return intdiv($netCents, 100000);
    }
}