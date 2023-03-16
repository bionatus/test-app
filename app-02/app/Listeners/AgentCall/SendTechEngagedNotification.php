<?php

namespace App\Listeners\AgentCall;

use App\Events\AgentCall\Answered;
use App\Notifications\Agent\TechEngagedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendTechEngagedNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(Answered $event)
    {
        $agentCall = $event->agentCall();

        $agentCall->agent->notify(new TechEngagedNotification($agentCall));
    }
}
