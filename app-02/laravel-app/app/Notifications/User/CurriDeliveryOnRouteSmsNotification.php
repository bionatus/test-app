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

class CurriDeliveryOnRouteSmsNotification extends Notification implements ShouldQueue
{
    use Queueable;

    const SMS_MESSAGE = 'Bluon - :supplier_name sent you a message: Your driver is on the way! Do Not Reply to this text.';
    protected Order $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
        $this->onConnection('database');
    }

    public function via($notifiable)
    {
        if ($notifiable->shouldSendSmsNotificationWithoutSetting()) {
            return [TwilioChannel::class];
        }

        return [];
    }

    public function toTwilio(): TwilioSmsMessage
    {
        $supplier = $this->order->supplier;
        $message  = Lang::get(self::SMS_MESSAGE, ['supplier_name' => $supplier->name]);

        return (new TwilioSmsMessage())->content($message);
    }
}
