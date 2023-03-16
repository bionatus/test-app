<?php

namespace App\Notifications\Tech;

use App\Constants\PushNotificationEventNames;
use App\Models\AgentCall;
use App\Models\Ticket;
use App\Notifications\SendsPushNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use NotificationChannels\Fcm\FcmMessage;
use NotificationChannels\Fcm\Resources;

class UnsolvedTicketNotification extends Notification implements ShouldQueue
{
    use Queueable;
    use SendsPushNotification;

    const SOURCE_EVENT = PushNotificationEventNames::UNSOLVED;
    const SOURCE_TYPE  = Ticket::MORPH_ALIAS;
    private Ticket    $ticket;
    private AgentCall $agentCall;

    public function __construct(AgentCall $agentCall, Ticket $ticket)
    {
        $this->ticket    = $ticket;
        $this->agentCall = $agentCall;
    }

    public function toFcm(): FcmMessage
    {
        $ticketId = $this->ticket->getRouteKey();

        $name         = $this->agentCall->agent->user->first_name;
        $title        = "Did {$name} solve your problem?";
        $body         = "Tap here to leave feedback about your experience with {$name}!";
        $notification = Resources\Notification::create()->setTitle($title)->setBody($body);

        $message = $this->message($notification);
        $message->setData([
            'type'   => 'source',
            'source' => json_encode([
                'event' => self::SOURCE_EVENT,
                'type'  => self::SOURCE_TYPE,
                'id'    => $ticketId,
            ]),
        ]);

        return $message;
    }
}
