<?php

namespace App\Support;

class TimeHelper
{
    public static function normalize(?string $time): ?string
    {
        if ($time === null || $time === '') {
            return null;
        }

        if (preg_match('/^(\d{1,2}):(\d{2})(?::(\d{2}))?$/', trim($time), $matches)) {
            return sprintf('%02d:%02d', (int) $matches[1], (int) $matches[2]);
        }

        return $time;
    }

    public static function toMinutes(string $time): int
    {
        $normalized = self::normalize($time) ?: '00:00';
        [$hours, $minutes] = array_map('intval', explode(':', $normalized));

        return ($hours * 60) + $minutes;
    }

    public static function isValidRange(?string $start, ?string $end): bool
    {
        if (!$start || !$end) {
            return false;
        }

        return self::toMinutes($end) > self::toMinutes($start);
    }
}
