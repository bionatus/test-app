<?php

namespace App\Notifications\Agent;

use App\Models\AgentCall;
use App\Notifications\SendsPushNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use NotificationChannels\Fcm\FcmMessage;

class TechEngagedNotification extends Notification implements ShouldQueue
{
    use Queueable;
    use SendsPushNotification;

    private AgentCall $agentCall;

    public function __construct(AgentCall $agentCall)
    {
        $this->agentCall = $agentCall;
    }

    public function toFcm(): FcmMessage
    {

        $session = $this->agentCall->call->communication->session;
        $ticket  = $session->ticket;

        $message = FcmMessage::create();
        $message->setData([
            'type'     => 'resource',
            'resource' => json_encode([
                'type' => 'tech_engaged',
                'data' => [
                    'ticket' => [
                        'id' => $ticket ? $ticket->getRouteKey() : null,
                    ],
                ],
            ]),
        ]);

        return $message;
    }
}
