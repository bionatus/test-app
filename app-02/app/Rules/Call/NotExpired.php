<?php

namespace App\Rules\Call;

use Config;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Carbon;

class NotExpired implements Rule
{
    private Exists $callExists;

    public function __construct(Exists $callExists)
    {
        $this->callExists = $callExists;
    }

    public function passes($attribute, $value)
    {
        $call = $this->callExists->call();
        if (!$call->exists) {
            return false;
        }

        $maxTechWaitingTime = (int) Config::get('communications.calls.max_user_waiting_time');

        return $maxTechWaitingTime > $call->created_at->diffInSeconds(Carbon::now());
    }

    public function message()
    {
        return 'The :attribute is expired';
    }
}
