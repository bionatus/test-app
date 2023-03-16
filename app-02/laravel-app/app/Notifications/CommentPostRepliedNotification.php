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

class CommentPostRepliedNotification extends Notification implements ShouldQueue
{
    use Queueable;
    use SendsPushNotification;

    const MESSAGE      = 'There’s a new comment on a post you’ve commented on. Tap here to view!';
    const PUSH_TITLE   = 'New comment';
    const SETTING_SLUG = Setting::SLUG_FORUM_NEW_COMMENTS_ON_A_POST;
    const SOURCE_EVENT = PushNotificationEventNames::CREATED;
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
        $notification = FcmNotification::create()->setTitle(self::PUSH_TITLE)->setBody(self::MESSAGE);
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
            /** @var InternalNotification $internalNotification */
            $internalNotification = $user->internalNotifications()->create([
                'message'      => self::MESSAGE,
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
