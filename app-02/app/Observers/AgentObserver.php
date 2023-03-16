<?php

namespace App\Observers;

use App\Models\Agent;
use Illuminate\Support\Str;

class AgentObserver
{
    public function creating(Agent $agent): void
    {
        $agent->uuid = Str::uuid();
    }
}
