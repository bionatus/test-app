<?php

namespace App\Notifications\Supplier;

use App;
use App\Actions\Models\SettingSupplier\GetNotificationSetting;
use App\Mail\Supplier\OrderApprovedEmail;
use App\Models\Order;
use App\Models\Setting;
use App\NotificationChannels\TwilioByProkeepPhoneChannel;
use Config;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use NotificationChannels\Twilio\TwilioChannel;
use NotificationChannels\Twilio\TwilioSmsMessage;

class OrderApprovedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    const SMS_TEXT = 'BluonLive - Order %s has been approved ✅ Link: %s. Do Not Reply to this text.';
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

        $slugEmailSetting = Setting::SLUG_ORDER_APPROVED_NOTIFICATION_EMAIL;
        $slugSmsSetting   = Setting::SLUG_ORDER_APPROVED_NOTIFICATION_SMS;
        $shouldSendEmail  = App::make(GetNotificationSetting::class,
            ['supplier' => $supplier, 'slug' => $slugEmailSetting])->execute();
        $shouldSendSms    = App::make(GetNotificationSetting::class,
            ['supplier' => $supplier, 'slug' => $slugSmsSetting])->execute();

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
        $outboundUrl      = Config::get('live.url') . Config::get('live.routes.outbound');
        $orderDescription = $this->order->name ?? 'Bid #' . $this->order->bid_number;

        return (new TwilioSmsMessage())->content(sprintf(self::SMS_TEXT, $orderDescription, $outboundUrl));
    }

    public function toMail($notifiable)
    {
        return (new OrderApprovedEmail($this->order))->to($notifiable->contact_email, $notifiable->name)
            ->bcc($notifiable->contact_secondary_email, $notifiable->name);
    }
}
