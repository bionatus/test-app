<?php

namespace App\Notifications;

use App\Constants\Notifications;
use App\Constants\PushNotificationEventNames;
use App\Models\InternalNotification;
use App\Models\Post;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use NotificationChannels\Fcm\FcmMessage;
use NotificationChannels\Fcm\Resources\Notification as FcmNotification;

class PostSolvedNotification extends Notification implements ShouldQueue
{
    use Queueable;
    use SendsPushNotification;

    const MESSAGE      = 'A post with your followed tag has been solved.';
    const PUSH_TITLE   = 'Post solved';
    const SETTING_SLUG = Setting::SLUG_FORUM_POST_I_COMMENTED_ON_IS_SOLVED;
    const SOURCE_EVENT = PushNotificationEventNames::SOLVED;
    const SOURCE_TYPE  = Post::MORPH_ALIAS;
    private Post                  $post;
    private ?InternalNotification $internalNotification;

    public function __construct(Post $post)
    {
        $this->post = $post;
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
                'id'                       => $this->post->getRouteKey(),
                'internal_notification_id' => $this->internalNotification->getRouteKey(),
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
                'source_id'    => $this->post->getRouteKey(),
            ]);

            return $internalNotification;
        }

        return null;
    }
}
