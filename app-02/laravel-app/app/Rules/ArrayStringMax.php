<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class ArrayStringMax implements Rule
{
    private int $max;

    public function __construct(int $max)
    {
        $this->max = $max;
    }

    public function passes($attribute, $value)
    {
        $array = explode(',', $value);

        return count($array) <= $this->max;
    }

    public function message()
    {
        return "The :attribute may not have more than {$this->max} items.";
    }
}
