<?php

namespace App\Rules\Agent;

use App\Models\Agent;
use App\Models\Scopes\ByKey;
use Illuminate\Contracts\Validation\Rule;
use Lang;
use Str;

class Exists implements Rule
{
    public function passes($attribute, $value)
    {
        $agentId = Str::substr($value, strlen('client:'), strlen($value));

        return !!Agent::scoped(new ByKey($agentId))->count();
    }

    public function message()
    {
        return Lang::get('validation.exists');
    }
}
