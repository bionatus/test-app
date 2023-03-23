<?php

namespace App\Notifications\User;

use App;
use App\Models\Order;
use App\Models\Setting;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Lang;
use NotificationChannels\Twilio\TwilioChannel;
use NotificationChannels\Twilio\TwilioSmsMessage;

class AssignSmsNotification extends Notification implements ShouldQueue
{
    use Queueable;

    const SETTING_SLUG = Setting::SLUG_SUPPLIER_IS_WORKING_IN_YOUR_ORDER_SMS;
    const SMS_MESSAGE  = 'Bluon - :working_on_it is working on your quote. Stay tuned! Do Not Reply to this text.';
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
        $smsMessage = Lang::get(self::SMS_MESSAGE, ['working_on_it' => $this->order->working_on_it]);

        return (new TwilioSmsMessage())->content($smsMessage);
    }
}
