<?php

namespace App\Constants;

class DeliveryTimeRanges
{
    const RANGE_A        = '06:00 - 09:00';
    const RANGE_B        = '09:00 - 12:00';
    const RANGE_C        = '12:00 - 15:00';
    const RANGE_D        = '15:00 - 18:00';
    const TIME_RANGES    = [
        self::RANGE_A => ['start' => '06:00', 'end' => '09:00'],
        self::RANGE_B => ['start' => '09:00', 'end' => '12:00'],
        self::RANGE_C => ['start' => '12:00', 'end' => '15:00'],
        self::RANGE_D => ['start' => '15:00', 'end' => '18:00'],
    ];
    const TIME_A         = '06:00';
    const TIME_B         = '09:00';
    const TIME_C         = '12:00';
    const TIME_D         = '15:00';
    const TIME_E         = '18:00';
    const ALL_START_TIME = [
        self::TIME_A,
        self::TIME_B,
        self::TIME_C,
        self::TIME_D,
    ];
    const ALL_END_TIME   = [
        self::TIME_B,
        self::TIME_C,
        self::TIME_D,
        self::TIME_E,
    ];
}
