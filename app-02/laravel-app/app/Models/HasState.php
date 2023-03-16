<?php

namespace App\Models;

use Str;

trait HasState
{
    public function getStateShortCode()
    {
        if (is_null($this->state)) {
            return null;
        }

        return Str::substr($this->state, 3);
    }
}

