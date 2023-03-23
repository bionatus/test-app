<?php

namespace App\Listeners\AgentCall;

use App\Events\AgentCall\Ringing;
use App\Notifications\Agent\TechCallingNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendTechCallingNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(Ringing $event)
    {
        $agentCall = $event->agentCall();

        $agentCall->agent->notify(new TechCallingNotification($agentCall->call->communication->session));
    }
}
