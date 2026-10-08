<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * 1on1 面談予約 (Meeting) の状態を表す Enum。
 */
enum MeetingReminderType: string
{
    case Eve = 'eve';
    case OneHourBefore = 'one_hour_before';

    public function label(): string
    {
        return match ($this) {
            self::Eve => '前日',
            self::OneHourBefore => '1時間前',
        };
    }
}
