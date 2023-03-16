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

class OrderPointsEarnedSmsNotification extends Notification implements ShouldQueue
{
    use Queueable;

    const SETTING_SLUG = Setting::SLUG_BLUON_POINTS_EARNED_SMS;
    const SMS_MESSAGE  = "Bluon - You just earned :total_points_earned Points for approving PO :po_number. Reminder: you will lose them if you cancel.\nDo not reply to this text.";
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
        $smsMessage = Lang::get(self::SMS_MESSAGE, [
            'po_number'           => $this->order->name,
            'total_points_earned' => $this->order->totalPointsEarned(),
        ]);

        return (new TwilioSmsMessage())->content($smsMessage);
    }
}
