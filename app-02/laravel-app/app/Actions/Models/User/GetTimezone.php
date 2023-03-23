<?php

namespace App\Actions\Models\User;

use App\Models\StateTimezone;
use App\Models\User;
use App\Models\ZipTimezone;

class GetTimezone
{
    private User $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function execute(): ?string
    {
        $state = $this->user->state;
        $zip   = $this->user->zip;

        if (!$country = $this->user->country) {
            return null;
        }

        if ($state && $stateTimezone = StateTimezone::where(['country' => $country, 'state' => $state])->first()) {
            return $stateTimezone->timezone;
        }

        if ($zip && $zipTimezone = ZipTimezone::where(['country' => $country, 'zip' => $zip])->first()) {
            return $zipTimezone->timezone;
        }

        return null;
    }
}
