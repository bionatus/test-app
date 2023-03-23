<?php

namespace App\Rules\OrderDelivery;

use App\Constants\DeliveryTimeRanges;
use Exception;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Carbon;

class ValidDateTime implements Rule
{
    private int     $minutes;
    private ?string $date;
    private ?string $timezone;

    public function __construct(?string $date, ?string $timezone, int $minutes = 30)
    {
        $this->date     = $date;
        $this->minutes  = $minutes;
        $this->timezone = $timezone;
    }

    public function passes($attribute, $value): bool
    {
        if (!in_array($value, DeliveryTimeRanges::ALL_END_TIME)) {
            return false;
        }
        try {
            $formattedTime     = $value;
            $formattedDateTime = Carbon::createFromFormat('Y-m-d H:i', "$this->date $formattedTime", $this->timezone);
        } catch (Exception $exception) {
            return false;
        }

        if (Carbon::now()->addMinutes($this->minutes)->gte($formattedDateTime)) {
            return false;
        }

        return true;
    }

    public function message(): string
    {
        $datetime = Carbon::now($this->timezone)->addMinutes($this->minutes)->format('Y-m-d h:iA');

        return "The datetime should be after $datetime.";
    }
}
