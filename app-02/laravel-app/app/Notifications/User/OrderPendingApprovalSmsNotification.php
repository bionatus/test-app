<?php

namespace App\Notifications\User;

use App;
use App\Models\Order;
use App\Models\Setting;
use App\Actions\Models\Order\CalculatePoints;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Lang;
use NotificationChannels\Twilio\TwilioChannel;
use NotificationChannels\Twilio\TwilioSmsMessage;

class OrderPendingApprovalSmsNotification extends Notification implements ShouldQueue
{
    use Queueable;

    const SETTING_SLUG = Setting::SLUG_ORDER_PENDING_APPROVAL_SMS;
    const SMS_MESSAGE  = "Bluon - Quote Alert! Be sure to approve the Quote from :supplier_name to receive :total_points_earned Bluon Points.\nDo not reply to this text.";
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
            'supplier_name'       => $this->order->supplier->name,
            'total_points_earned' => $this->getOrderPoints(),
        ]);

        return (new TwilioSmsMessage())->content($smsMessage);
    }

    private function getOrderPoints(): int
    {
        $point = App::make(CalculatePoints::class, ['order' => $this->order])->execute();

        return $point->points();
    }
}
