<?php

namespace App\Listeners\AgentCall;

use App\Events\AgentCall\Answered;
use App\Notifications\Tech\AgentAnsweredNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendAgentAnsweredNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(Answered $event)
    {
        $agentCall = $event->agentCall();

        $agentCall->call->communication->session->user->notify(new AgentAnsweredNotification($agentCall));
    }
}
