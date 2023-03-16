<?php

namespace App\Rules\Call;

use App\Models\Call;
use App\Models\Communication;
use App\Models\Communication\Scopes\ByProvider;
use Illuminate\Contracts\Validation\Rule;
use Lang;

class Exists implements Rule
{
    private string $provider;
    private Call   $call;

    public function __construct(string $provider)
    {
        $this->provider = $provider;
        $this->call     = new Call();
    }

    public function passes($attribute, $value)
    {
        if (!($communication = Communication::scoped(new ByProvider($this->provider, $value))->first())) {
            return false;
        }

        if ($isCall = $communication->isCall()) {
            $this->call = $communication->call;
        }

        return $isCall;
    }

    public function message()
    {
        return Lang::get('validation.exists');
    }

    public function call(): Call
    {
        return $this->call;
    }
}
