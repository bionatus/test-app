<?php

namespace App\Rules\OrderDelivery;

use App\Constants\DeliveryTimeRanges;
use Illuminate\Contracts\Validation\Rule;

class ValidEndTime implements Rule
{
    private ?string $startTime;

    public function __construct(?string $startTime)
    {
        $this->startTime = $startTime;
    }

    public function passes($attribute, $value): bool
    {
        if (!in_array($value, DeliveryTimeRanges::ALL_END_TIME)) {
            return false;
        }
        $range = ['start' => $this->startTime, 'end' => $value];

        if (!in_array($range, DeliveryTimeRanges::TIME_RANGES)) {
            return false;
        }

        return true;
    }

    public function message(): string
    {
        return "This range is not enabled.";
    }
}
