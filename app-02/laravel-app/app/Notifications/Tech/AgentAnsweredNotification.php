<?php

namespace App\Notifications\Tech;

use App\Models\AgentCall;
use App\Notifications\SendsPushNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use NotificationChannels\Fcm\FcmMessage;
use Storage;

class AgentAnsweredNotification extends Notification implements ShouldQueue
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
        $subject = $session->subject;
        $ticket  = $session->ticket;
        $user    = $this->agentCall->agent->user;

        $message = FcmMessage::create();
        $message->setData([
            'type'     => 'resource',
            'resource' => json_encode([
                'type' => 'agent_answered',
                'data' => [
                    'user'   => [
                        'id'         => $user->getRouteKey(),
                        'name'       => $user->name ?: ($user->first_name . ' ' . $user->last_name),
                        'experience' => $user->experience_years,
                        'photo'      => !empty($user->photo) ? asset(Storage::url($user->photo)) : null,
                    ],
                    'topic'  => [
                        'id'   => $subject->getRouteKey(),
                        'name' => $subject->name,
                    ],
                    'ticket' => [
                        'id' => $ticket ? $ticket->getRouteKey() : null,
                    ],
                ],
            ]),
        ]);

        return $message;
    }
}
