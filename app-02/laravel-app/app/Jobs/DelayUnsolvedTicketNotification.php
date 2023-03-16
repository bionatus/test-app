<?php

namespace App\Jobs;

use App\Models\AgentCall;
use App\Notifications\Tech\UnsolvedTicketNotification;
use Config;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class DelayUnsolvedTicketNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private AgentCall $agentCall;

    public function __construct(AgentCall $agentCall)
    {
        $this->onConnection('database');
        $this->delay(Config::get('communications.tickets.notifications.open'));
        $this->agentCall = $agentCall;
    }

    public function handle()
    {
        $session = $this->agentCall->call->communication->session;
        $ticket  = $session->ticket;
        if ($ticket && $ticket->isOpen()) {
            $session->user->notify(new UnsolvedTicketNotification($this->agentCall, $ticket));
        }
    }
}
