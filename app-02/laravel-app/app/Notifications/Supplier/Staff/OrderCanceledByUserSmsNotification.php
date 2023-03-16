<?php

namespace App\Notifications\Supplier\Staff;

use App;
use App\Actions\Models\SettingStaff\GetNotificationSetting;
use App\Models\Order;
use App\Models\Setting;
use App\Models\Staff;
use Config;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use NotificationChannels\Twilio\TwilioChannel;
use NotificationChannels\Twilio\TwilioSmsMessage;

class OrderCanceledByUserSmsNotification extends Notification implements ShouldQueue
{
    use Queueable;

    const SMS_TEXT = 'BluonLive - Bid#: %s at %s location has been cancelled by %s from %s. Do Not Reply to this text.';
    protected Order $order;
    protected Staff $staff;
    protected bool  $shouldSendSupplierSms;

    public function __construct(Order $order, Staff $staff, bool $shouldSendSupplierSms)
    {
        $this->order                 = $order;
        $this->staff                 = $staff;
        $this->shouldSendSupplierSms = $shouldSendSupplierSms;
        $this->onConnection('database');
    }

    public function via($notifiable)
    {
        $via = [];

        $staff = $this->staff;

        $configSmsEnabled = Config::get('notifications.sms.enabled');

        $slugStaffSmsSetting = Setting::SLUG_STAFF_SMS_NOTIFICATION;
        $shouldSendStaffSms  = App::make(GetNotificationSetting::class,
            ['staff' => $staff, 'slug' => $slugStaffSmsSetting])->execute();

        if ($staff->routeNotificationForTwilio() && $configSmsEnabled && $this->shouldSendSupplierSms && $shouldSendStaffSms) {
            $via[] = TwilioChannel::class;
        }

        return $via;
    }

    public function toTwilio($notifiable)
    {
        $order    = $this->order;
        $supplier = $order->supplier;
        $user     = $order->user;

        $smsTextReplaced = sprintf(self::SMS_TEXT, $order->bid_number, $supplier->address, $user->fullName(),
            $user->companyName());

        return (new TwilioSmsMessage())->content($smsTextReplaced);
    }
}
