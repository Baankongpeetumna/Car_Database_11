<?php

namespace App\Support;

use App\Models\MembershipTier;

final class TierVisual
{
    public static function style(?MembershipTier $tier): string
    {
        $color = $tier?->color_hex ?? '#71717A';

        $channels = [
            hexdec(substr($color, 1, 2)),
            hexdec(substr($color, 3, 2)),
            hexdec(substr($color, 5, 2)),
        ];

        $linear = array_map(function (int $channel): float {
            $value = $channel / 255;

            return $value <= 0.04045
                ? $value / 12.92
                : (($value + 0.055) / 1.055) ** 2.4;
        }, $channels);

        $luminance = 0.2126 * $linear[0]
            + 0.7152 * $linear[1]
            + 0.0722 * $linear[2];

        $ink = $luminance > 0.179 ? '#18181B' : '#FFFFFF';

        return "--tier-color: {$color}; --tier-ink: {$ink};";
    }

    public static function theme(): array
    {
        return [
            'bar' => 'tier-bar',
            'text' => 'tier-text',
            'tint' => 'tier-tint',
            'pill' => 'tier-badge',
            'avatar' => 'tier-avatar',
            'badge' => 'tier-badge',
            'dot' => 'tier-dot',
            'on' => 'tier-on',
            'stat' => 'tier-stat',
            'sel' => 'tier-choice',
        ];
    }
}