<?php

namespace App\Notifications\Supplier;

use App\Actions\Models\SettingSupplier\GetNotificationSetting;
use App\Mail\Supplier\OrderCanceledEmail;
use App\Models\Order;
use App\Models\Setting;
use App\NotificationChannels\TwilioByProkeepPhoneChannel;
use Config;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use NotificationChannels\Twilio\TwilioChannel;
use NotificationChannels\Twilio\TwilioSmsMessage;

class OrderCanceledByUserNotification extends Notification implements ShouldQueue
{
    use Queueable;

    const SMS_TEXT = 'BluonLive - Bid#: %s at %s location has been cancelled by %s from %s. Do Not Reply to this text.';
    protected Order $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
        $this->onConnection('database');
    }

    public function via($notifiable)
    {
        $via = [];

        $supplier = $this->order->supplier;

        $configSmsEnabled = Config::get('notifications.sms.enabled');

        $slugEmailSetting = Setting::SLUG_ORDER_REJECTED_NOTIFICATION_EMAIL;
        $slugSmsSetting   = Setting::SLUG_ORDER_REJECTED_NOTIFICATION_SMS;
        $shouldSendEmail  = (new GetNotificationSetting($supplier, $slugEmailSetting))->execute();
        $shouldSendSms    = (new GetNotificationSetting($supplier, $slugSmsSetting))->execute();

        if ($supplier->routeNotificationForTwilio() && $configSmsEnabled && $shouldSendSms) {
            $via[] = TwilioChannel::class;
        }

        if ($supplier->routeNotificationForTwilioByProkeepPhone() && $configSmsEnabled && $shouldSendSms) {
            $via[] = TwilioByProkeepPhoneChannel::class;
        }

        if ($supplier->hasContactEmail() && $shouldSendEmail) {
            $via[] = 'mail';
        }

        return $via;
    }

    public function toTwilio($notifiable)
    {
        $order       = $this->order;
        $supplier    = $order->supplier;
        $user        = $order->user;

        $smsTextReplaced = sprintf(self::SMS_TEXT, $order->bid_number, $supplier->address, $user->fullName(),
            $user->companyName());

        return (new TwilioSmsMessage())->content($smsTextReplaced);
    }

    public function toMail($notifiable)
    {
        return (new OrderCanceledEmail($this->order))->to($notifiable->contact_email, $notifiable->name)
            ->bcc($notifiable->contact_secondary_email, $notifiable->name);
    }
}
