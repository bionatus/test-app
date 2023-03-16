<?php

namespace App\Notifications\User;

use App;
use App\Models\Order;
use App\Models\Setting;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use NotificationChannels\Twilio\TwilioChannel;
use NotificationChannels\Twilio\TwilioSmsMessage;

class ApprovedByTeamSmsNotification extends Notification implements ShouldQueue
{
    use Queueable;

    const SETTING_SLUG = Setting::SLUG_ORDER_APPROVED_BY_YOUR_TEAM_SMS;
    const SMS_MESSAGE  = 'Bluon - Your team has approved your quote via shared link. Do Not Reply to this text.';
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
        return (new TwilioSmsMessage())->content(self::SMS_MESSAGE);
    }
}
