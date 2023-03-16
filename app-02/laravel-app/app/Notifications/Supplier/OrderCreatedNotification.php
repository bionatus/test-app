<?php

namespace App\Notifications\Supplier;

use App\Actions\Models\SettingSupplier\GetNotificationSetting;
use App\Mail\Supplier\OrderCreationEmail;
use App\Models\Order;
use App\Models\Setting;
use App\NotificationChannels\TwilioByProkeepPhoneChannel;
use Config;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use NotificationChannels\Twilio\TwilioChannel;
use NotificationChannels\Twilio\TwilioSmsMessage;

class OrderCreatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    const SMS_TEXT = 'BluonLive - %s from %s has sent a new Order Request to %s!. Do Not Reply to this text.';
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

        $slugEmailSetting = Setting::SLUG_NEW_ORDER_REQUEST_NOTIFICATION_EMAIL;
        $slugSmsSetting   = Setting::SLUG_NEW_ORDER_REQUEST_NOTIFICATION_SMS;
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
        $user            = $this->order->user;
        $smsTextReplaced = sprintf(self::SMS_TEXT, $user->fullName(), $user->companyName(),
            $this->order->supplier->address);

        return (new TwilioSmsMessage())->content($smsTextReplaced);
    }

    public function toMail($notifiable)
    {
        return (new OrderCreationEmail($this->order))->to($notifiable->contact_email, $notifiable->name)
            ->bcc($notifiable->contact_secondary_email, $notifiable->name);
    }
}
