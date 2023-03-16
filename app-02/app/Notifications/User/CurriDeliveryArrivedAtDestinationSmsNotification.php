<?php

namespace App\Notifications\User;

use App;
use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Lang;
use NotificationChannels\Twilio\TwilioChannel;
use NotificationChannels\Twilio\TwilioSmsMessage;

class CurriDeliveryArrivedAtDestinationSmsNotification extends Notification implements ShouldQueue
{
    use Queueable;

    const SMS_MESSAGE = 'Bluon - The driver for PO :name has arrived. Do Not Reply to this text.';
    protected Order $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
        $this->onConnection('database');
    }

    public function via($notifiable)
    {
        if ($this->order->user->shouldSendSmsNotificationWithoutSetting()) {
            return [TwilioChannel::class];
        }

        return [];
    }

    public function toTwilio($notifiable)
    {
        $msg = Lang::get(self::SMS_MESSAGE, ['name' => $this->order->name]);

        return (new TwilioSmsMessage())->content($msg);
    }
}
