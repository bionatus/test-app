<?php

namespace App\Notifications;

use App\Constants\Notifications;
use App\Constants\PushNotificationEventNames;
use App\Models\Comment;
use App\Models\InternalNotification;
use App\Models\Setting;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use NotificationChannels\Fcm\FcmMessage;
use NotificationChannels\Fcm\Resources\Notification as FcmNotification;

class SolutionCreatedNotification extends Notification implements ShouldQueue
{
    use Queueable;
    use SendsPushNotification;

    const MESSAGE      = 'Your response was badass and marked the “Best Answer”.';
    const PUSH_TITLE   = 'Post solved';
    const SETTING_SLUG = Setting::SLUG_FORUM_POST_I_COMMENTED_ON_IS_SOLVED;
    const SOURCE_EVENT = PushNotificationEventNames::SELECTED;
    const SOURCE_TYPE  = Comment::MORPH_ALIAS;
    private Comment               $solution;
    private ?InternalNotification $internalNotification;

    public function __construct(Comment $solution)
    {
        $this->solution             = $solution;
        $this->internalNotification = $this->createInternalNotification();
        $this->onConnection('database');
    }

    public function via($notifiable)
    {
        $user = $this->solution->user;

        if ($user->shouldSendForumNotifications(self::SETTING_SLUG)) {
            return [Notifications::VIA_FCM];
        }

        return [];
    }

    public function toFcm(): FcmMessage
    {
        $notification = FcmNotification::create()->setTitle(self::PUSH_TITLE)->setBody(self::MESSAGE);
        $message      = $this->message($notification);

        $message->setData([
            'source' => json_encode([
                'event'                    => self::SOURCE_EVENT,
                'type'                     => self::SOURCE_TYPE,
                'id'                       => $this->solution->getRouteKey(),
                'post_id'                  => $this->solution->post->getRouteKey(),
                'internal_notification_id' => $this->internalNotification->getRouteKey(),
            ]),
        ]);

        return $message;
    }

    private function createInternalNotification(): ?InternalNotification
    {
        $user = $this->solution->user;

        if (!$user->disabled_at) {
            /** @var InternalNotification $internalNotification */
            $internalNotification = $user->internalNotifications()->create([
                'message'      => self::MESSAGE,
                'source_event' => self::SOURCE_EVENT,
                'source_type'  => self::SOURCE_TYPE,
                'source_id'    => $this->solution->post->getRouteKey(),
            ]);

            return $internalNotification;
        }

        return null;
    }
}
