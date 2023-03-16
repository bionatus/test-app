<?php

namespace App\Notifications\Agent;

use App\Models\Session;
use App\Models\Subject;
use App\Notifications\SendsPushNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use NotificationChannels\Fcm\FcmMessage;

class TechCallingNotification extends Notification implements ShouldQueue
{
    use Queueable;
    use SendsPushNotification;

    private Session $session;

    public function __construct(Session $session)
    {
        $this->session = $session;
    }

    public function toFcm(): FcmMessage
    {

        $subject = $this->session->subject;
        $user    = $this->session->user;

        $message = FcmMessage::create();
        $message->setData([
            'type'     => 'resource',
            'resource' => json_encode([
                'type' => 'tech_calling',
                'data' => [
                    'user'  => [
                        'id'    => $user->getRouteKey(),
                        'name'  => $user->fullName(),
                        'photo' => $user->photoUrl(),
                    ],
                    'topic' => [
                        'id'   => $subject->getRouteKey(),
                        'name' => $this->displayableSubjectName($subject),
                    ],
                ],
            ]),
        ]);

        return $message;
    }

    private function displayableSubjectName(Subject $subject): string
    {
        return $subject->isTopic() ? $subject->name : ($subject->subtopic->topic->subject->name . '/' . $subject->name);
    }
}
