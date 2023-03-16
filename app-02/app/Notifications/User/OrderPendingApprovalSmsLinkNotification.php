<?php

namespace App\Notifications\User;

use App;
use App\Models\Order;
use App\Models\Setting;
use Config;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use NotificationChannels\Twilio\TwilioChannel;
use NotificationChannels\Twilio\TwilioSmsMessage;
use Str;

class OrderPendingApprovalSmsLinkNotification extends Notification implements ShouldQueue
{
    use Queueable;

    const SETTING_SLUG   = Setting::SLUG_ORDER_PENDING_APPROVAL_SMS;
    const MESSAGE_PREFIX = 'Bluon - ';
    const SMS_SUFFIX     = ' - Do Not Reply to this text.';
    protected Order $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
        $this->onConnection('database');
    }

    public function via($notifiable)
    {
        $user = $this->order->user;

        if ($user->shouldSendSmsNotification(self::SETTING_SLUG)) {
            return [TwilioChannel::class];
        }

        return [];
    }

    public function toTwilio($notifiable)
    {
        $orderUuid = $this->order->getRouteKey();
        $link      = self::MESSAGE_PREFIX . Config::get('live.url') . Str::replace('{order}', $orderUuid,
                Config::get('live.order.summary')) . self::SMS_SUFFIX;

        return (new TwilioSmsMessage())->content($link);
    }
}
