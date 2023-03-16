<?php

namespace App\Notifications\User;

use App;
use App\Actions\Models\PubnubChannel\GetPubnubChannel;
use App\Constants\Notifications;
use App\Constants\PushNotificationEventNames;
use App\Models\InternalNotification;
use App\Models\Setting;
use App\Models\Supplier;
use App\Models\User;
use App\Notifications\SendsPushNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;
use NotificationChannels\Fcm\FcmMessage;
use NotificationChannels\Fcm\Resources\Notification as FcmNotification;
use NotificationChannels\Twilio\TwilioChannel;
use NotificationChannels\Twilio\TwilioSmsMessage;

class NewMessagePubnubNotification extends Notification implements ShouldQueue
{
    use Queueable;
    use SendsPushNotification;

    const MESSAGE_LENGTH    = 40;
    const PUSH_SETTING_SLUG = Setting::SLUG_NEW_CHAT_MESSAGE_IN_APP;
    const PUSH_TITLE        = 'New Message from %s';
    const SMS_MESSAGE       = 'Bluon - %s sent you a message: "%s". Do Not Reply to this text.';
    const SMS_SETTING_SLUG  = Setting::SLUG_NEW_CHAT_MESSAGE_SMS;
    const SOURCE_EVENT      = PushNotificationEventNames::NEW_MESSAGE;
    const SOURCE_TYPE       = Supplier::MORPH_ALIAS;
    protected Supplier              $supplier;
    protected User                  $user;
    private string                  $message;
    protected ?InternalNotification $internalNotification;

    public function __construct(Supplier $supplier, User $user, string $message)
    {
        $this->message              = $message;
        $this->supplier             = $supplier;
        $this->user                 = $user;
        $this->internalNotification = $this->createInternalNotification();
        $this->onConnection('database');
    }

    public function via($notifiable)
    {
        $via = [];

        if ($this->user->shouldSendInAppNotification(self::PUSH_SETTING_SLUG)) {
            $via[] = Notifications::VIA_FCM;
        }

        if ($this->user->shouldSendSmsNotification(self::SMS_SETTING_SLUG)) {
            $via[] = TwilioChannel::class;
        }

        return $via;
    }

    public function toFcm(): FcmMessage
    {
        $title         = sprintf(self::PUSH_TITLE, $this->supplier->name);
        $notification  = FcmNotification::create()->setTitle($title)->setBody($this->message);
        $pubnubChannel = App::make(GetPubnubChannel::class, [
            'supplier' => $this->supplier,
            'user'     => $this->user,
        ])->execute();
        $message       = $this->message($notification);

        $message->setData([
            'source' => json_encode([
                'event'                    => self::SOURCE_EVENT,
                'type'                     => self::SOURCE_TYPE,
                'id'                       => $this->supplier->getRouteKey(),
                'internal_notification_id' => $this->internalNotification->getRouteKey(),
                'channel_id'               => $pubnubChannel->getRouteKey(),
                'supplier_data'            => ['id' => $this->supplier->getRouteKey()],
            ]),
        ]);

        return $message;
    }

    public function toTwilio($notifiable)
    {
        $message    = Str::limit($this->message, self::MESSAGE_LENGTH);
        $smsMessage = sprintf(self::SMS_MESSAGE, $this->supplier->name, $message);

        return (new TwilioSmsMessage())->content($smsMessage);
    }

    private function createInternalNotification(): ?InternalNotification
    {
        if (!$this->user->disabled_at) {
            /** @var InternalNotification $internalNotification */
            $internalNotification = $this->user->internalNotifications()->create([
                'message'      => $this->message,
                'source_event' => self::SOURCE_EVENT,
                'source_type'  => self::SOURCE_TYPE,
                'source_id'    => $this->supplier->getRouteKey(),
            ]);

            return $internalNotification;
        }

        return null;
    }
}
