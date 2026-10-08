<?php

namespace App\Support;

use App\Models\Car;

/**
 * ตัวช่วยแสดงผลรถบนหน้าเว็บ (สี, ทรงรถ, ป้ายสภาพรถ New/Used)
 * ไม่ได้แก้ข้อมูลในฐานข้อมูล ใช้เพื่อการแสดงผลเท่านั้น
 */
final class CarVisual
{
    private const COLORS = [
        'white' => '#F3F4F6', 'ขาว' => '#F3F4F6', 'pearl' => '#F3F4F6', 'มุก' => '#F3F4F6',
        'silver' => '#C5CAD1', 'เงิน' => '#C5CAD1',
        'grey' => '#7C838C', 'gray' => '#7C838C', 'เทา' => '#7C838C',
        'black' => '#24272C', 'ดำ' => '#24272C',
        'red' => '#D7322E', 'แดง' => '#D7322E',
        'blue' => '#2D5BBE', 'น้ำเงิน' => '#2D5BBE', 'ฟ้า' => '#4C8FD9',
        'orange' => '#EE7A22', 'ส้ม' => '#EE7A22',
        'yellow' => '#F2C230', 'เหลือง' => '#F2C230',
        'green' => '#2F8F5B', 'เขียว' => '#2F8F5B',
        'brown' => '#7A5236', 'น้ำตาล' => '#7A5236',
    ];

    /** ค่าที่ใช้กรองสภาพรถ: new = รถใหม่, used = มือสอง */
    public const NEW_VALUES = ['new', 'ใหม่', 'รถใหม่', 'brand new'];

    public static function colorHex(?string $color): string
    {
        $key = mb_strtolower(trim((string) $color));

        foreach (self::COLORS as $name => $hex) {
            if ($key !== '' && str_contains($key, $name)) {
                return $hex;
            }
        }

        return '#9AA0A6';
    }

    /** ทรงรถสำหรับภาพประกอบ: sedan, suv, pickup, hatchback, coupe, van */
    public static function bodyType(?string $category): string
    {
        $key = mb_strtolower((string) $category);

        return match (true) {
            str_contains($key, 'suv'), str_contains($key, 'crossover') => 'suv',
            str_contains($key, 'pickup'), str_contains($key, 'กระบะ') => 'pickup',
            str_contains($key, 'hatch') => 'hatchback',
            str_contains($key, 'coupe'), str_contains($key, 'sport') => 'coupe',
            str_contains($key, 'van'), str_contains($key, 'ตู้') => 'van',
            default => 'sedan',
        };
    }

    public static function isNew(?string $condition): bool
    {
        return in_array(mb_strtolower(trim((string) $condition)), self::NEW_VALUES, true);
    }

    public static function conditionLabel(?string $condition): string
    {
        if (self::isNew($condition)) {
            return 'New';
        }

        return match (mb_strtolower(trim((string) $condition))) {
            'used', 'มือสอง', 'รถมือสอง' => 'Used',
            '' => '-',
            default => (string) $condition,
        };
    }

    /** URL รูปรถ หรือ null ถ้าไม่มีรูป (รองรับทั้งลิงก์เต็มและไฟล์ใน storage) */
    public static function imageSrc(Car $car): ?string
    {
        $url = trim((string) $car->image_url);

        if ($url === '') {
            return null;
        }

        if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://') || str_starts_with($url, '/')) {
            return $url;
        }

        return asset('storage/'.ltrim($url, '/'));
    }

    public static function baht(int|float|string|null $amount): string
    {
        return '฿'.number_format((float) $amount);
    }
}
