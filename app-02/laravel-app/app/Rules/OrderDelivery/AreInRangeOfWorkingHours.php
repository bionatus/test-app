<?php

namespace App\Rules\OrderDelivery;

use App;
use Illuminate\Contracts\Validation\Rule;

class AreInRangeOfWorkingHours implements Rule
{
    private array  $validRanges;
    private string $startTime;

    public function __construct(array $validRanges, string $startTime)
    {
        $this->validRanges = $validRanges;
        $this->startTime   = $startTime;
    }

    public function passes($attribute, $value): bool
    {
        $range = ['start' => $this->startTime, 'end' => $value];

        return in_array($range, $this->validRanges);
    }

    public function message(): string
    {
        return "This range of hours is not enabled.";
    }
}
