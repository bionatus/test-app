<?php

namespace App\Rules\OrderDelivery;

use App;
use Illuminate\Contracts\Validation\Rule;

class IsInStartWorkingHours implements Rule
{
    private array $workingHours;

    public function __construct(array $workingHours)
    {
        $this->workingHours = $workingHours;
    }

    public function passes($attribute, $value): bool
    {
        return in_array($value, $this->workingHours['start_time']);
    }

    public function message(): string
    {
        return "The selected requested start time is invalid.";
    }
}

