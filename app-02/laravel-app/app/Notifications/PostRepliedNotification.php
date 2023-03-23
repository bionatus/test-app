<?php

namespace App\Notifications;

use App\Constants\Notifications;
use App\Constants\PushNotificationEventNames;
use App\Models\InternalNotification;
use App\Models\Post;
use App\Models\Setting;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use NotificationChannels\Fcm\FcmMessage;
use NotificationChannels\Fcm\Resources\Notification as FcmNotification;

class PostRepliedNotification extends Notification implements ShouldQueue
{
    use Queueable;
    use SendsPushNotification;

    const INTERNAL_MESSAGE = 'A new comment has been created for your post.';
    const PUSH_BODY        = 'Your post has a new comment.';
    const PUSH_TITLE       = 'Post replied';
    const SETTING_SLUG     = Setting::SLUG_FORUM_SOMEONE_COMMENTS_ON_MY_POST;
    const SOURCE_EVENT     = PushNotificationEventNames::REPLIED;
    const SOURCE_TYPE      = Post::MORPH_ALIAS;
    private Post                  $post;
    private ?InternalNotification $internalNotification;

    public function __construct(Post $post)
    {
        $this->post                 = $post;
        $this->internalNotification = $this->createInternalNotification();
        $this->onConnection('database');
    }

    public function via($notifiable)
    {
        $user = $this->post->user;

        if ($user->shouldSendForumNotifications(self::SETTING_SLUG)) {
            return [Notifications::VIA_FCM];
        }

        return [];
    }

    public function toFcm(): FcmMessage
    {
        $notification = FcmNotification::create()->setTitle(self::PUSH_TITLE)->setBody(self::PUSH_BODY);
        $message      = $this->message($notification);

        $message->setData([
            'source' => json_encode([
                'event'                    => self::SOURCE_EVENT,
                'type'                     => self::SOURCE_TYPE,
                'id'                       => $this->post->getRouteKey(),
                'internal_notification_id' => $this->internalNotification->getRouteKey(),
            ]),
        ]);

        return $message;
    }

    private function createInternalNotification(): ?InternalNotification
    {
        $user = $this->post->user;

        if (!$user->disabled_at) {
            /** @var InternalNotification $internalNotification */
            $internalNotification = $user->internalNotifications()->create([
                'message'      => self::INTERNAL_MESSAGE,
                'source_event' => self::SOURCE_EVENT,
                'source_type'  => self::SOURCE_TYPE,
                'source_id'    => $this->post->getRouteKey(),
            ]);

            return $internalNotification;
        }

        return null;
    }
}
