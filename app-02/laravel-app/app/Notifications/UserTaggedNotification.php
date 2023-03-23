<?php

namespace App\Notifications;

use App\Constants\Notifications;
use App\Constants\PushNotificationEventNames;
use App\Models\Comment;
use App\Models\InternalNotification;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use NotificationChannels\Fcm\FcmMessage;
use NotificationChannels\Fcm\Resources\Notification as FcmNotification;

class UserTaggedNotification extends Notification implements ShouldQueue
{
    use Queueable;
    use SendsPushNotification;

    const MESSAGE      = "%s tagged you in a comment. Tap here to view!";
    const PUSH_TITLE   = 'Tagged in a Comment';
    const SETTING_SLUG = Setting::SLUG_FORUM_SOMEONE_TAGS_YOU_IN_A_COMMENT;
    const SOURCE_EVENT = PushNotificationEventNames::USER_TAGGED;
    const SOURCE_TYPE  = Comment::MORPH_ALIAS;
    private Comment               $comment;
    private ?InternalNotification $internalNotification;

    public function __construct(Comment $comment)
    {
        $this->comment = $comment;
        $this->onConnection('database');
    }

    public function via($notifiable)
    {
        $user                       = $notifiable;
        $this->internalNotification = $this->createInternalNotification($user);

        if ($user->shouldSendForumNotifications(self::SETTING_SLUG)) {
            return [Notifications::VIA_FCM];
        }

        return [];
    }

    public function toFcm(): FcmMessage
    {
        $bodyMessage  = sprintf(self::MESSAGE, $this->comment->user->fullName());
        $notification = FcmNotification::create()->setTitle(self::PUSH_TITLE)->setBody($bodyMessage);
        $message      = $this->message($notification);

        $message->setData([
            'source' => json_encode([
                'event'                    => self::SOURCE_EVENT,
                'type'                     => self::SOURCE_TYPE,
                'id'                       => $this->comment->getRouteKey(),
                'internal_notification_id' => $this->internalNotification->getRouteKey(),
                'data'                     => ['post_id' => $this->comment->post->getRouteKey()],
            ]),
        ]);

        return $message;
    }

    private function createInternalNotification(User $user): ?InternalNotification
    {
        if (!$user->disabled_at) {
            $bodyMessage = sprintf(self::MESSAGE, $this->comment->user->fullName());
            /** @var InternalNotification $internalNotification */
            $internalNotification = $user->internalNotifications()->create([
                'message'      => $bodyMessage,
                'source_event' => self::SOURCE_EVENT,
                'source_type'  => self::SOURCE_TYPE,
                'source_id'    => $this->comment->getRouteKey(),
                'data'         => ['post_id' => $this->comment->post->getRouteKey()],
            ]);

            return $internalNotification;
        }

        return null;
    }
}
