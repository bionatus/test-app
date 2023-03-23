<?php

namespace App\Rules\OrderDelivery;

use App;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Carbon;

class Is30MinutesBefore implements Rule
{
    private array    $workingHours;

    public function __construct(array $workingHours)
    {
        $this->workingHours = $workingHours;
    }

    public function passes($attribute, $value): bool
    {
        if (!in_array($value, array_keys($this->workingHours['end_time']))) {
            return false;
        }
        $endDate = $this->workingHours['end_time'][$value];

        return !Carbon::now()->addMinutes(30)->gte($endDate);
    }

    public function message(): string
    {
        return "The end of the time range is less than 30 minutes from now.";
    }
}
