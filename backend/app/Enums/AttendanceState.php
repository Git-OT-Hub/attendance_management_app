<?php

namespace App\Enums;

enum AttendanceState: int
{
    case WORK = 1;
    case BREAK = 2;
    case FINISHED = 3;

    /**
     * 勤怠状態の日本語化
     * @return string
     */
    public function label(): string
    {
        return match($this)
        {
            self::WORK => '出勤中',
            self::BREAK => '休憩中',
            self::FINISHED => '退勤済',
        };
    }
}