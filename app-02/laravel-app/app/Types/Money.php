<?php

namespace App\Types;

class Money
{
    const BASE_10  = 10;
    const DECIMALS = 2;

    static public function toDollars(?int $value)
    {
        return $value / (self::BASE_10 ** self::DECIMALS);
    }

    static public function toCents(?float $value): int
    {
        return (int) round(round($value, 2) * (self::BASE_10 ** self::DECIMALS));
    }

    static public function formatDollars(float $value): string
    {
        return number_format($value, self::DECIMALS, '.', '');
    }
}
