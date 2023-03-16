<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Collection;
use Lang;

class UniqueString implements Rule
{
    private Collection $alreadyUsedValues;

    public function __construct()
    {
        $this->alreadyUsedValues = Collection::make();
    }

    public function passes($attribute, $value): bool
    {
        if (is_object($value) || is_array($value)) {
            return false;
        }

        $validated = strtolower(trim($value));
        if ($this->alreadyUsedValues->has($validated)) {
            return false;
        }

        $this->alreadyUsedValues->put($validated, $validated);

        return true;
    }

    public function message(): string
    {
        return Lang::get('validation.unique');
    }
}
